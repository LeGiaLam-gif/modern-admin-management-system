-- Chỉ chạy MỘT lần trên database hiện có sau migration 005.
-- Thêm token ghi nhớ đăng nhập và giới hạn số lần đăng nhập sai.
ALTER TABLE admins
    ADD COLUMN remember_token CHAR(64) DEFAULT NULL AFTER full_name,
    ADD COLUMN remember_expires_at DATETIME DEFAULT NULL AFTER remember_token;

CREATE TABLE IF NOT EXISTS admin_rate_limits (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    bucket VARCHAR(190) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_rate_bucket_created (bucket, created_at)
);
