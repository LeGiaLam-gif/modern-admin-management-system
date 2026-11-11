<div class="login-page luxury-login">
    <div class="login-ambient" aria-hidden="true">
        <div class="login-deep-space"></div>
        <div class="login-ambient-light"></div>
        <canvas id="loginNebulaCanvas"></canvas>
        <div class="login-center-mask"></div>
        <canvas id="loginCosmosCanvas"></canvas>
    </div>

    <main class="luxury-login-main">
        <header class="luxury-login-brand">
            <div class="luxury-login-emblem" aria-hidden="true">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="24" y="7" width="16" height="9" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M27 16v6h10v-6M18 25h28l4 26c.5 3.2-3.1 5-18 5s-18.5-1.8-18-5l4-26Z" stroke="currentColor" stroke-width="2"/>
                    <path d="m25 31-2 17m18-17 2 17M28 25v29m8-29v29" stroke="currentColor" stroke-width="1.5" opacity=".75"/>
                    <path d="M25 35h14" stroke="currentColor" stroke-width="1.5" opacity=".75"/>
                </svg>
            </div>
            <h1>LIQUID SILHOUETTE</h1>
            <p>The Invisible Architecture of Man</p>
            <p class="luxury-login-caption">Studio &amp; Inventory Console</p>
        </header>

        <section class="luxury-login-card" aria-labelledby="login-heading">
            <div class="luxury-login-card-shimmer" aria-hidden="true"></div>
            <div class="luxury-login-card-heading">
                <span class="luxury-login-status"><span aria-hidden="true"></span> CỔNG QUẢN TRỊ</span>
                <h2 id="login-heading">Chào mừng trở lại</h2>
                <p>Đăng nhập để quản lý cửa hàng của bạn.</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="luxury-login-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login" id="loginForm" class="luxury-login-form">
                <?= csrf_field() ?>
                <div class="luxury-login-field">
                    <label for="username">Email hoặc tài khoản</label>
                    <div class="luxury-login-input">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 6.5h16v11H4zM4.5 7l7.5 6 7.5-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input type="text" name="username" id="username" placeholder="Nhập email hoặc tài khoản" autocomplete="username" required>
                    </div>
                    <small class="luxury-login-field-error field-error" id="usernameError"></small>
                </div>

                <div class="luxury-login-field">
                    <div class="luxury-login-label-row">
                        <label for="password">Mật khẩu</label>
                        <a href="/forgot-password">Quên mật khẩu?</a>
                    </div>
                    <div class="luxury-login-input">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
                        <button class="luxury-login-password-toggle" type="button" id="togglePassword" aria-label="Hiện mật khẩu" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.6"/>
                                <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.6"/>
                            </svg>
                        </button>
                    </div>
                    <small class="luxury-login-field-error field-error" id="passwordError"></small>
                </div>

                <label class="luxury-login-remember">
                    <input type="checkbox" name="remember" value="1" <?= !empty($remember) ? "checked" : "" ?>>
                    <span class="luxury-login-check" aria-hidden="true"></span>
                    <span>Duy trì phiên làm việc bảo mật <small>(30 ngày)</small></span>
                </label>

                <button class="luxury-login-submit" type="submit">
                    <span>Đăng nhập hệ thống</span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>

            <div class="luxury-login-demo">
                <span class="luxury-login-demo-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 19 6v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span><strong>Tài khoản demo</strong><small>admin / admin123</small></span>
            </div>
        </section>

        <footer class="luxury-login-footer">
            <span class="luxury-login-lock" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </span>
            <span>Phiên đăng nhập được bảo vệ an toàn</span>
        </footer>
    </main>
</div>
<div class="luxury-login-toast" id="loginToast" role="alert" aria-live="assertive" aria-hidden="true">
    <span class="luxury-login-toast-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M12 8v5m0 3h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    <span class="luxury-login-toast-copy">
        <strong>Không thể đăng nhập</strong>
        <span id="loginToastMessage"></span>
    </span>
    <button class="luxury-login-toast-close" type="button" id="loginToastClose" aria-label="Đóng thông báo">&times;</button>
</div>
<script src="/assets/js/login-cosmos.js?v=<?= filemtime(__DIR__ . "/../../public/assets/js/login-cosmos.js") ?>"></script>
<script>
    document.getElementById("togglePassword").addEventListener("click", function () {
        const password = document.getElementById("password");
        const isVisible = password.type === "text";
        password.type = isVisible ? "password" : "text";
        this.setAttribute("aria-pressed", String(!isVisible));
        this.setAttribute("aria-label", isVisible ? "Hiện mật khẩu" : "Ẩn mật khẩu");
    });

    const loginError = document.querySelector(".luxury-login-error");
    const loginToast = document.getElementById("loginToast");
    const loginToastMessage = document.getElementById("loginToastMessage");
    let loginToastTimer;

    function hideLoginToast() {
        loginToast.classList.remove("is-visible");
        loginToast.setAttribute("aria-hidden", "true");
    }

    if (loginError && loginToast && loginToastMessage) {
        loginToastMessage.textContent = loginError.textContent.trim();
        loginToast.classList.add("is-visible");
        loginToast.setAttribute("aria-hidden", "false");
        loginToastTimer = window.setTimeout(hideLoginToast, 6000);
    }

    document.getElementById("loginToastClose").addEventListener("click", function () {
        window.clearTimeout(loginToastTimer);
        hideLoginToast();
    });
</script>
