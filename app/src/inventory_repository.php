<?php
require_once __DIR__ . "/db.php";

function inventory_products(): array {
    return db()->query("SELECT id, name, sku, quantity FROM products ORDER BY name")->fetchAll();
}

function inventory_movements(string $keyword = "", ?string $type = null, int $limit = 200): array {
    $sql = "SELECT im.*, a.full_name AS admin_name
            FROM inventory_movements im
            LEFT JOIN admins a ON a.id = im.admin_id";
    $conditions = [];
    $params = [];

    if ($keyword !== "") {
        $conditions[] = "(im.product_name LIKE :product_name
                          OR im.product_sku LIKE :product_sku
                          OR im.note LIKE :note
                          OR CAST(im.id AS CHAR) LIKE :movement_id
                          OR CONCAT(CASE WHEN im.movement_type = 'in' THEN 'PN-' ELSE 'PX-' END,
                                    DATE_FORMAT(im.created_at, '%Y%m%d'),
                                    LPAD(im.id, 3, '0')) LIKE :voucher_code)";
        $search = "%" . $keyword . "%";
        $params["product_name"] = $search;
        $params["product_sku"] = $search;
        $params["note"] = $search;
        $params["movement_id"] = $search;
        $params["voucher_code"] = $search;
    }
    if ($type !== null) {
        $conditions[] = "im.movement_type = :movement_type";
        $params["movement_type"] = $type;
    }
    if ($conditions) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY im.id DESC LIMIT :limit";

    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":" . $key, $value);
    }
    $stmt->bindValue(":limit", max(1, min(500, $limit)), PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function inventory_summary_metrics(int $lowStockThreshold = 5): array {
    $monthStart = new DateTimeImmutable("first day of this month 00:00:00");
    $nextMonthStart = $monthStart->modify("first day of next month");
    $stmt = db()->prepare("
        SELECT
            (SELECT COALESCE(SUM(quantity), 0) FROM products) AS total_stock,
            (SELECT COUNT(*) FROM products WHERE quantity <= :low_stock_threshold) AS low_stock_products,
            (SELECT COUNT(*) FROM inventory_movements
             WHERE movement_type = 'in' AND created_at >= :month_start_in AND created_at < :next_month_start_in) AS incoming_this_month,
            (SELECT COUNT(*) FROM inventory_movements
             WHERE movement_type = 'out' AND created_at >= :month_start_out AND created_at < :next_month_start_out) AS outgoing_this_month
    ");
    $stmt->execute([
        "low_stock_threshold" => max(0, $lowStockThreshold),
        "month_start_in" => $monthStart->format("Y-m-d H:i:s"),
        "next_month_start_in" => $nextMonthStart->format("Y-m-d H:i:s"),
        "month_start_out" => $monthStart->format("Y-m-d H:i:s"),
        "next_month_start_out" => $nextMonthStart->format("Y-m-d H:i:s"),
    ]);
    $metrics = $stmt->fetch();

    return [
        "totalStock" => (int) $metrics["total_stock"],
        "lowStockProducts" => (int) $metrics["low_stock_products"],
        "incomingThisMonth" => (int) $metrics["incoming_this_month"],
        "outgoingThisMonth" => (int) $metrics["outgoing_this_month"],
        "lowStockThreshold" => max(0, $lowStockThreshold),
    ];
}

function inventory_create_movement(int $productId, string $type, int $quantity, string $note, int $adminId): void {
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $productStmt = $pdo->prepare(
            "SELECT id, name, sku, quantity FROM products WHERE id = :id FOR UPDATE"
        );
        $productStmt->execute(["id" => $productId]);
        $product = $productStmt->fetch();
        if (!$product) {
            throw new DomainException("Không tìm thấy sản phẩm.");
        }

        $before = (int) $product["quantity"];
        if ($type === "out" && $quantity > $before) {
            throw new DomainException(
                "Sản phẩm \"" . $product["name"] . "\" chỉ còn " . $before . " sản phẩm trong kho."
            );
        }
        $after = $type === "in" ? $before + $quantity : $before - $quantity;
        if ($after > 2147483647) {
            throw new DomainException("Tồn kho sau khi nhập vượt quá giới hạn.");
        }

        $stockStmt = $pdo->prepare("UPDATE products SET quantity = :quantity WHERE id = :id");
        $stockStmt->execute(["quantity" => $after, "id" => $productId]);

        $movementStmt = $pdo->prepare(
            "INSERT INTO inventory_movements
                (product_id, product_name, product_sku, movement_type, quantity, stock_before, stock_after, note, admin_id)
             VALUES
                (:product_id, :product_name, :product_sku, :movement_type, :quantity, :stock_before, :stock_after, :note, :admin_id)"
        );
        $movementStmt->execute([
            "product_id" => $productId,
            "product_name" => $product["name"],
            "product_sku" => $product["sku"],
            "movement_type" => $type,
            "quantity" => $quantity,
            "stock_before" => $before,
            "stock_after" => $after,
            "note" => $note !== "" ? $note : null,
            "admin_id" => $adminId,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
