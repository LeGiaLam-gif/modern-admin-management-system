<section class="brand-metrics" aria-label="Tổng quan thương hiệu">
    <article class="brand-metric-card brand-metric-total">
        <span class="brand-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m20.6 13.4-7.2 7.2a2 2 0 0 1-2.8 0L3 13V4h9l8.6 8.6a.6.6 0 0 1 0 .8Z"/><circle cx="7.5" cy="8.5" r="1"/></svg>
        </span>
        <div><span class="brand-metric-label">Tổng thương hiệu</span><strong><?= number_format($brandSummary["totalBrands"]) ?></strong></div>
    </article>
    <article class="brand-metric-card brand-metric-new">
        <span class="brand-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3v18m-9-9h18"/></svg>
        </span>
        <div><span class="brand-metric-label">Thương hiệu mới</span><strong>+<?= number_format($brandSummary["newThisMonth"]) ?></strong><small>tháng này</small></div>
    </article>
    <article class="brand-metric-card brand-metric-top">
        <span class="brand-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m12 3 2.3 5.2 5.7.6-4.2 3.8 1.2 5.6-5-2.9-5 2.9 1.2-5.6L4 8.8l5.7-.6L12 3Z"/><path d="M7 20h10"/></svg>
        </span>
        <div class="brand-top-copy">
            <span class="brand-metric-label">Top quy mô</span>
            <div class="brand-top-value">
                <strong title="<?= e($brandSummary["topBrand"]) ?>"><?= e($brandSummary["topBrand"]) ?></strong>
                <small><?= number_format($brandSummary["topBrandProductCount"]) ?> SP</small>
            </div>
        </div>
    </article>
</section>

<?php if (!empty($error)): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<section class="panel brand-toolbar" aria-label="Tìm kiếm và lọc thương hiệu">
    <form method="GET" action="/brands" class="brand-search-form">
        <label class="brand-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
            <input type="search" name="keyword" value="<?= e($keyword) ?>" placeholder="Tìm tên thương hiệu..." aria-label="Tìm tên thương hiệu">
        </label>
        <select name="status" aria-label="Lọc theo trạng thái">
            <option value="" <?= $status === null ? "selected" : "" ?>>Tất cả trạng thái</option>
            <option value="1" <?= $status === 1 ? "selected" : "" ?>>Đang hiển thị</option>
            <option value="0" <?= $status === 0 ? "selected" : "" ?>>Đang ẩn</option>
        </select>
        <button class="btn secondary brand-filter-submit" type="submit">Lọc</button>
        <a class="btn primary brand-add-button" href="/brands/create">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
            Thêm thương hiệu
        </a>
    </form>
</section>

<section class="panel brand-table-panel" aria-label="Danh sách thương hiệu">
    <div class="table-wrap brand-table-wrap">
        <table class="table brand-table">
            <thead>
                <tr><th>ID</th><th>Tên thương hiệu</th><th>Mô tả</th><th>Sản phẩm</th><th>Trạng thái</th><th width="180">Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $brand): ?>
                <tr>
                    <td><?= e($brand["id"]) ?></td>
                    <td><strong><?= e($brand["name"]) ?></strong></td>
                    <td><?= e($brand["description"]) ?></td>
                    <td><span class="data-count-pill"><?= number_format((int) $brand["product_count"]) ?></span></td>
                    <td><span class="visibility-status <?= $brand["status"] ? 'is-visible' : 'is-hidden' ?>"><?= $brand["status"] ? "Hiển thị" : "Ẩn" ?></span></td>
                    <td>
                        <div class="table-row-actions">
                            <a class="table-icon-action edit-icon-action" href="/brands/edit?id=<?= e($brand["id"]) ?>" aria-label="Sửa thương hiệu <?= e($brand["name"]) ?>" title="Sửa">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10"/><path d="m15.5 5.5 3 3M10 14l8.8-8.8a2.1 2.1 0 0 1 3 3L13 17l-4 1 1-4Z"/></svg>
                            </a>
                            <form class="inline" method="POST" action="/brands/delete" onsubmit="return confirmDelete('Bạn chắc chắn muốn xóa thương hiệu này?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($brand["id"]) ?>">
                                <input type="hidden" name="keyword" value="<?= e($keyword) ?>">
                                <input type="hidden" name="status" value="<?= e($status === null ? "" : (string) $status) ?>">
                                <button class="table-icon-action delete-icon-action" type="submit" aria-label="Xóa thương hiệu <?= e($brand["name"]) ?>" title="Xóa">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16m-10 4v7m4-7v7M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($brands)): ?>
                <tr><td colspan="6" class="empty"><?= $keyword !== "" || $status !== null ? "Không tìm thấy thương hiệu phù hợp." : "Chưa có thương hiệu." ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
