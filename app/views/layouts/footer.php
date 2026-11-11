<?php if (!empty($_SESSION["admin_id"])): ?></main></div><?php endif; ?>
<script src="/assets/js/admin.js"></script>
<script src="/assets/js/account-menu.js?v=<?= filemtime(__DIR__ . "/../../public/assets/js/account-menu.js") ?>"></script>
<script src="/assets/js/product-images.js"></script>
<script src="/assets/js/orders.js"></script>
<?php if (parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) === "/products"): ?>
<script src="/assets/js/product-description-tooltip.js?v=<?= filemtime(__DIR__ . "/../../public/assets/js/product-description-tooltip.js") ?>"></script>
<?php endif; ?>
<?php if (parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) === "/dashboard"): ?>
<script id="dashboard-data" type="application/json"><?= json_encode($salesReport, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="/assets/js/dashboard.js?v=<?= filemtime(__DIR__ . "/../../public/assets/js/dashboard.js") ?>"></script>
<?php endif; ?>
</body>
</html>
