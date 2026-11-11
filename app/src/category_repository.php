<?php
require_once __DIR__ . "/db.php";
function category_all(string $keyword = "", ?int $status = null): array {
    $sql = "SELECT c.*,
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
            FROM categories c";
    $conditions = [];
    $params = [];

    if ($keyword !== "") {
        $conditions[] = "c.name LIKE :keyword";
        $params["keyword"] = "%" . $keyword . "%";
    }
    if ($status !== null) {
        $conditions[] = "c.status = :status";
        $params["status"] = $status;
    }
    if ($conditions) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY c.id DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function category_summary_metrics(): array {
    $metrics = db()->query("
        SELECT
            (SELECT COUNT(*) FROM categories) AS total_categories,
            (SELECT COUNT(*) FROM categories WHERE status = 1) AS active_categories,
            (SELECT COALESCE(ROUND(AVG(category_products.product_count)), 0)
             FROM (
                 SELECT c.id, COUNT(p.id) AS product_count
                 FROM categories c
                 LEFT JOIN products p ON p.category_id = c.id
                 GROUP BY c.id
             ) AS category_products) AS average_products
    ")->fetch();

    return [
        "totalCategories" => (int) $metrics["total_categories"],
        "activeCategories" => (int) $metrics["active_categories"],
        "averageProducts" => (int) $metrics["average_products"],
    ];
}
function category_active_all(): array { return db()->query("SELECT * FROM categories WHERE status=1 ORDER BY name ASC")->fetchAll(); }
function category_find(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id=:id LIMIT 1");
    $stmt->execute(["id"=>$id]); $r=$stmt->fetch(); return $r ?: null;
}
function category_create(string $name, ?string $description, int $status): int {
    $stmt=db()->prepare("INSERT INTO categories(name,description,status) VALUES(:n,:d,:s)");
    $stmt->execute(["n"=>$name,"d"=>$description,"s"=>$status]);
    return (int) db()->lastInsertId();
}
function category_update(int $id, string $name, ?string $description, int $status): void {
    $stmt=db()->prepare("UPDATE categories SET name=:n, description=:d, status=:s WHERE id=:id");
    $stmt->execute(["id"=>$id,"n"=>$name,"d"=>$description,"s"=>$status]);
}
function category_delete(int $id): void {
    $stmt=db()->prepare("DELETE FROM categories WHERE id=:id");
    $stmt->execute(["id"=>$id]);
}
function category_count(): int { return (int) db()->query("SELECT COUNT(*) FROM categories")->fetchColumn(); }
