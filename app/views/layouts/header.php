<?php
$config = require __DIR__ . "/../../src/config.php";
$currentPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$pageDescriptions = [
    "/dashboard" => "Hệ thống đang hoạt động ổn định · Phiên làm việc: 22:00 PM",
    "/categories" => "Tổng quan danh mục · Tổ chức và theo dõi các nhóm sản phẩm.",
    "/brands" => "Tổng quan thương hiệu · Quản lý thương hiệu và sản phẩm liên quan.",
    "/products" => "Tổng quan sản phẩm · Theo dõi hàng hóa, tồn kho và trạng thái hiển thị.",
    "/customers" => "Quản lý hồ sơ, phân hạng và lịch sử mua hàng.",
    "/inventory" => "Quản lý dòng chảy hàng hóa và cảnh báo tồn kho.",
    "/orders" => "Theo dõi tiến trình xử lý và trạng thái vận chuyển.",
    "/activity-logs" => "Theo dõi lịch sử thao tác quản trị hệ thống.",
];
$pageDescription = $pageDescriptions[$currentPath] ?? null;
if ($pageDescription === null) {
    foreach ($pageDescriptions as $route => $description) {
        if ($route !== "/dashboard" && str_starts_with($currentPath, $route . "/")) {
            $pageDescription = $description;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title><?= e($title ?? $config["app"]["name"]) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="/assets/js/theme.js?v=<?= filemtime(__DIR__ . "/../../public/assets/js/theme.js") ?>"></script>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/auth-session.css">
    <link rel="stylesheet" href="/assets/css/product-features.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/product-features.css") ?>">
    <?php if (str_starts_with($currentPath, "/orders")): ?><link rel="stylesheet" href="/assets/css/orders.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/orders.css") ?>"><?php endif; ?>
    <link rel="stylesheet" href="/assets/css/app-theme.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/app-theme.css") ?>">
    <?php if ($currentPath === "/categories"): ?><link rel="stylesheet" href="/assets/css/categories.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/categories.css") ?>"><?php endif; ?>
    <?php if ($currentPath === "/inventory"): ?><link rel="stylesheet" href="/assets/css/inventory.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/inventory.css") ?>"><?php endif; ?>
    <?php if ($currentPath === "/activity-logs"): ?><link rel="stylesheet" href="/assets/css/activity-logs.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/activity-logs.css") ?>"><?php endif; ?>
    <?php if (str_starts_with($currentPath, "/brands")): ?><link rel="stylesheet" href="/assets/css/brands.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/brands.css") ?>"><?php endif; ?>
    <?php if (str_starts_with($currentPath, "/customers")): ?><link rel="stylesheet" href="/assets/css/customers.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/customers.css") ?>"><?php endif; ?>
    <?php if ($currentPath === "/dashboard"): ?><link rel="stylesheet" href="/assets/css/dashboard.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/dashboard.css") ?>"><?php endif; ?>
    <?php if ($currentPath === "/login"): ?><link rel="stylesheet" href="/assets/css/login.css?v=<?= filemtime(__DIR__ . "/../../public/assets/css/login.css") ?>"><?php endif; ?>
</head>
<body>
<?php if (!empty($_SESSION["admin_id"])): ?>
<div class="admin-layout<?= $currentPath === "/dashboard" ? " dashboard-layout" : "" ?>">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"><?= $currentPath === "/dashboard" ? "LS" : "L" ?></div>
            <div class="brand-copy">
                <?php if ($currentPath === "/dashboard"): ?>
                    <strong>LIQUID</strong><small>SILHOUETTE</small>
                <?php else: ?>
                    <strong>LIQUID<br>SILHOUETTE</strong><small>Product Manager</small>
                <?php endif; ?>
            </div>
        </div>
        <nav class="nav">
            <a class="<?= $currentPath === '/dashboard' ? 'active' : '' ?>" href="/dashboard"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></svg><span>Dashboard</span></a>
            <a class="<?= str_starts_with($currentPath, '/categories') ? 'active' : '' ?>" href="/categories"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span>Danh mục</span></a>
            <a class="<?= str_starts_with($currentPath, '/brands') ? 'active' : '' ?>" href="/brands"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/></svg><span>Thương hiệu</span></a>
            <a class="<?= str_starts_with($currentPath, '/products') ? 'active' : '' ?>" href="/products"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.7 7.5 4.2 7.5-4.2M12 12v9"/></svg><span>Sản phẩm</span></a>
            <a class="<?= str_starts_with($currentPath, '/customers') ? 'active' : '' ?>" href="/customers"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.5a6.5 6.5 0 0 1 13 0V20M16 5.2a3.5 3.5 0 0 1 0 6.6M18 14a5 5 0 0 1 3.5 4.8V20"/></svg><span>Khách hàng</span></a>
            <a class="<?= str_starts_with($currentPath, '/inventory') ? 'active' : '' ?>" href="/inventory"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h13l-3-3m3 3-3 3M17 17H4l3 3m-3-3 3-3"/></svg><span>Nhập xuất kho</span></a>
            <a class="<?= str_starts_with($currentPath, '/orders') ? 'active' : '' ?>" href="/orders"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 1.9-1.4L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span>Đơn hàng</span></a>
        </nav>
        <?php
        $adminDisplayName = trim((string) ($_SESSION["admin_full_name"] ?? $_SESSION["admin_username"] ?? "Admin"));
        if (str_contains($adminDisplayName, ":")) {
            $adminDisplayName = trim((string) substr($adminDisplayName, strrpos($adminDisplayName, ":") + 1));
        }
        $adminNameParts = preg_split('/\s+/u', $adminDisplayName, -1, PREG_SPLIT_NO_EMPTY) ?: ["A"];
        preg_match('/^\X/u', $adminNameParts[0], $firstInitial);
        preg_match('/^\X/u', $adminNameParts[count($adminNameParts) - 1], $lastInitial);
        $adminAvatarInitials = strtoupper(($firstInitial[0] ?? "A")
            . (count($adminNameParts) > 1 ? ($lastInitial[0] ?? "") : ""));
        ?>
        <div class="sidebar-footer">
            <div class="sidebar-account-row">
                <details class="sidebar-account-menu">
                    <summary class="sidebar-profile-trigger" aria-label="Mở menu tài khoản">
                        <span class="sidebar-profile-avatar" aria-hidden="true"><?= e($adminAvatarInitials) ?></span>
                        <span class="sidebar-profile-copy">
                            <strong><?= e($adminDisplayName) ?></strong>
                            <small>Manager</small>
                        </span>
                    </summary>
                    <div class="sidebar-account-popover">
                        <a class="sidebar-account-activity<?= str_starts_with($currentPath, '/activity-logs') ? ' active' : '' ?>"
                           href="/activity-logs"
                           <?= str_starts_with($currentPath, '/activity-logs') ? 'aria-current="page"' : '' ?>>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h4l3-8 4 16 3-8h4"/><circle cx="12" cy="12" r="9"/></svg>
                            <span>Log hoạt động</span>
                        </a>
                        <form method="POST" action="/logout">
                            <?= csrf_field() ?>
                            <button class="sidebar-account-logout" type="submit">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </details>
                <button class="sidebar-settings-button" type="button" aria-label="Cài đặt" aria-expanded="false" aria-describedby="sidebar-settings-status" title="Cài đặt">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z"/><path d="m19.4 15 .1.1a1.8 1.8 0 0 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 0 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 0 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3H3.8a1.8 1.8 0 0 1 0-3.6H4a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 0 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 0 1 3.6 0V2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 0 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 0 1 0 3.6H20a1.8 1.8 0 0 0-.6 3Z"/></svg>
                </button>
            </div>
            <p class="sidebar-settings-status" id="sidebar-settings-status" role="status" hidden>Chức năng chưa được hỗ trợ.</p>
        </div>
    </aside>
    <main class="main">
        <header class="topbar<?= $currentPath === "/dashboard" ? " topbar-dashboard" : "" ?>">
            <div class="topbar-copy">
                <h1><?php if ($currentPath === "/dashboard"): ?><span class="dashboard-title-primary">Dashboard</span> <span class="dashboard-title-secondary">Overview</span><?php else: ?><?= e($title ?? "Admin") ?><?php endif; ?></h1>
                <p><?php if ($currentPath === "/dashboard"): ?><span class="dashboard-title-dot" aria-hidden="true"></span><?php endif; ?><?= e($pageDescription ?? ("Xin chào, " . ($_SESSION["admin_full_name"] ?? $_SESSION["admin_username"] ?? "Admin"))) ?></p>
            </div>
            <?php if ($currentPath === "/dashboard" && isset($salesReport)): ?>
                <div class="dashboard-header-actions">
                    <nav class="dashboard-period-tabs" aria-label="Lọc dashboard theo thời gian">
                        <?php foreach (["day" => "Ngày", "week" => "Tuần", "month" => "Tháng", "quarter" => "Quý", "year" => "Năm"] as $periodKey => $periodLabel): ?>
                            <a class="<?= $salesReport["period"] === $periodKey ? "active" : "" ?>"
                               href="/dashboard?period=<?= e($periodKey) ?>"
                               <?= $salesReport["period"] === $periodKey ? 'aria-current="page"' : "" ?>><?= e($periodLabel) ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <details class="dashboard-quick-menu">
                        <summary class="dashboard-header-quick-action">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.2 2 5 13h5l-.8 9L19 10h-5l-.8-8Z"/></svg> Thao tác nhanh
                            <span class="quick-menu-caret" aria-hidden="true"></span>
                        </summary>
                        <nav class="dashboard-quick-menu-list" aria-label="Thao tác nhanh">
                            <a href="/orders/create">
                                <span class="quick-menu-icon quick-menu-orders" aria-hidden="true">＋</span>
                                <span><strong>Tạo đơn hàng mới</strong><small>Bắt đầu đơn hàng</small></span>
                            </a>
                            <a href="/customers/create">
                                <span class="quick-menu-icon quick-menu-other-icon" aria-hidden="true">＋</span>
                                <span><strong>Thêm khách hàng</strong><small>Tạo hồ sơ khách hàng</small></span>
                            </a>
                        </nav>
                    </details>
                    <label class="theme-switch" aria-label="Chuyển đổi giữa giao diện sáng và tối">
                        <input class="theme-switch-checkbox" type="checkbox" aria-label="Bật giao diện tối">
                        <span class="theme-switch-slider" aria-hidden="true">
                            <span class="theme-switch-cloud theme-switch-cloud-one"></span>
                            <span class="theme-switch-cloud theme-switch-cloud-two"></span>
                            <span class="theme-switch-star theme-switch-star-one"></span>
                            <span class="theme-switch-star theme-switch-star-two"></span>
                            <span class="theme-switch-star theme-switch-star-three"></span>
                            <span class="theme-switch-thumb">
                                <span class="theme-switch-icon theme-switch-sun">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                                </span>
                                <span class="theme-switch-icon theme-switch-moon">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.2 15.6A8.8 8.8 0 0 1 8.4 3.8 9 9 0 1 0 20.2 15.6Z"/></svg>
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            <?php else: ?>
                <div class="module-header-actions">
                    <?php if ($currentPath === "/products" && isset($keyword, $categoryId, $brandId)): ?>
                        <a class="product-export-action" href="/products/export?<?= e(http_build_query(["keyword" => $keyword, "category_id" => $categoryId, "brand_id" => $brandId])) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5"/><path d="M4 16v4h16v-4"/></svg>
                            Xuất Excel
                        </a>
                    <?php endif; ?>
                    <label class="theme-switch" aria-label="Chuyển đổi giữa giao diện sáng và tối">
                        <input class="theme-switch-checkbox" type="checkbox" aria-label="Bật giao diện tối">
                        <span class="theme-switch-slider" aria-hidden="true">
                            <span class="theme-switch-cloud theme-switch-cloud-one"></span>
                            <span class="theme-switch-cloud theme-switch-cloud-two"></span>
                            <span class="theme-switch-star theme-switch-star-one"></span>
                            <span class="theme-switch-star theme-switch-star-two"></span>
                            <span class="theme-switch-star theme-switch-star-three"></span>
                            <span class="theme-switch-thumb">
                                <span class="theme-switch-icon theme-switch-sun">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                                </span>
                                <span class="theme-switch-icon theme-switch-moon">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.2 15.6A8.8 8.8 0 0 1 8.4 3.8 9 9 0 1 0 20.2 15.6Z"/></svg>
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            <?php endif; ?>
        </header>
<?php endif; ?>
