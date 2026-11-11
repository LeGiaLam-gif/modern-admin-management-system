USE admin_product_db;

ALTER TABLE orders
    ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER status,
    ADD INDEX idx_orders_payment_status (payment_status, created_at);
