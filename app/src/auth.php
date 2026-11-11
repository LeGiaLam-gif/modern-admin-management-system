<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/helpers.php";

$authConfig = require __DIR__ . "/config.php";
if (session_status() === PHP_SESSION_NONE) {
    session_name($authConfig["app"]["session_name"]);
    ini_set("session.use_strict_mode", "1");
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => str_starts_with($authConfig["app"]["url"], "https://"),
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    session_start();
}

const ADMIN_LOGIN_WINDOW_MINUTES = 15;
const ADMIN_LOGIN_MAX_ATTEMPTS = 5;
const ADMIN_IP_MAX_ATTEMPTS = 20;
const ADMIN_REMEMBER_DAYS = 30;

function admin_is_logged_in(): bool {
    return isset($_SESSION["admin_id"]);
}

function admin_client_ip(): string {
    return substr((string) ($_SERVER["REMOTE_ADDR"] ?? "unknown"), 0, 45);
}

function admin_login_bucket(string $identifier): string {
    return "login:id:" . hash("sha256", strtolower(substr($identifier, 0, 150)));
}

function admin_login_is_throttled(PDO $pdo, string $bucket, int $maximum): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM admin_rate_limits
        WHERE bucket = :bucket
          AND created_at > (NOW() - INTERVAL " . ADMIN_LOGIN_WINDOW_MINUTES . " MINUTE)
    ");
    $stmt->execute(["bucket" => $bucket]);
    return (int) $stmt->fetchColumn() >= $maximum;
}

function admin_login_record_attempt(PDO $pdo, string $bucket): void {
    $pdo->prepare("DELETE FROM admin_rate_limits WHERE created_at < (NOW() - INTERVAL 1 DAY)")->execute();
    $stmt = $pdo->prepare("INSERT INTO admin_rate_limits (bucket) VALUES (:bucket)");
    $stmt->execute(["bucket" => $bucket]);
}

function admin_login_clear_attempts(PDO $pdo, string $bucket): void {
    $stmt = $pdo->prepare("DELETE FROM admin_rate_limits WHERE bucket = :bucket");
    $stmt->execute(["bucket" => $bucket]);
}

function admin_login(string $username, string $password, bool $remember = false): array {
    if ($username === "" || $password === "") {
        return ["success" => false, "message" => "Vui lòng nhập tài khoản và mật khẩu."];
    }

    $pdo = db();
    $idBucket = admin_login_bucket($username);
    $ipBucket = "login:ip:" . admin_client_ip();
    if (admin_login_is_throttled($pdo, $idBucket, ADMIN_LOGIN_MAX_ATTEMPTS)
        || admin_login_is_throttled($pdo, $ipBucket, ADMIN_IP_MAX_ATTEMPTS)) {
        return [
            "success" => false,
            "message" => "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau " . ADMIN_LOGIN_WINDOW_MINUTES . " phút.",
        ];
    }

    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, full_name
        FROM admins
        WHERE username = :username OR email = :email
        LIMIT 1
    ");
    $stmt->execute(["username" => $username, "email" => $username]);
    $admin = $stmt->fetch();

    if ($admin) {
        $passwordHash = (string) $admin["password_hash"];
        $passwordVerified = password_verify($password, $passwordHash);
        $legacyHash = preg_match('/\A[a-f0-9]{64}\z/i', $passwordHash) === 1;
        if (!$passwordVerified && $legacyHash) {
            $passwordVerified = hash_equals(strtolower($passwordHash), hash("sha256", $password));
        }
    } else {
        static $dummyHash = null;
        if ($dummyHash === null) {
            $dummyHash = password_hash("dummy-password", PASSWORD_DEFAULT);
        }
        password_verify($password, $dummyHash);
        $passwordVerified = false;
        $passwordHash = "";
    }

    if (!$passwordVerified) {
        admin_login_record_attempt($pdo, $idBucket);
        admin_login_record_attempt($pdo, $ipBucket);
        return ["success" => false, "message" => "Tài khoản hoặc mật khẩu không chính xác."];
    }

    if ($legacyHash || password_needs_rehash($passwordHash, PASSWORD_DEFAULT)) {
        $updateStmt = $pdo->prepare("UPDATE admins SET password_hash = :password_hash WHERE id = :id");
        $updateStmt->execute([
            "password_hash" => password_hash($password, PASSWORD_DEFAULT),
            "id" => $admin["id"],
        ]);
    }

    admin_login_clear_attempts($pdo, $idBucket);
    session_regenerate_id(true);
    unset($_SESSION["_csrf_token"]);
    $_SESSION["admin_id"] = (int) $admin["id"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_full_name"] = $admin["full_name"];

    if ($remember) {
        admin_issue_remember_token($pdo, (int) $admin["id"]);
    } else {
        admin_revoke_remember_token($pdo, (int) $admin["id"]);
    }

    return ["success" => true, "message" => "Đăng nhập thành công."];
}

function admin_set_remember_cookie(string $value, int $expires): void {
    $config = require __DIR__ . "/config.php";
    setcookie("admin_remember_token", $value, [
        "expires" => $expires,
        "path" => "/",
        "secure" => str_starts_with($config["app"]["url"], "https://"),
        "httponly" => true,
        "samesite" => "Lax",
    ]);
}

function admin_issue_remember_token(PDO $pdo, int $adminId): void {
    $token = bin2hex(random_bytes(32));
    $days = ADMIN_REMEMBER_DAYS;
    $stmt = $pdo->prepare("
        UPDATE admins
        SET remember_token = :token,
            remember_expires_at = (NOW() + INTERVAL {$days} DAY)
        WHERE id = :id
    ");
    $stmt->execute(["token" => hash("sha256", $token), "id" => $adminId]);
    admin_set_remember_cookie($adminId . ":" . $token, time() + 86400 * $days);
}

function admin_revoke_remember_token(PDO $pdo, int $adminId): void {
    $stmt = $pdo->prepare("
        UPDATE admins
        SET remember_token = NULL, remember_expires_at = NULL
        WHERE id = :id
    ");
    $stmt->execute(["id" => $adminId]);
    admin_set_remember_cookie("", time() - 3600);
}

function admin_attempt_remember_login(): void {
    $cookie = $_COOKIE["admin_remember_token"] ?? null;
    if (admin_is_logged_in() || !is_string($cookie) || $cookie === "") {
        return;
    }

    $parts = explode(":", $cookie, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0]) || preg_match('/\A[a-f0-9]{64}\z/i', $parts[1]) !== 1) {
        admin_set_remember_cookie("", time() - 3600);
        return;
    }

    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, username, email, full_name, remember_token
        FROM admins
        WHERE id = :id AND remember_expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute(["id" => (int) $parts[0]]);
    $admin = $stmt->fetch();
    if (!$admin || !is_string($admin["remember_token"])
        || !hash_equals($admin["remember_token"], hash("sha256", $parts[1]))) {
        admin_set_remember_cookie("", time() - 3600);
        return;
    }

    session_regenerate_id(true);
    $_SESSION["admin_id"] = (int) $admin["id"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_full_name"] = $admin["full_name"];
    admin_issue_remember_token($pdo, (int) $admin["id"]);
}

function require_admin(): void {
    if (!admin_is_logged_in()) {
        redirect("/login");
    }
}

function admin_logout(bool $redirectToLogin = true): void {
    if (admin_is_logged_in()) {
        admin_revoke_remember_token(db(), (int) $_SESSION["admin_id"]);
    } else {
        admin_set_remember_cookie("", time() - 3600);
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"] ?: "Lax",
        ]);
    }
    session_destroy();
    if ($redirectToLogin) {
        redirect("/login");
    }
}

admin_attempt_remember_login();
