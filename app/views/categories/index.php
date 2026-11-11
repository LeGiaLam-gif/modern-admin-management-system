<section class="category-metrics" aria-label="Tổng quan danh mục">
    <article class="category-metric-card category-metric-total">
        <span class="category-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m12 3 9 4.5-9 4.5-9-4.5L12 3Z"/><path d="m3 12 9 4.5 9-4.5M3 16.5 12 21l9-4.5"/></svg>
        </span>
        <div><span class="category-metric-label">Tổng danh mục</span><strong><?= number_format($categorySummary["totalCategories"]) ?></strong></div>
    </article>
    <article class="category-metric-card category-metric-active">
        <span class="category-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
        </span>
        <div><span class="category-metric-label">Đang hoạt động</span><strong><?= number_format($categorySummary["activeCategories"]) ?></strong></div>
    </article>
    <article class="category-metric-card category-metric-average">
        <span class="category-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="m3 7 9 4 9-4v10l-9 4-9-4V7Z"/><path d="M12 11v10"/></svg>
        </span>
        <div class="category-average-copy">
            <span class="category-metric-label">SP TB / danh mục</span>
            <div class="category-average-value"><strong><?= number_format($categorySummary["averageProducts"]) ?></strong><small>sản phẩm</small></div>
        </div>
    </article>
</section>

<section class="panel category-toolbar" aria-label="Tìm kiếm và lọc danh mục">
    <form method="GET" action="/categories" class="category-search-form">
        <label class="category-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
            <input type="search" name="keyword" value="<?= e($keyword) ?>" placeholder="Tìm tên danh mục..." aria-label="Tìm tên danh mục">
        </label>
        <select name="status" aria-label="Lọc theo trạng thái">
            <option value="" <?= $status === null ? "selected" : "" ?>>Tất cả trạng thái</option>
            <option value="1" <?= $status === 1 ? "selected" : "" ?>>Đang hiển thị</option>
            <option value="0" <?= $status === 0 ? "selected" : "" ?>>Đang ẩn</option>
        </select>
        <button class="btn secondary category-filter-submit" type="submit">Lọc</button>
        <a class="btn primary category-add-button" href="/categories/create">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
            Thêm danh mục
        </a>
    </form>
</section>

<section class="panel category-table-panel" aria-label="Danh sách danh mục">
    <div class="table-wrap category-table-wrap">
        <table class="table category-table">
            <thead>
                <tr><th>ID</th><th>Tên danh mục</th><th>Mô tả</th><th>Số lượng SP</th><th>Trạng thái</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category["id"]) ?></td>
                    <td><strong class="category-name"><?= e($category["name"]) ?></strong></td>
                    <td class="category-description"><?= e($category["description"] ?? "") ?></td>
                    <td><span class="data-count-pill category-product-count"><?= number_format((int) $category["product_count"]) ?></span></td>
                    <td><span class="visibility-status <?= $category["status"] ? "is-visible" : "is-hidden" ?>"><?= $category["status"] ? "Hiển thị" : "Đang ẩn" ?></span></td>
                    <td>
                        <div class="table-row-actions">
                            <a class="table-icon-action edit-icon-action" href="/categories/edit?id=<?= e($category["id"]) ?>" aria-label="Sửa danh mục <?= e($category["name"]) ?>" title="Sửa">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10"/><path d="m15.5 5.5 3 3M10 14l8.8-8.8a2.1 2.1 0 0 1 3 3L13 17l-4 1 1-4Z"/></svg>
                            </a>
                            <form class="inline" method="POST" action="/categories/delete" onsubmit="return confirmDelete('Bạn chắc chắn muốn xóa danh mục này?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($category["id"]) ?>">
                                <input type="hidden" name="keyword" value="<?= e($keyword) ?>">
                                <input type="hidden" name="status" value="<?= e($status === null ? "" : (string) $status) ?>">
                                <button class="table-icon-action delete-icon-action" type="submit" aria-label="Xóa danh mục <?= e($category["name"]) ?>" title="Xóa">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16m-10 4v7m4-7v7M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr><td colspan="6" class="empty"><?= $keyword !== "" || $status !== null ? "Không tìm thấy danh mục phù hợp." : "Chưa có danh mục." ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
