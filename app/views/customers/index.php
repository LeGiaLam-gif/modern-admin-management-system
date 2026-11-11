<section class="customer-metrics" aria-label="Tổng quan khách hàng">
    <article class="customer-metric-card customer-metric-total">
        <span class="customer-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.5a6.5 6.5 0 0 1 13 0V20M16 5.2a3.5 3.5 0 0 1 0 6.6M18 14a5 5 0 0 1 3.5 4.8V20"/></svg>
        </span>
        <div><span class="customer-metric-label">Tổng khách hàng</span><strong><?= number_format($customerSummary["totalCustomers"]) ?></strong></div>
    </article>
    <article class="customer-metric-card customer-metric-new">
        <span class="customer-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.5a6.5 6.5 0 0 1 13 0V20M19 8v6m-3-3h6"/></svg>
        </span>
        <div><span class="customer-metric-label">Khách mới tháng này</span><strong>+<?= number_format($customerSummary["newThisMonth"]) ?></strong></div>
    </article>
    <article class="customer-metric-card customer-metric-returning">
        <span class="customer-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 4v6h6M5.6 9a8 8 0 1 1-.2 6"/></svg>
        </span>
        <div><span class="customer-metric-label">Tỷ lệ quay lại</span><strong><?= number_format($customerSummary["returningRate"], 0) ?>%</strong></div>
    </article>
</section>

<section class="panel customer-toolbar" aria-label="Tìm kiếm khách hàng">
    <form method="GET" action="/customers" class="customer-search-form">
        <label class="customer-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
            <input type="search" name="keyword" value="<?= e($keyword) ?>" placeholder="Tìm tên, email, số điện thoại..." aria-label="Tìm tên, email hoặc số điện thoại khách hàng">
        </label>
        <a class="btn primary customer-add-button" href="/customers/create">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
            Thêm khách hàng
        </a>
    </form>
</section>

<section class="panel customer-table-panel" aria-label="Danh sách khách hàng">
    <div class="table-wrap customer-table-wrap">
        <table class="table">
            <thead>
                <tr><th>Khách hàng</th><th>Số điện thoại</th><th>Email</th><th>Địa chỉ</th><th>Ngày tạo</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (!$customers): ?>
                    <tr><td class="empty" colspan="6"><?= $keyword !== "" ? "Không tìm thấy khách hàng phù hợp." : "Chưa có khách hàng nào." ?></td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?= e($customer["name"]) ?></strong><?php if ($customer["notes"]): ?><small><?= e($customer["notes"]) ?></small><?php endif; ?></td>
                            <td><?= e($customer["phone"]) ?></td>
                            <td><?= e($customer["email"] ?: "—") ?></td>
                            <td><?= e($customer["address"]) ?></td>
                            <td><?= e($customer["created_at"]) ?></td>
                            <td>
                                <div class="table-row-actions">
                                    <a class="table-icon-action edit-icon-action" href="/customers/edit?id=<?= e((string) $customer["id"]) ?>" aria-label="Sửa khách hàng <?= e($customer["name"]) ?>" title="Sửa">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10"/><path d="m15.5 5.5 3 3M10 14l8.8-8.8a2.1 2.1 0 0 1 3 3L13 17l-4 1 1-4Z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
