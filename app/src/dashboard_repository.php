<?php
require_once __DIR__ . "/db.php";

function dashboard_sales_report(string $period): array {
    $periods = [
        "day" => ["count" => 30, "step" => "P1D", "group" => "day", "label" => "30 ngày gần nhất"],
        "week" => ["count" => 12, "step" => "P1W", "group" => "week", "label" => "12 tuần gần nhất"],
        "month" => ["count" => 12, "step" => "P1M", "group" => "month", "label" => "12 tháng gần nhất"],
        "quarter" => ["count" => 8, "step" => "P3M", "group" => "quarter", "label" => "8 quý gần nhất"],
        "year" => ["count" => 5, "step" => "P1Y", "group" => "year", "label" => "5 năm gần nhất"],
    ];
    if (!isset($periods[$period])) {
        $period = "month";
    }
    $config = $periods[$period];
    $today = new DateTimeImmutable("today");
    $currentStart = match ($period) {
        "week" => $today->modify("monday this week"),
        "month" => $today->modify("first day of this month"),
        "quarter" => $today->setDate(
            (int) $today->format("Y"),
            ((int) floor(((int) $today->format("n") - 1) / 3) * 3) + 1,
            1
        ),
        "year" => $today->setDate((int) $today->format("Y"), 1, 1),
        default => $today,
    };
    $start = $currentStart;
    for ($i = 1; $i < $config["count"]; $i++) {
        $start = $start->sub(new DateInterval($config["step"]));
    }
    $end = match ($period) {
        "week" => $currentStart->modify("+1 week"),
        "month" => $currentStart->modify("+1 month"),
        "quarter" => $currentStart->modify("+3 months"),
        "year" => $currentStart->modify("+1 year"),
        default => $today->modify("+1 day"),
    };
    $previousStart = $start;
    $previousEnd = $end;
    for ($i = 0; $i < $config["count"]; $i++) {
        $previousStart = $previousStart->sub(new DateInterval($config["step"]));
        $previousEnd = $previousEnd->sub(new DateInterval($config["step"]));
    }

    $bucketExpression = match ($config["group"]) {
        "week" => "DATE_FORMAT(DATE_SUB(DATE(o.created_at), INTERVAL WEEKDAY(o.created_at) DAY), '%Y-%m-%d')",
        "month" => "DATE_FORMAT(o.created_at, '%Y-%m-01')",
        "quarter" => "CONCAT(YEAR(o.created_at), '-', LPAD(((QUARTER(o.created_at) - 1) * 3) + 1, 2, '0'), '-01')",
        "year" => "DATE_FORMAT(o.created_at, '%Y-01-01')",
        default => "DATE_FORMAT(o.created_at, '%Y-%m-%d')",
    };
    $pdo = db();
    $params = ["start" => $start->format("Y-m-d H:i:s"), "end" => $end->format("Y-m-d H:i:s")];
    $revenueStmt = $pdo->prepare("
        SELECT {$bucketExpression} AS bucket,
               SUM(o.total_amount) AS revenue,
               COUNT(*) AS order_count
        FROM orders o
        WHERE o.status <> 'cancelled'
          AND o.created_at >= :start AND o.created_at < :end
        GROUP BY bucket
        ORDER BY bucket
    ");
    $revenueStmt->execute($params);
    $revenueRows = $revenueStmt->fetchAll();

    $labels = [];
    $buckets = [];
    $cursor = $start;
    for ($i = 0; $i < $config["count"]; $i++) {
        $key = $cursor->format("Y-m-d");
        $buckets[$key] = ["revenue" => 0.0, "orders" => 0];
        $labels[] = match ($config["group"]) {
            "week" => $cursor->format("d/m"),
            "month" => $cursor->format("m/Y"),
            "quarter" => "Q" . (int) ceil((int) $cursor->format("n") / 3) . " " . $cursor->format("Y"),
            "year" => $cursor->format("Y"),
            default => $cursor->format("d/m"),
        };
        $cursor = $cursor->add(new DateInterval($config["step"]));
    }
    foreach ($revenueRows as $row) {
        if (isset($buckets[$row["bucket"]])) {
            $buckets[$row["bucket"]] = [
                "revenue" => (float) $row["revenue"],
                "orders" => (int) $row["order_count"],
            ];
        }
    }

    $unitsStmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.quantity), 0)
        FROM order_items oi
        INNER JOIN orders o ON o.id = oi.order_id
        WHERE o.status <> 'cancelled'
          AND o.created_at >= :start AND o.created_at < :end
    ");
    $unitsStmt->execute($params);

    $previousStmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0)
        FROM orders
        WHERE status <> 'cancelled'
          AND created_at >= :previous_start AND created_at < :previous_end
    ");
    $previousStmt->execute([
        "previous_start" => $previousStart->format("Y-m-d H:i:s"),
        "previous_end" => $previousEnd->format("Y-m-d H:i:s"),
    ]);

    $statusStmt = $pdo->prepare("
        SELECT status,
               COUNT(*) AS order_count,
               SUM(payment_status = 'refunded') AS refunded_count,
               SUM(status = 'cancelled' OR payment_status = 'refunded') AS cancelled_or_refunded_count
        FROM orders
        WHERE created_at >= :start AND created_at < :end
        GROUP BY status
    ");
    $statusStmt->execute($params);
    $statusLabels = [
        "new" => "Chờ xác nhận",
        "confirmed" => "Đã xác nhận",
        "processing" => "Đang xử lý",
        "shipping" => "Đang giao",
        "completed" => "Hoàn tất",
        "cancelled" => "Đã hủy",
    ];
    $orderStatuses = array_fill_keys(array_values($statusLabels), 0);
    $allOrderCount = 0;
    $cancelledOrders = 0;
    $cancelledOrRefundedOrders = 0;
    $refundedOrdersByStatus = [];
    foreach ($statusStmt->fetchAll() as $row) {
        $status = (string) $row["status"];
        $count = (int) $row["order_count"];
        $label = $statusLabels[$status] ?? $status;
        $orderStatuses[$label] = ($orderStatuses[$label] ?? 0) + $count;
        $allOrderCount += $count;
        $cancelledOrRefundedOrders += (int) $row["cancelled_or_refunded_count"];
        $refundedOrdersByStatus[$status] = (int) $row["refunded_count"];
        if ($status === "cancelled") {
            $cancelledOrders = $count;
        }
    }
    $brandStmt = $pdo->prepare("
        SELECT COALESCE(b.name, 'Chưa gán thương hiệu') AS name,
               SUM(oi.line_total) AS revenue
        FROM order_items oi
        INNER JOIN orders o ON o.id = oi.order_id
        LEFT JOIN products p ON p.id = oi.product_id
        LEFT JOIN brands b ON b.id = p.brand_id
        WHERE o.status <> 'cancelled'
          AND o.created_at >= :start AND o.created_at < :end
        GROUP BY COALESCE(b.name, 'Chưa gán thương hiệu')
        ORDER BY revenue DESC, name ASC
    ");
    $brandStmt->execute($params);
    $brandRevenue = $brandStmt->fetchAll();
    $brandTotalRevenue = array_sum(array_map(
        static fn (array $brand): float => (float) $brand["revenue"],
        $brandRevenue
    ));

    $customerKey = "CASE
        WHEN o.customer_id IS NOT NULL THEN CONCAT('customer:', o.customer_id)
        ELSE CONCAT('phone:', o.customer_phone)
    END";
    $cohortStmt = $pdo->prepare("
        SELECT cohorts.segment,
               COUNT(*) AS customer_count,
               SUM(cohorts.order_count) AS order_count,
               SUM(cohorts.revenue) AS revenue
        FROM (
            SELECT period_customers.customer_key,
                   period_customers.order_count,
                   period_customers.revenue,
                   CASE
                       WHEN first_orders.first_order_at >= :cohort_start
                        AND first_orders.first_order_at < :cohort_end THEN 'new'
                       ELSE 'returning'
                   END AS segment
            FROM (
                SELECT {$customerKey} AS customer_key,
                       COUNT(*) AS order_count,
                       SUM(o.total_amount) AS revenue
                FROM orders o
                WHERE o.status <> 'cancelled'
                  AND o.created_at >= :period_start AND o.created_at < :period_end
                GROUP BY {$customerKey}
            ) period_customers
            INNER JOIN (
                SELECT {$customerKey} AS customer_key,
                       MIN(o.created_at) AS first_order_at
                FROM orders o
                WHERE o.status <> 'cancelled'
                GROUP BY {$customerKey}
            ) first_orders ON first_orders.customer_key = period_customers.customer_key
        ) cohorts
        GROUP BY cohorts.segment
    ");
    $cohortStmt->execute([
        "cohort_start" => $start->format("Y-m-d H:i:s"),
        "cohort_end" => $end->format("Y-m-d H:i:s"),
        "period_start" => $start->format("Y-m-d H:i:s"),
        "period_end" => $end->format("Y-m-d H:i:s"),
    ]);
    $customerCohorts = [
        "new" => ["customerCount" => 0, "orderCount" => 0, "revenue" => 0.0, "averageOrderValue" => 0.0],
        "returning" => ["customerCount" => 0, "orderCount" => 0, "revenue" => 0.0, "averageOrderValue" => 0.0],
    ];
    foreach ($cohortStmt->fetchAll() as $row) {
        $orderCount = (int) $row["order_count"];
        $revenue = (float) $row["revenue"];
        $customerCohorts[$row["segment"]] = [
            "customerCount" => (int) $row["customer_count"],
            "orderCount" => $orderCount,
            "revenue" => $revenue,
            "averageOrderValue" => $orderCount > 0 ? $revenue / $orderCount : 0.0,
        ];
    }

    $inventoryEnd = new DateTimeImmutable("now");
    $inventoryStart = $inventoryEnd->sub(new DateInterval("P30D"));
    $inventoryStmt = $pdo->prepare("
        SELECT p.id, p.name, p.sku, p.quantity,
               COALESCE(SUM(CASE WHEN o.id IS NOT NULL THEN oi.quantity ELSE 0 END), 0) AS sold_units
        FROM products p
        LEFT JOIN order_items oi ON oi.product_id = p.id
        LEFT JOIN orders o
            ON o.id = oi.order_id
           AND o.status <> 'cancelled'
           AND o.created_at >= :inventory_start
           AND o.created_at < :inventory_end
        GROUP BY p.id, p.name, p.sku, p.quantity
        ORDER BY p.quantity ASC, sold_units DESC, p.name ASC
    ");
    $inventoryStmt->execute([
        "inventory_start" => $inventoryStart->format("Y-m-d H:i:s"),
        "inventory_end" => $inventoryEnd->format("Y-m-d H:i:s"),
    ]);
    $inventory = [];
    foreach ($inventoryStmt->fetchAll() as $row) {
        $quantity = (int) $row["quantity"];
        $soldUnits = (int) $row["sold_units"];
        $dailySales = $soldUnits / 30;
        $daysUntilStockout = $dailySales > 0 ? $quantity / $dailySales : null;
        $alert = $quantity === 0 ? "out" : (
            $daysUntilStockout === null ? "stagnant" : (
                $daysUntilStockout <= 60 ? "critical" : (
                    $daysUntilStockout <= 120 ? "warning" : "ok"
                )
            )
        );
        $stockoutProgress = match ($alert) {
            "out" => 5.0,
            "critical" => max(0.0, min(30.0, $daysUntilStockout / 2)),
            "warning" => 30.0 + min(40.0, ($daysUntilStockout - 60) / 1.5),
            "ok" => min(100.0, 70.0 + (($daysUntilStockout - 120) / 12)),
            default => 0.0,
        };
        $inventory[] = [
            "id" => (int) $row["id"],
            "name" => (string) $row["name"],
            "sku" => $row["sku"],
            "quantity" => $quantity,
            "soldUnits" => $soldUnits,
            "dailySales" => $dailySales,
            "daysUntilStockout" => $daysUntilStockout,
            "stockoutProgress" => $stockoutProgress,
            "alert" => $alert,
        ];
    }

    $totalRevenue = array_sum(array_column($buckets, "revenue"));
    $totalOrders = array_sum(array_column($buckets, "orders"));
    $previousRevenue = (float) $previousStmt->fetchColumn();
    return [
        "period" => $period,
        "periodLabel" => $config["label"],
        "labels" => $labels,
        "buckets" => $buckets,
        "totalRevenue" => $totalRevenue,
        "previousRevenue" => $previousRevenue,
        "revenueGrowthPercent" => $previousRevenue > 0
            ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100
            : null,
        "totalOrders" => $totalOrders,
        "allOrderCount" => $allOrderCount,
        "cancelledOrders" => $cancelledOrders,
        "cancelledOrRefundedOrders" => $cancelledOrRefundedOrders,
        "refundedOrdersByStatus" => $refundedOrdersByStatus,
        "cancellationRate" => $allOrderCount > 0 ? ($cancelledOrders / $allOrderCount) * 100 : 0.0,
        "averageOrderValue" => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0.0,
        "newCustomers" => $customerCohorts["new"]["customerCount"],
        "customerCohorts" => $customerCohorts,
        "totalUnits" => (int) $unitsStmt->fetchColumn(),
        "orderStatuses" => $orderStatuses,
        "brandRevenue" => array_map(static function (array $brand) use ($brandTotalRevenue): array {
            $brand["revenue"] = (float) $brand["revenue"];
            $brand["sharePercent"] = $brandTotalRevenue > 0
                ? ($brand["revenue"] / $brandTotalRevenue) * 100
                : 0.0;
            return $brand;
        }, $brandRevenue),
        "inventory" => $inventory,
    ];
}
