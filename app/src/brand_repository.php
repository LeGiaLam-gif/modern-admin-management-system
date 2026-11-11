<?php
require_once __DIR__ . "/db.php";

function brand_all(string $keyword = "", ?int $status = null): array {
    $sql = "SELECT b.*,
                   (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id) AS product_count
            FROM brands b";
    $conditions = [];
    $params = [];

    if ($keyword !== "") {
        $conditions[] = "b.name LIKE :keyword";
        $params["keyword"] = "%" . $keyword . "%";
    }
    if ($status !== null) {
        $conditions[] = "b.status = :status";
        $params["status"] = $status;
    }
    if ($conditions) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY b.id DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function brand_summary_metrics(): array {
    $monthStart = new DateTimeImmutable("first day of this month 00:00:00");
    $nextMonthStart = $monthStart->modify("first day of next month");
    $stmt = db()->prepare("
        SELECT
            (SELECT COUNT(*) FROM brands) AS total_brands,
            (SELECT COUNT(*) FROM brands
             WHERE created_at >= :month_start AND created_at < :next_month_start) AS new_brands,
            (SELECT b.name
             FROM brands b
             LEFT JOIN products p ON p.brand_id = b.id
             GROUP BY b.id, b.name
             ORDER BY COUNT(p.id) DESC, b.name ASC
             LIMIT 1) AS top_brand,
            (SELECT COUNT(*)
             FROM products p
             INNER JOIN brands b ON b.id = p.brand_id
             GROUP BY b.id, b.name
             ORDER BY COUNT(*) DESC, b.name ASC
             LIMIT 1) AS top_brand_product_count
    ");
    $stmt->execute([
        "month_start" => $monthStart->format("Y-m-d H:i:s"),
        "next_month_start" => $nextMonthStart->format("Y-m-d H:i:s"),
    ]);
    $metrics = $stmt->fetch();

    return [
        "totalBrands" => (int) $metrics["total_brands"],
        "newThisMonth" => (int) $metrics["new_brands"],
        "topBrand" => $metrics["top_brand"] !== null ? (string) $metrics["top_brand"] : "Chưa có dữ liệu",
        "topBrandProductCount" => (int) ($metrics["top_brand_product_count"] ?? 0),
    ];
}

function brand_find(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM brands WHERE id = :id LIMIT 1");
    $stmt->execute(["id" => $id]);
    $brand = $stmt->fetch();
    return $brand ?: null;
}

function brand_create(string $name, ?string $description, int $status): int {
    $stmt = db()->prepare("INSERT INTO brands(name, description, status) VALUES(:name, :description, :status)");
    $stmt->execute(["name" => $name, "description" => $description, "status" => $status]);
    return (int) db()->lastInsertId();
}

function brand_update(int $id, string $name, ?string $description, int $status): void {
    $stmt = db()->prepare("UPDATE brands SET name = :name, description = :description, status = :status WHERE id = :id");
    $stmt->execute(["id" => $id, "name" => $name, "description" => $description, "status" => $status]);
}

function brand_delete(int $id): void {
    $stmt = db()->prepare("DELETE FROM brands WHERE id = :id");
    $stmt->execute(["id" => $id]);
}

function brand_product_count(int $id): int {
    $stmt = db()->prepare("SELECT COUNT(*) FROM products WHERE brand_id = :id");
    $stmt->execute(["id" => $id]);
    return (int) $stmt->fetchColumn();
}

function brand_name_exists(string $name, int $exceptId = 0): bool {
    $stmt = db()->prepare("SELECT 1 FROM brands WHERE name = :name AND id <> :except_id LIMIT 1");
    $stmt->execute(["name" => $name, "except_id" => $exceptId]);
    return (bool) $stmt->fetchColumn();
}
