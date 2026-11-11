<section class="product-metrics" aria-label="Tổng quan sản phẩm">
    <article class="product-metric-card product-metric-bestseller">
        <span class="product-metric-icon product-icon-total" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="m3 7 9 4 9-4v10l-9 4-9-4V7Z"/><path d="M12 11v10"/></svg>
        </span>
        <div>
            <span class="product-metric-label">Tổng sản phẩm</span>
            <strong><?= number_format($productSummary["totalProducts"]) ?></strong>
        </div>
    </article>
    <article class="product-metric-card product-metric-warning">
        <span class="product-metric-icon product-icon-warning" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg>
        </span>
        <div>
            <span class="product-metric-label">Sắp hết hàng</span>
            <strong><?= number_format($productSummary["lowStockCount"]) ?></strong>
            <small>Còn <?= number_format($productSummary["lowStockThreshold"]) ?> sản phẩm hoặc ít hơn</small>
        </div>
    </article>
    <article class="product-metric-card">
        <span class="product-metric-icon product-icon-bestseller" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m12 3 2.4 5 5.5.8-4 3.9.9 5.5-4.8-2.6-4.8 2.6.9-5.5-4-3.9 5.5-.8L12 3Z"/><path d="M7 20h10"/></svg>
        </span>
        <div class="product-bestseller-copy">
            <span class="product-metric-label">Sản phẩm bán chạy</span>
            <strong title="<?= e($productSummary["bestSeller"]) ?>"><?= e($productSummary["bestSeller"]) ?></strong>
        </div>
    </article>
</section>

<section class="panel product-filter-panel" aria-label="Tìm và lọc sản phẩm">
    <form method="GET" action="/products" class="filter-form product-filter-form">
        <label class="product-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
            <input type="search" name="keyword" placeholder="Tìm tên sản phẩm hoặc SKU..." value="<?= e($keyword) ?>" aria-label="Tìm tên sản phẩm hoặc SKU">
        </label>
        <select name="category_id" aria-label="Lọc theo danh mục">
            <option value="0">Tất cả danh mục</option>
            <?php foreach ($categories as $category): ?>
            <option value="<?= e($category["id"]) ?>" <?= (int)$categoryId === (int)$category["id"] ? "selected" : "" ?>><?= e($category["name"]) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="brand_id" aria-label="Lọc theo thương hiệu">
            <option value="0">Tất cả thương hiệu</option>
            <?php foreach ($brands as $brand): ?>
            <option value="<?= e($brand["id"]) ?>" <?= (int)$brandId === (int)$brand["id"] ? "selected" : "" ?>><?= e($brand["name"]) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn secondary product-filter-submit" type="submit">Lọc</button>
        <a class="btn primary product-add-button" href="/products/create">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
            Thêm sản phẩm
        </a>
    </form>
</section>

<section class="panel product-table-panel" aria-label="Danh sách sản phẩm">
    <div class="table-wrap product-table-wrap">
        <table class="table product-table">
            <thead>
                <tr>
                    <th>Hình</th>
                    <th>Thông tin sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>SL</th>
                    <th>Đơn giá</th>
                    <th>Trạng thái</th>
                    <th width="180">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?php if (!empty($product["image_path"])): ?>
                            <img class="thumb" src="/<?= e($product["image_path"]) ?>" alt="<?= e($product["name"]) ?>">
                        <?php else: ?>
                            <div class="no-image">No image</div>
                        <?php endif; ?>
                    </td>
                    <td class="product-information">
                        <?php if (!empty($product["description"])): ?>
                        <button class="product-name-trigger" type="button" data-description="<?= e($product["description"]) ?>" aria-label="Xem mô tả sản phẩm <?= e($product["name"]) ?>" aria-expanded="false">
                            <?= e($product["name"]) ?>
                        </button>
                        <?php else: ?>
                        <strong><?= e($product["name"]) ?></strong>
                        <?php endif; ?>
                        <small>SKU: <?= e($product["sku"] ?: "N/A") ?></small>
                    </td>
                    <td><?= e($product["category_name"]) ?></td>
                    <td><?= e($product["brand_name"] ?? "—") ?></td>
                    <td>
                        <span class="data-count-pill product-stock-count">
                            <?= number_format((int)$product["quantity"]) ?>
                        </span>
                    </td>
                    <td class="product-price"><?= number_format((float)$product["unit_price"], 0, ',', '.') ?> đ</td>
                    <td><span class="visibility-status <?= $product["status"] ? 'is-visible' : 'is-hidden' ?>"><?= $product["status"] ? "Hiển thị" : "Ẩn" ?></span></td>
                    <td><div class="table-row-actions product-row-actions">
                            <a class="table-icon-action edit-icon-action" href="/products/edit?id=<?= e($product["id"]) ?>" aria-label="Sửa sản phẩm <?= e($product["name"]) ?>" title="Sửa">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10"/><path d="m15.5 5.5 3 3M10 14l8.8-8.8a2.1 2.1 0 0 1 3 3L13 17l-4 1 1-4Z"/></svg>
                            </a>
                            <form class="inline" method="POST" action="/products/delete" onsubmit="return confirmDelete('Bạn chắc chắn muốn xóa sản phẩm này?')">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($product["id"]) ?>"><button class="table-icon-action delete-icon-action" type="submit" aria-label="Xóa sản phẩm <?= e($product["name"]) ?>" title="Xóa">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16m-10 4v7m4-7v7M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?><tr><td colspan="8" class="empty">Không có sản phẩm phù hợp.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    $paginationParams = [
        "keyword" => $keyword,
        "category_id" => $categoryId,
        "brand_id" => $brandId,
    ];
    $firstPage = max(1, $currentPage - 2);
    $lastPage = min($totalPages, $currentPage + 2);
    ?>
    <?php if ($totalProducts > 0): ?>
    <div class="pagination">
        <span class="pagination-summary">
            Hiển thị <?= number_format(min(($currentPage - 1) * $perPage + 1, $totalProducts)) ?>
            - <?= number_format(min($currentPage * $perPage, $totalProducts)) ?>
            trên <?= number_format($totalProducts) ?> sản phẩm
        </span>
        <?php if ($totalPages > 1): ?>
        <nav class="pagination-links" aria-label="Phân trang sản phẩm">
            <?php if ($currentPage > 1): ?>
            <a class="btn small secondary" href="/products?<?= e(http_build_query($paginationParams + ["page" => $currentPage - 1])) ?>">Trước</a>
            <?php else: ?>
            <span class="btn small secondary disabled" aria-disabled="true">Trước</span>
            <?php endif; ?>

            <?php for ($page = $firstPage; $page <= $lastPage; $page++): ?>
            <?php if ($page === $currentPage): ?>
            <span class="btn small primary" aria-current="page"><?= $page ?></span>
            <?php else: ?>
            <a class="btn small secondary" href="/products?<?= e(http_build_query($paginationParams + ["page" => $page])) ?>"><?= $page ?></a>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
            <a class="btn small secondary" href="/products?<?= e(http_build_query($paginationParams + ["page" => $currentPage + 1])) ?>">Sau</a>
            <?php else: ?>
            <span class="btn small secondary disabled" aria-disabled="true">Sau</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>
