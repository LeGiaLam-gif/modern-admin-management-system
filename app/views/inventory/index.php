<section class="inventory-metrics" aria-label="Tổng quan kho hàng">
    <article class="inventory-metric-card inventory-metric-total">
        <span class="inventory-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 7.5 12 3l9 4.5-9 4.5-9-4.5Z"/><path d="M3 7.5v9L12 21l9-4.5v-9M12 12v9"/><path d="m7 5 9 4.5"/></svg>
        </span>
        <div><span class="inventory-metric-label">Tổng kho hiện tại</span><strong><?= number_format($inventorySummary["totalStock"]) ?> <small>SP</small></strong></div>
    </article>
    <article class="inventory-metric-card inventory-metric-warning">
        <span class="inventory-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg>
        </span>
        <div><span class="inventory-metric-label">Cảnh báo hết hàng</span><strong><?= number_format($inventorySummary["lowStockProducts"]) ?> <small>SP</small></strong></div>
    </article>
    <article class="inventory-metric-card inventory-metric-in">
        <span class="inventory-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M14 3h4a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-4"/></svg>
        </span>
        <div><span class="inventory-metric-label">Phiếu nhập tháng</span><strong><?= number_format($inventorySummary["incomingThisMonth"]) ?></strong></div>
    </article>
    <article class="inventory-metric-card inventory-metric-out">
        <span class="inventory-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m14 7-5 5 5 5m-5-5h12"/><path d="M10 3H6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h4"/></svg>
        </span>
        <div><span class="inventory-metric-label">Phiếu xuất tháng</span><strong><?= number_format($inventorySummary["outgoingThisMonth"]) ?></strong></div>
    </article>
</section>

<section class="panel inventory-toolbar" aria-label="Tìm kiếm và lọc giao dịch kho">
    <form method="GET" action="/inventory" class="inventory-filter-form">
        <label class="inventory-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
            <input type="search" name="keyword" value="<?= e($keyword) ?>" placeholder="Tìm mã phiếu hoặc sản phẩm..." aria-label="Tìm mã phiếu hoặc sản phẩm">
        </label>
        <select name="type" aria-label="Lọc theo loại phiếu">
            <option value="" <?= $type === null ? "selected" : "" ?>>Tất cả loại phiếu</option>
            <option value="in" <?= $type === "in" ? "selected" : "" ?>>Nhập kho</option>
            <option value="out" <?= $type === "out" ? "selected" : "" ?>>Xuất kho</option>
        </select>
        <button class="btn secondary inventory-filter-submit" type="submit">Lọc</button>
        <a class="btn primary inventory-create-button" href="/inventory/create">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v6h6M9 14h6m-6 3h6"/></svg>
            Tạo phiếu mới
        </a>
    </form>
</section>

<section class="panel inventory-table-panel" aria-label="Lịch sử nhập xuất kho">
    <div class="table-wrap inventory-table-wrap">
        <table class="table inventory-table">
            <thead>
                <tr><th>Mã phiếu</th><th>Loại</th><th>Ngày tạo</th><th>Sản phẩm / ghi chú</th><th>Số lượng</th><th>Tồn kho</th><th>Người phụ trách</th></tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $movement): ?>
                <?php
                $movementType = (string) $movement["movement_type"];
                $timestamp = strtotime((string) $movement["created_at"]);
                $actorName = trim((string) ($movement["admin_name"] ?? "")) ?: "Hệ thống";
                $nameParts = preg_split('/\s+/u', $actorName, -1, PREG_SPLIT_NO_EMPTY) ?: ["H"];
                preg_match('/^\X/u', $nameParts[0], $firstInitial);
                preg_match('/^\X/u', $nameParts[count($nameParts) - 1], $lastInitial);
                $initials = strtoupper(($firstInitial[0] ?? "H") . (count($nameParts) > 1 ? ($lastInitial[0] ?? "") : ""));
                $voucherPrefix = $movementType === "in" ? "PN" : "PX";
                $voucherDate = $timestamp !== false ? date("Ymd", $timestamp) : "unknown";
                $voucherCode = $voucherPrefix . "-" . $voucherDate . str_pad((string) $movement["id"], 3, "0", STR_PAD_LEFT);
                $reference = !empty($movement["note"])
                    ? (string) $movement["note"]
                    : ((string) ($movement["reference_type"] ?? "") === "order"
                        ? "Đơn hàng DH-" . str_pad((string) $movement["reference_id"], 6, "0", STR_PAD_LEFT)
                        : "Không có ghi chú");
                ?>
                <tr>
                    <td><strong class="inventory-voucher-code inventory-voucher-<?= $movementType ?>"><?= e($voucherCode) ?></strong></td>
                    <td><span class="inventory-type-badge inventory-type-<?= $movementType ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $movementType === "in" ? "M12 19V5m-6 6 6-6 6 6" : "M12 5v14m-6-6 6 6 6-6" ?>"/></svg><?= $movementType === "in" ? "Nhập kho" : "Xuất kho" ?></span></td>
                    <td class="inventory-date">
                        <?php if ($timestamp !== false): ?>
                        <strong><?= e(date("Y-m-d", $timestamp)) ?></strong>
                        <small><?= e(date("H:i", $timestamp)) ?></small>
                        <?php else: ?>
                        <span>—</span>
                        <?php endif; ?>
                    </td>
                    <td class="inventory-product-detail">
                        <strong><?= e($movement["product_name"]) ?></strong>
                        <?php if (!empty($movement["product_sku"])): ?><small>SKU: <?= e($movement["product_sku"]) ?></small><?php endif; ?>
                        <small class="inventory-reference" title="<?= e($reference) ?>"><?= e($reference) ?></small>
                    </td>
                    <td><span class="data-count-pill inventory-quantity inventory-quantity-<?= $movementType ?>"><?= $movementType === "in" ? "+" : "−" ?><?= number_format((int) $movement["quantity"]) ?></span></td>
                    <td><span class="data-count-pill inventory-stock"><?= number_format((int) $movement["stock_after"]) ?></span></td>
                    <td>
                        <div class="inventory-admin">
                            <span class="inventory-admin-avatar" aria-hidden="true"><?= e($initials) ?></span>
                            <strong><?= e($actorName) ?></strong>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($movements)): ?>
                <tr><td class="empty inventory-empty" colspan="7"><?= $keyword !== "" || $type !== null ? "Không tìm thấy giao dịch phù hợp." : "Chưa có giao dịch kho nào." ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="help inventory-table-note">Hiển thị tối đa 200 giao dịch gần nhất.</p>
</section>
