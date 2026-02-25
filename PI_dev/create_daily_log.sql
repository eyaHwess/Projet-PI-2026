CREATE TABLE IF NOT EXISTS daily_activity_log (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    total_activities INT NOT NULL DEFAULT 0,
    completed_activities INT NOT NULL DEFAULT 0,
    total_routines INT NOT NULL DEFAULT 0,
    completed_routines INT NOT NULL DEFAULT 0,
    completion_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_user_date ON daily_activity_log (user_id, log_date);
