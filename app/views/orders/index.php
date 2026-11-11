<nav class="orders-status-tabs" aria-label="Lọc đơn hàng theo trạng thái">
    <a class="<?= $statusFilter === null ? "active" : "" ?>" href="/orders" <?= $statusFilter === null ? 'aria-current="page"' : "" ?>>Tất cả</a>
    <a class="<?= $statusFilter === "pending" ? "active" : "" ?>" href="/orders?status=pending" <?= $statusFilter === "pending" ? 'aria-current="page"' : "" ?>>
        Chờ xử lý <span class="orders-tab-count"><?= number_format($orderStatusCounts["pending"]) ?></span>
    </a>
    <a class="<?= $statusFilter === "shipping" ? "active" : "" ?>" href="/orders?status=shipping" <?= $statusFilter === "shipping" ? 'aria-current="page"' : "" ?>>Đang giao</a>
    <a class="<?= $statusFilter === "completed" ? "active" : "" ?>" href="/orders?status=completed" <?= $statusFilter === "completed" ? 'aria-current="page"' : "" ?>>Hoàn tất</a>
</nav>

<section class="panel orders-table-panel" aria-label="Danh sách đơn hàng">
    <div class="table-wrap orders-table-wrap">
        <table class="table orders-table">
            <thead>
                <tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày tạo</th><th>Chi tiết</th></tr>
            </thead>
            <tbody>
                <?php if (!$orders): ?>
                <tr><td class="empty orders-empty" colspan="6"><?= isset($statusFilterLabel) && $statusFilterLabel !== null
                    ? "Không có đơn hàng ở trạng thái: " . e($statusFilterLabel) . "."
                    : "Chưa có đơn hàng nào." ?></td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <?php
                    $status = (string) $order["status"];
                    $createdAt = strtotime((string) $order["created_at"]);
                    ?>
                    <tr>
                        <td><strong class="orders-code">DH-<?= e(str_pad((string) $order["id"], 6, "0", STR_PAD_LEFT)) ?></strong></td>
                        <td><strong class="orders-customer"><?= e($order["customer_name"]) ?></strong></td>
                        <td><strong class="orders-total"><?= number_format((float) $order["total_amount"], 0, ',', '.') ?> đ</strong></td>
                        <td><span class="orders-state orders-state-<?= e($status) ?>"><?= e(order_status_labels()[$status] ?? $status) ?></span></td>
                        <td class="orders-date">
                            <?php if ($createdAt !== false): ?>
                            <?= e(date("Y-m-d H:i", $createdAt)) ?>
                            <?php else: ?>
                            <?= e($order["created_at"]) ?>
                            <?php endif; ?>
                        </td>
                        <td><a class="btn small secondary orders-view-button" href="/orders/show?id=<?= e((string) $order["id"]) ?>">Xem</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
