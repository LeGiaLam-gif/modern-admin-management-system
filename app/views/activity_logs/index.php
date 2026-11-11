<?php
$periodLabels = [7 => "7 ngày gần nhất", 30 => "30 ngày gần nhất", 0 => "Toàn bộ thời gian"];
$selectedPeriod = $periodLabels[$days] ?? $periodLabels[7];
$entityLabels = [
    "admin" => "Tài khoản quản trị",
    "brand" => "Thương hiệu",
    "category" => "Danh mục",
    "customer" => "Khách hàng",
    "order" => "Đơn hàng",
    "product" => "Sản phẩm",
];
?>
<section class="panel activity-log-toolbar" aria-label="Bộ lọc nhật ký hoạt động">
    <div class="activity-log-toolbar-copy">
        <span class="activity-log-live-indicator" aria-hidden="true"></span>
        <div>
            <h2>Lịch sử hoạt động <?= e($selectedPeriod) ?></h2>
            <p>Hiển thị tối đa 200 thao tác quản trị gần đây</p>
        </div>
    </div>
    <details class="activity-log-filter">
        <summary class="btn secondary">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10m-7 6h4"/><circle cx="8" cy="6" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="12" cy="18" r="1.5"/></svg>
            Bộ lọc
        </summary>
        <form method="GET" action="/activity-logs" class="activity-log-filter-popover">
            <label>
                <span>Khoảng thời gian</span>
                <select name="days">
                    <?php foreach ($periodLabels as $value => $label): ?>
                    <option value="<?= e((string) $value) ?>" <?= $days === $value ? "selected" : "" ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Loại hoạt động</span>
                <select name="action">
                    <option value="">Tất cả hoạt động</option>
                    <?php foreach ($actionLabels as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $action === $value ? "selected" : "" ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn primary">Áp dụng bộ lọc</button>
        </form>
    </details>
</section>

<section class="panel activity-log-table-panel" aria-label="Danh sách hoạt động quản trị">
    <div class="table-wrap activity-log-table-wrap">
        <table class="table activity-log-table">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Admin</th>
                    <th>Hoạt động</th>
                    <th>Chi tiết</th>
                    <th>Địa chỉ IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <?php
                $actorName = trim((string) ($log["actor_name"] ?? "")) ?: "Admin";
                $nameParts = preg_split('/\s+/u', $actorName, -1, PREG_SPLIT_NO_EMPTY) ?: ["A"];
                preg_match('/^\X/u', $nameParts[0], $firstInitial);
                preg_match('/^\X/u', $nameParts[count($nameParts) - 1], $lastInitial);
                $initials = strtoupper(($firstInitial[0] ?? "A") . (count($nameParts) > 1 ? ($lastInitial[0] ?? "") : ""));
                $actionKey = (string) ($log["action"] ?? "");
                $actionLabel = $actionLabels[$actionKey] ?? ucfirst(str_replace("_", " ", $actionKey));
                $timestamp = strtotime((string) ($log["created_at"] ?? ""));
                $entityType = (string) ($log["entity_type"] ?? "");
                $entityLabel = $entityLabels[$entityType] ?? ucfirst(str_replace("_", " ", $entityType));
                ?>
                <tr>
                    <td class="activity-log-time">
                        <?php if ($timestamp !== false): ?>
                        <strong><?= e(date("d/m/Y", $timestamp)) ?></strong>
                        <small><?= e(date("H:i:s", $timestamp)) ?></small>
                        <?php else: ?>
                        <span>—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="activity-log-admin">
                            <span class="activity-log-avatar" aria-hidden="true"><?= e($initials) ?></span>
                            <strong><?= e($actorName) ?></strong>
                        </div>
                    </td>
                    <td><span class="activity-log-action activity-log-action-<?= e(preg_replace('/[^a-z0-9_-]/i', '-', $actionKey)) ?>"><?= e($actionLabel) ?></span></td>
                    <td class="activity-log-detail">
                        <strong><?= e((string) ($log["description"] ?? "")) ?></strong>
                        <?php if ($entityType !== ""): ?>
                        <small><?= e($entityLabel) ?><?= !empty($log["entity_id"]) ? " · #" . e((string) $log["entity_id"]) : "" ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="activity-log-ip"><?= e((string) ($log["ip_address"] ?? "—") ?: "—") ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="empty activity-log-empty">Không có hoạt động nào phù hợp với bộ lọc.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
