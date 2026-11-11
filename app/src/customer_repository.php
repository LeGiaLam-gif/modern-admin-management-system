<?php
require_once __DIR__ . "/db.php";

function customer_all(string $keyword = ""): array {
    $sql = "SELECT * FROM customers";
    $params = [];
    if ($keyword !== "") {
        $sql .= " WHERE name LIKE :name_keyword
                  OR email LIKE :email_keyword
                  OR phone LIKE :phone_keyword";
        $search = "%" . $keyword . "%";
        $params = [
            "name_keyword" => $search,
            "email_keyword" => $search,
            "phone_keyword" => $search,
        ];
    }
    $sql .= " ORDER BY id DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function customer_summary_metrics(): array {
    $monthStart = new DateTimeImmutable("first day of this month 00:00:00");
    $nextMonthStart = $monthStart->modify("first day of next month");
    $stmt = db()->prepare("
        SELECT
            (SELECT COUNT(*) FROM customers) AS total_customers,
            (SELECT COUNT(*)
             FROM customers
             WHERE created_at >= :month_start AND created_at < :next_month_start) AS new_customers,
            (SELECT COUNT(*)
             FROM (
                 SELECT o.customer_id
                 FROM orders o
                 WHERE o.customer_id IS NOT NULL AND o.status <> 'cancelled'
                 GROUP BY o.customer_id
                 HAVING COUNT(*) >= 2
             ) AS returning_customers) AS returning_customers,
            (SELECT COUNT(*)
             FROM (
                 SELECT o.customer_id
                 FROM orders o
                 WHERE o.customer_id IS NOT NULL AND o.status <> 'cancelled'
                 GROUP BY o.customer_id
             ) AS purchasing_customers) AS purchasing_customers
    ");
    $stmt->execute([
        "month_start" => $monthStart->format("Y-m-d H:i:s"),
        "next_month_start" => $nextMonthStart->format("Y-m-d H:i:s"),
    ]);
    $metrics = $stmt->fetch();
    $purchasingCustomers = (int) $metrics["purchasing_customers"];
    $returningCustomers = (int) $metrics["returning_customers"];

    return [
        "totalCustomers" => (int) $metrics["total_customers"],
        "newThisMonth" => (int) $metrics["new_customers"],
        "returningRate" => $purchasingCustomers > 0
            ? ($returningCustomers / $purchasingCustomers) * 100
            : 0.0,
    ];
}

function customer_find(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM customers WHERE id = :id LIMIT 1");
    $stmt->execute(["id" => $id]);
    $customer = $stmt->fetch();
    return $customer ?: null;
}

function customer_create(array $customer): int {
    $stmt = db()->prepare(
        "INSERT INTO customers (name, phone, email, address, notes)
         VALUES (:name, :phone, :email, :address, :notes)"
    );
    $stmt->execute($customer);
    return (int) db()->lastInsertId();
}

function customer_update(int $id, array $customer): void {
    $customer["id"] = $id;
    $stmt = db()->prepare(
        "UPDATE customers
         SET name = :name, phone = :phone, email = :email, address = :address, notes = :notes
         WHERE id = :id"
    );
    $stmt->execute($customer);
}
