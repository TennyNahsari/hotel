-- ============================================================================
-- SQL Schema Initialization for PostgreSQL
-- Aplikasi Hotel Management System
--
-- KETERANGAN AKSES ADMIN (DEFAULT LOGIN):
-- Email: owner@hotel.com
-- Password: password
-- ============================================================================

BEGIN;

-- 1. Create Migrations Tracking Table
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
);

-- 2. Create Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    permissions JSONB NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 3. Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    role_id BIGINT NULL REFERENCES roles(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NULL,
    email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 4. Create Password Reset Tokens Table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 5. Create Failed Jobs Table
CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 6. Create Personal Access Tokens Table (Sanctum)
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    expires_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens(tokenable_type, tokenable_id);

-- 7. Create Room Types Table
CREATE TABLE IF NOT EXISTS room_types (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    base_price NUMERIC(12, 2) NOT NULL,
    capacity INT NOT NULL,
    facilities JSONB NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 8. Create Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    id BIGSERIAL PRIMARY KEY,
    room_type_id BIGINT NOT NULL REFERENCES room_types(id) ON DELETE RESTRICT,
    room_number VARCHAR(255) NOT NULL UNIQUE,
    floor VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'available' CHECK (status IN ('available', 'booked', 'occupied', 'dirty', 'cleaning', 'out_of_order')),
    notes TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 9. Create Guests Table
CREATE TABLE IF NOT EXISTS guests (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(255) NOT NULL,
    id_card_type VARCHAR(255) NULL,
    id_card_number VARCHAR(255) NULL,
    address TEXT NULL,
    nationality VARCHAR(255) NOT NULL DEFAULT 'Indonesia',
    birth_date DATE NULL,
    preferences JSONB NULL,
    is_repeat_guest BOOLEAN NOT NULL DEFAULT FALSE,
    total_stays INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS guests_phone_index ON guests(phone);
CREATE INDEX IF NOT EXISTS guests_email_index ON guests(email);

-- 10. Create Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id BIGSERIAL PRIMARY KEY,
    booking_number VARCHAR(255) NOT NULL UNIQUE,
    guest_id BIGINT NOT NULL REFERENCES guests(id) ON DELETE RESTRICT,
    created_by BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'walk_in' CHECK (source IN ('walk_in', 'phone', 'website', 'ota')),
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    nights INT NOT NULL,
    adults INT NOT NULL DEFAULT 1,
    children INT NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled')),
    breakfast_status VARCHAR(50) NOT NULL DEFAULT 'not_taken',
    breakfast_date DATE NULL,
    total_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    deposit_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    payment_due_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    notes TEXT NULL,
    special_requests TEXT NULL,
    checked_in_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    checked_out_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS bookings_booking_number_index ON bookings(booking_number);
CREATE INDEX IF NOT EXISTS bookings_check_in_date_index ON bookings(check_in_date);
CREATE INDEX IF NOT EXISTS bookings_status_index ON bookings(status);

-- 11. Create Booking Rooms Table
CREATE TABLE IF NOT EXISTS booking_rooms (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    room_id BIGINT NOT NULL REFERENCES rooms(id) ON DELETE RESTRICT,
    check_in_date DATE NULL,
    check_out_date DATE NULL,
    room_rate NUMERIC(12, 2) NOT NULL,
    nights INT NOT NULL,
    subtotal NUMERIC(12, 2) NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    CONSTRAINT booking_rooms_booking_id_room_id_unique UNIQUE (booking_id, room_id)
);

-- 12. Create Halls Table
CREATE TABLE IF NOT EXISTS halls (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    hall_type VARCHAR(50) NOT NULL,
    floor VARCHAR(20) NULL,
    capacity INT NOT NULL,
    area_sqm NUMERIC(10, 2) NULL,
    price_per_hour NUMERIC(12, 2) NOT NULL,
    facilities JSONB NULL,
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'available' CHECK (status IN ('available', 'booked', 'occupied', 'maintenance', 'unavailable', 'cleaning', 'dirty')),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS halls_status_index ON halls(status);
CREATE INDEX IF NOT EXISTS halls_hall_type_index ON halls(hall_type);

-- 13. Create Hall Bookings Table
CREATE TABLE IF NOT EXISTS hall_bookings (
    id BIGSERIAL PRIMARY KEY,
    booking_number VARCHAR(50) NOT NULL UNIQUE,
    hall_id BIGINT NOT NULL REFERENCES halls(id) ON DELETE RESTRICT,
    guest_id BIGINT NULL REFERENCES guests(id) ON DELETE SET NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_company VARCHAR(100) NULL,
    event_name VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    start_time TIME WITHOUT TIME ZONE NOT NULL,
    end_time TIME WITHOUT TIME ZONE NOT NULL,
    duration_hours NUMERIC(5, 2) NOT NULL,
    attendees INT NOT NULL,
    total_amount NUMERIC(12, 2) NOT NULL,
    deposit_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    payment_due_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'checked_in', 'completed', 'cancelled')),
    special_requests TEXT NULL,
    notes TEXT NULL,
    booked_by BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS hall_bookings_event_date_start_time_end_time_index ON hall_bookings(event_date, start_time, end_time);
CREATE INDEX IF NOT EXISTS hall_bookings_hall_id_event_date_status_index ON hall_bookings(hall_id, event_date, status);
CREATE INDEX IF NOT EXISTS hall_bookings_guest_id_index ON hall_bookings(guest_id);
CREATE INDEX IF NOT EXISTS hall_bookings_status_index ON hall_bookings(status);

-- 14. Create Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NULL REFERENCES bookings(id) ON DELETE RESTRICT,
    hall_booking_id BIGINT NULL REFERENCES hall_bookings(id) ON DELETE RESTRICT,
    payment_number VARCHAR(255) NOT NULL UNIQUE,
    payment_type VARCHAR(50) NOT NULL CHECK (payment_type IN ('deposit', 'partial', 'full', 'refund', 'extra_charge')),
    payment_method VARCHAR(50) NOT NULL CHECK (payment_method IN ('cash', 'transfer', 'qris', 'card', 'other')),
    amount NUMERIC(12, 2) NOT NULL,
    restaurant_charges NUMERIC(10, 2) NOT NULL DEFAULT 0,
    laundry_charges NUMERIC(10, 2) NOT NULL DEFAULT 0,
    reference_number VARCHAR(255) NULL,
    receipt_path VARCHAR(255) NULL,
    notes TEXT NULL,
    processed_by BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    CONSTRAINT payments_booking_check CHECK (NOT (booking_id IS NOT NULL AND hall_booking_id IS NOT NULL))
);
CREATE INDEX IF NOT EXISTS payments_payment_number_index ON payments(payment_number);
CREATE INDEX IF NOT EXISTS payments_booking_id_index ON payments(booking_id);
CREATE INDEX IF NOT EXISTS payments_hall_booking_id_index ON payments(hall_booking_id);

-- 15. Create Housekeeping Tasks Table
CREATE TABLE IF NOT EXISTS housekeeping_tasks (
    id BIGSERIAL PRIMARY KEY,
    room_id BIGINT NULL REFERENCES rooms(id) ON DELETE CASCADE,
    hall_id BIGINT NULL REFERENCES halls(id) ON DELETE RESTRICT,
    task_type VARCHAR(50) NOT NULL DEFAULT 'cleaning' CHECK (task_type IN ('cleaning', 'inspection', 'maintenance', 'hall_cleaning')),
    priority VARCHAR(50) NOT NULL DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed')),
    assigned_to BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    notes TEXT NULL,
    started_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    completed_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS housekeeping_tasks_room_id_index ON housekeeping_tasks(room_id);
CREATE INDEX IF NOT EXISTS housekeeping_tasks_hall_id_index ON housekeeping_tasks(hall_id);
CREATE INDEX IF NOT EXISTS housekeeping_tasks_status_index ON housekeeping_tasks(status);
CREATE INDEX IF NOT EXISTS housekeeping_tasks_assigned_to_index ON housekeeping_tasks(assigned_to);

-- 16. Create Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL CHECK (category IN ('food', 'beverage', 'snack', 'package')),
    price NUMERIC(10, 2) NOT NULL,
    description TEXT NULL,
    photo VARCHAR(255) NULL,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 17. Create Restaurant Orders Table
CREATE TABLE IF NOT EXISTS restaurant_orders (
    id BIGSERIAL PRIMARY KEY,
    order_number VARCHAR(255) NOT NULL UNIQUE,
    booking_id BIGINT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    hall_booking_id BIGINT NULL REFERENCES hall_bookings(id) ON DELETE CASCADE,
    customer_name VARCHAR(255) NULL,
    total_amount NUMERIC(10, 2) NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'preparing', 'delivered', 'cancelled')),
    notes TEXT NULL,
    created_by BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS restaurant_orders_booking_id_index ON restaurant_orders(booking_id);
CREATE INDEX IF NOT EXISTS restaurant_orders_hall_booking_id_index ON restaurant_orders(hall_booking_id);
CREATE INDEX IF NOT EXISTS restaurant_orders_order_number_index ON restaurant_orders(order_number);

-- 18. Create Restaurant Order Items Table
CREATE TABLE IF NOT EXISTS restaurant_order_items (
    id BIGSERIAL PRIMARY KEY,
    restaurant_order_id BIGINT NOT NULL REFERENCES restaurant_orders(id) ON DELETE CASCADE,
    menu_item_id BIGINT NOT NULL REFERENCES menu_items(id) ON DELETE CASCADE,
    quantity INT NOT NULL DEFAULT 1,
    price NUMERIC(10, 2) NOT NULL,
    subtotal NUMERIC(10, 2) NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS restaurant_order_items_restaurant_order_id_index ON restaurant_order_items(restaurant_order_id);
CREATE INDEX IF NOT EXISTS restaurant_order_items_menu_item_id_index ON restaurant_order_items(menu_item_id);

-- 19. Create Laundry Orders Table
CREATE TABLE IF NOT EXISTS laundry_orders (
    id BIGSERIAL PRIMARY KEY,
    order_number VARCHAR(255) NOT NULL UNIQUE,
    booking_id BIGINT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    hall_booking_id BIGINT NULL REFERENCES hall_bookings(id) ON DELETE CASCADE,
    weight_kg NUMERIC(8, 2) NOT NULL,
    price_per_kg NUMERIC(10, 2) NOT NULL,
    total_amount NUMERIC(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'delivered', 'cancelled')),
    notes TEXT NULL,
    created_by BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS laundry_orders_booking_id_index ON laundry_orders(booking_id);
CREATE INDEX IF NOT EXISTS laundry_orders_hall_booking_id_index ON laundry_orders(hall_booking_id);
CREATE INDEX IF NOT EXISTS laundry_orders_order_number_index ON laundry_orders(order_number);

-- 20. Create AI Predictions Table
CREATE TABLE IF NOT EXISTS ai_predictions (
    id BIGSERIAL PRIMARY KEY,
    prediction_type VARCHAR(50) NOT NULL,
    prediction_data JSONB NOT NULL,
    confidence_score NUMERIC(3, 2) NULL,
    generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    expires_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS ai_predictions_prediction_type_generated_at_index ON ai_predictions(prediction_type, generated_at);

-- 21. Create ML Model Versions Table
CREATE TABLE IF NOT EXISTS ml_model_versions (
    id BIGSERIAL PRIMARY KEY,
    model_name VARCHAR(50) NOT NULL,
    version VARCHAR(20) NOT NULL,
    accuracy NUMERIC(5, 4) NULL,
    trained_samples INT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    trained_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS ml_model_versions_model_name_is_active_index ON ml_model_versions(model_name, is_active);
CREATE INDEX IF NOT EXISTS ml_model_versions_trained_at_index ON ml_model_versions(trained_at);

-- 22. Create Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);

-- 23. Create Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    model_type VARCHAR(255) NULL,
    model_id BIGINT NULL,
    old_values JSONB NULL,
    new_values JSONB NULL,
    ip_address VARCHAR(255) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS audit_logs_user_id_index ON audit_logs(user_id);
CREATE INDEX IF NOT EXISTS audit_logs_action_index ON audit_logs(action);
CREATE INDEX IF NOT EXISTS audit_logs_model_type_model_id_index ON audit_logs(model_type, model_id);


-- ============================================================================
-- INITIAL DATA SEEDING
-- ============================================================================

-- Insert Default Roles
INSERT INTO roles (id, name, display_name, description, permissions, created_at, updated_at) VALUES
(1, 'owner', 'Owner', 'Full access to all features', '["all"]'::jsonb, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'manager', 'Manager', 'Manage operations and view reports', '["view_dashboard", "view_reports", "manage_bookings", "manage_rooms", "manage_staff"]'::jsonb, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'front_office', 'Front Office', 'Handle reservations, check-in, check-out', '["manage_bookings", "manage_guests", "view_rooms", "process_payments"]'::jsonb, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 'housekeeping', 'Housekeeping', 'Manage room cleaning and maintenance', '["view_tasks", "update_room_status"]'::jsonb, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 'accounting', 'Accounting', 'Handle payments and financial reports', '["process_payments", "view_reports", "manage_payments"]'::jsonb, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

-- Adjust sequence for roles table
SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles));

-- Insert Default Users (Password for all users: "password")
-- Hashed using Bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (id, role_id, name, email, phone, password, is_active, created_at, updated_at) VALUES
(1, 1, 'Admin Owner', 'owner@hotel.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 3, 'Front Desk', 'frontdesk@hotel.com', '081234567891', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 4, 'Housekeeping Staff', 'housekeeping@hotel.com', '081234567892', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (email) DO NOTHING;

-- Adjust sequence for users table
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

-- Register migrations to prevent Laravel migration collisions
INSERT INTO migrations (migration, batch) VALUES
('2014_10_12_000000_create_users_table', 1),
('2014_10_12_100000_create_password_reset_tokens_table', 1),
('2019_08_19_000000_create_failed_jobs_table', 1),
('2019_12_14_000001_create_personal_access_tokens_table', 1),
('2026_01_02_081140_create_roles_table', 1),
('2026_01_02_081141_add_role_to_users_table', 1),
('2026_01_02_081142_create_room_types_table', 1),
('2026_01_02_081144_create_rooms_table', 1),
('2026_01_02_081145_create_guests_table', 1),
('2026_01_02_081146_create_bookings_table', 1),
('2026_01_02_081147_create_booking_rooms_table', 1),
('2026_01_02_081149_create_payments_table', 1),
('2026_01_02_081150_create_housekeeping_tasks_table', 1),
('2026_01_02_081151_create_audit_logs_table', 1),
('2026_02_20_000000_remove_weekend_price_from_room_types_table', 1),
('2026_02_25_000001_create_halls_table', 1),
('2026_02_25_000002_create_hall_bookings_table', 1),
('2026_02_25_000003_add_hall_booking_id_to_payments_table', 1),
('2026_02_25_000004_add_hall_support_to_housekeeping_tasks', 1),
('2026_02_25_155944_make_booking_id_nullable_in_payments_table', 1),
('2026_02_26_000001_add_breakfast_columns_to_bookings_table', 1),
('2026_02_26_000002_create_menu_items_table', 1),
('2026_02_26_000003_create_restaurant_orders_table', 1),
('2026_02_26_000004_create_restaurant_order_items_table', 1),
('2026_02_26_000005_add_restaurant_charges_to_payments_table', 1),
('2026_02_26_000010_add_package_category_to_menu_items', 1),
('2026_02_26_000011_add_laundry_charges_to_payments_table', 1),
('2026_02_26_000012_create_laundry_orders_table', 1),
('2026_02_26_000013_add_hall_booking_id_to_restaurant_orders', 1),
('2026_03_09_142033_create_ai_predictions_table', 1),
('2026_03_09_142046_create_ml_model_versions_table', 1),
('2026_08_23_072340_add_receipt_path_to_payments_table', 1),
('2026_08_23_173000_add_booked_status_to_rooms_table', 1),
('2026_08_23_184000_update_halls_and_hall_bookings_statuses', 1),
('2026_08_23_184500_make_booked_by_nullable_in_hall_bookings_table', 1),
('2026_08_26_000001_create_settings_table', 1),
('2026_08_26_000002_add_check_in_out_dates_to_booking_rooms_table', 1),
('2026_08_30_000001_add_payment_due_at_to_bookings_and_hall_bookings_table', 1),
('2026_08_30_212900_add_cleaning_status_to_halls_table', 1),
('2026_08_30_213100_add_dirty_status_to_halls_table', 1),
('2026_08_31_000001_add_hall_booking_id_to_laundry_orders_table', 1),
('2026_08_31_000002_add_status_to_laundry_orders_table', 1),
('2026_08_31_000003_add_deposit_amount_to_hall_bookings_table', 1),
('2026_08_31_000004_add_customer_name_to_restaurant_orders_table', 1),
('2026_08_31_000005_update_payments_booking_check_constraint', 1)
ON CONFLICT DO NOTHING;

COMMIT;
