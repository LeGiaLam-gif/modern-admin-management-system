<div class="dashboard-overview">
    <section class="dashboard-summary dashboard-primary-metrics" aria-label="Chỉ số kinh doanh chính">
        <article class="summary-card summary-revenue metric-revenue">
            <span>Doanh thu · <?= e($salesReport["periodLabel"]) ?></span>
            <strong><?= number_format($salesReport["totalRevenue"], 0, ',', '.') ?> <span class="metric-currency">đ</span></strong>
            <div class="revenue-comparison">
                <span class="<?= $salesReport["revenueGrowthPercent"] !== null && $salesReport["revenueGrowthPercent"] < 0 ? "growth-negative" : "growth-positive" ?>">
                    <?php if ($salesReport["revenueGrowthPercent"] !== null): ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $salesReport["revenueGrowthPercent"] < 0 ? 'm4 7 6 6 4-4 6 6M14 15h6v-6' : 'm4 17 6-6 4 4 6-6M14 9h6v6' ?>"/></svg>
                    <?php endif; ?>
                    <?= $salesReport["revenueGrowthPercent"] === null
                        ? ($salesReport["totalRevenue"] > 0 ? "Mới phát sinh" : "Chưa có dữ liệu")
                        : number_format($salesReport["revenueGrowthPercent"], 1, ',', '.') . "%" ?>
                </span>
                <span>So với kỳ trước</span>
            </div>
        </article>

        <article class="summary-card metric-orders">
            <div class="metric-card-heading">
                <div>
                    <span>Đơn hàng · <?= e($salesReport["periodLabel"]) ?></span>
                    <strong><?= number_format($salesReport["totalOrders"]) ?></strong>
                </div>
                <a class="metric-action-button order-add-button" href="/orders/create" aria-label="Tạo đơn hàng mới" title="Tạo đơn hàng mới">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1 12H5L4 8Z"/><path d="m8 8 4-5 4 5m-4 3v6m-3-3h6"/></svg>
                </a>
            </div>
            <div class="orders-card-details">
                <div><span>Sản phẩm đã bán</span><strong><?= number_format($salesReport["totalUnits"]) ?></strong></div>
                <div><span>Tỷ lệ hủy</span><strong class="cancellation-rate"><?= number_format($salesReport["cancellationRate"], 1, ',', '.') ?>%</strong></div>
            </div>
        </article>

        <article class="summary-card metric-aov">
            <span>Giá trị đơn TB (AOV)</span>
            <strong><?= number_format($salesReport["averageOrderValue"], 0, ',', '.') ?> <span class="metric-currency">đ</span></strong>
            <div class="customer-metrics">
                <div class="customer-new-metric">
                    <a class="metric-action-button customer-add-button" href="/customers/create" aria-label="Thêm khách hàng mới" title="Thêm khách hàng mới">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.5a6.5 6.5 0 0 1 13 0V20M19 8v6m-3-3h6"/></svg>
                    </a>
                    <div><span>Khách mới</span><strong><?= number_format($salesReport["newCustomers"]) ?></strong></div>
                </div>
                <div class="customer-returning-metric">
                    <span>Khách quay lại</span>
                    <strong><?= number_format($salesReport["customerCohorts"]["returning"]["customerCount"]) ?></strong>
                </div>
            </div>
        </article>
    </section>

    <section class="dashboard-overview-grid dashboard-chart-row" aria-label="Doanh thu và cơ cấu thương hiệu">
        <article class="dashboard-panel revenue-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Doanh thu &amp; số lượng đơn hàng</h2>
                    <p>Xu hướng trong <?= e($salesReport["periodLabel"]) ?></p>
                </div>
                <div class="chart-legend" aria-label="Chú giải biểu đồ">
                    <span><i class="legend-dot revenue-dot" aria-hidden="true"></i>Doanh thu</span>
                    <span><i class="legend-dot orders-dot" aria-hidden="true"></i>Số đơn</span>
                </div>
            </div>
            <div id="revenue-chart" class="chart-area">
                <canvas role="img" aria-label="Biểu đồ doanh thu và số đơn hàng"></canvas>
            </div>
        </article>

        <article class="dashboard-panel brand-revenue-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Cơ cấu theo thương hiệu</h2>
                    <p>Tỷ trọng doanh thu sản phẩm theo thương hiệu</p>
                </div>
            </div>
            <?php if (!$salesReport["brandRevenue"]): ?>
                <p class="dashboard-empty">Chưa có doanh thu trong khoảng thời gian này.</p>
            <?php else: ?>
                <div class="brand-donut-layout">
                    <div id="brand-donut" class="brand-donut">
                        <canvas role="img" aria-label="Biểu đồ tỷ trọng doanh thu theo thương hiệu"></canvas>
                        <div class="donut-center" aria-hidden="true"><strong></strong><span></span></div>
                    </div>
                    <ul id="brand-legend" class="brand-legend" aria-label="Doanh thu theo thương hiệu"></ul>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <section class="dashboard-grid dashboard-operations-grid">
        <article class="dashboard-panel order-status-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Trạng thái đơn hàng</h2>
                    <p><?= number_format($salesReport["allOrderCount"]) ?> đơn (<?= e($salesReport["periodLabel"]) ?>)</p>
                </div>
                <a class="panel-link" href="/orders">Tất cả đơn hàng <span aria-hidden="true">→</span></a>
            </div>
            <div class="order-status-list">
                <a class="order-status-item order-status-total" href="/orders">
                    <span class="status-indicator status-total" aria-hidden="true"></span>
                    <span>Tổng đơn hàng</span>
                    <strong><?= number_format($salesReport["allOrderCount"]) ?></strong>
                </a>
                <?php foreach ([
                    ["label" => "Chờ xác nhận", "count" => $salesReport["orderStatuses"]["Chờ xác nhận"] - ($salesReport["refundedOrdersByStatus"]["new"] ?? 0), "filter" => "pending", "status" => "pending"],
                    ["label" => "Đang xử lý / Giao", "count" => $salesReport["orderStatuses"]["Đã xác nhận"] + $salesReport["orderStatuses"]["Đang xử lý"] + $salesReport["orderStatuses"]["Đang giao"] - ($salesReport["refundedOrdersByStatus"]["confirmed"] ?? 0) - ($salesReport["refundedOrdersByStatus"]["processing"] ?? 0) - ($salesReport["refundedOrdersByStatus"]["shipping"] ?? 0), "filter" => "processing", "status" => "processing"],
                    ["label" => "Hoàn tất", "count" => $salesReport["orderStatuses"]["Hoàn tất"] - ($salesReport["refundedOrdersByStatus"]["completed"] ?? 0), "filter" => "completed", "status" => "completed"],
                    ["label" => "Đã hủy / Hoàn tiền", "count" => $salesReport["cancelledOrRefundedOrders"], "filter" => "cancelled_refunded", "status" => "cancelled"],
                ] as $status): ?>
                    <a class="order-status-item" href="/orders?status=<?= e($status["filter"]) ?>">
                        <span class="status-indicator status-<?= e($status["status"]) ?>" aria-hidden="true"></span>
                        <span><?= e($status["label"]) ?></span>
                        <strong><?= number_format($status["count"]) ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dashboard-panel inventory-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Tồn kho &amp; tốc độ tiêu thụ</h2>
                    <p>Dự báo dựa trên tốc độ bán 30 ngày gần nhất</p>
                </div>
                <a class="panel-link" href="/inventory">Quản lý kho <span aria-hidden="true">→</span></a>
            </div>
            <div class="dashboard-table-wrap inventory-table-wrap">
                <table class="dashboard-table">
                    <thead><tr><th>Sản phẩm</th><th>Tồn kho</th><th>Bán/ngày</th><th>Dự kiến hết &amp; cảnh báo</th></tr></thead>
                    <tbody>
                        <?php if (!$salesReport["inventory"]): ?>
                            <tr><td class="dashboard-empty" colspan="4">Chưa có sản phẩm.</td></tr>
                        <?php else: ?>
                            <?php foreach ($salesReport["inventory"] as $item): ?>
                                <tr>
                                    <td class="inventory-product">
                                        <strong><?= e($item["name"]) ?></strong>
                                        <?php if ($item["sku"]): ?><small><?= e($item["sku"]) ?></small><?php endif; ?>
                                    </td>
                                    <td><span class="data-count-pill"><?= number_format($item["quantity"]) ?></span></td>
                                    <td><span class="data-count-pill"><?= number_format($item["dailySales"], 1, ',', '.') ?></span></td>
                                    <td>
                                        <div class="stockout-forecast">
                                            <strong class="stockout-label alert-<?= e($item["alert"]) ?>">
                                                <?= $item["daysUntilStockout"] === null
                                                    ? ($item["quantity"] === 0 ? "Hết hàng" : "Chưa đủ dữ liệu")
                                                    : number_format($item["daysUntilStockout"], 0, ',', '.') . " ngày" ?>
                                            </strong>
                                            <div class="stockout-track" role="progressbar"
                                                 aria-label="<?= e("Dự báo tồn kho " . $item["name"]) ?>"
                                                 aria-valuemin="0" aria-valuemax="100"
                                                 aria-valuenow="<?= number_format($item["stockoutProgress"], 1, '.', '') ?>">
                                                <span class="stockout-fill alert-<?= e($item["alert"]) ?>" style="width: <?= number_format($item["stockoutProgress"], 1, '.', '') ?>%"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</div>
