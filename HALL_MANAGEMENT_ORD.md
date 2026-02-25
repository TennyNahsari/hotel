# Hall Management Feature - Simplified ORD
## Hotel Management System

### Document Information
- **Feature**: Hall/Meeting Room Management (Simplified)
- **Version**: 2.0
- **Date**: February 25, 2026
- **Status**: Design Phase

---

## Overview

Fitur manajemen hall/ruangan pertemuan yang **terintegrasi dengan sistem existing**:
- ✅ CRUD Hall/ruang pertemuan (simple)
- ✅ Booking hall mirip dengan booking rooms
- ✅ Payment menggunakan sistem payment yang sudah ada
- ✅ Housekeeping/cleaning bisa untuk hall dan rooms

**Prinsip Design**:
- Leverage existing tables (`payments`, `housekeeping_tasks`)
- Mirip dengan flow booking rooms yang sudah jalan
- Minimal new tables, maksimal reuse

---

## Database Tables

### 1. NEW: `halls` - Data Hall/Ruangan

**Purpose**: Data master hall/ruang pertemuan untuk meeting/event

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(100) | NOT NULL, UNIQUE | Nama hall (e.g., "Ballroom A", "Meeting Room 1") |
| hall_type | VARCHAR(50) | NOT NULL | Tipe hall (Meeting Room, Ballroom, Conference Hall) |
| floor | VARCHAR(20) | NULLABLE | Lokasi lantai (e.g., "2nd Floor", "Ground Floor") |
| capacity | INT | NOT NULL | Kapasitas maksimal (orang) |
| area_sqm | DECIMAL(10,2) | NULLABLE | Luas area (meter persegi) |
| price_per_hour | DECIMAL(12,2) | NOT NULL | Harga per jam |
| facilities | TEXT | NULLABLE | Daftar fasilitas (JSON atau comma-separated) |
| description | TEXT | NULLABLE | Deskripsi hall |
| image_url | VARCHAR(255) | NULLABLE | URL gambar hall |
| status | VARCHAR(20) | NOT NULL | Status (available, maintenance, unavailable) |
| created_at | TIMESTAMP | NOT NULL | Tanggal dibuat |
| updated_at | TIMESTAMP | NOT NULL | Tanggal diupdate |

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE KEY (name)
- INDEX (status)
- INDEX (hall_type)

**Sample hall_type values**:
```
- Meeting Room Small (< 30 pax)
- Meeting Room Medium (30-50 pax)
- Conference Hall (50-100 pax)  
- Ballroom (100-300 pax)
- Function Room (varies)
```

**Sample facilities** (stored as JSON):
```json
{
  "av_equipment": ["Projector", "Screen", "Sound System", "Microphones (2)"],
  "furniture": ["Tables", "Chairs", "Podium", "Whiteboard"],
  "tech": ["WiFi", "Video Conference", "Air Conditioning"],
  "other": ["Pantry Access", "Parking"]
}
```

**Business Rules**:
- `capacity` harus lebih besar dari 0
- `price_per_hour` harus lebih besar dari 0
- `status` default: "available"

---

### 2. NEW: `hall_bookings` - Booking Hall

**Purpose**: Booking/reservasi hall (mirip dengan `bookings` table untuk rooms)

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| booking_number | VARCHAR(50) | NOT NULL, UNIQUE | Nomor booking (HB-YYYYMMDD-XXXX) |
| hall_id | BIGINT | FK, NOT NULL | Relasi ke halls |
| guest_id | BIGINT | FK, NULLABLE | Relasi ke guests (bisa existing guest atau baru) |
| customer_name | VARCHAR(100) | NOT NULL | Nama customer/PIC |
| customer_email | VARCHAR(100) | NOT NULL | Email customer |
| customer_phone | VARCHAR(20) | NOT NULL | Telepon customer |
| customer_company | VARCHAR(100) | NULLABLE | Nama perusahaan/organisasi |
| event_name | VARCHAR(200) | NOT NULL | Nama acara/event |
| event_date | DATE | NOT NULL | Tanggal acara |
| start_time | TIME | NOT NULL | Jam mulai |
| end_time | TIME | NOT NULL | Jam selesai |
| duration_hours | DECIMAL(5,2) | NOT NULL | Durasi (jam) - calculated |
| attendees | INT | NOT NULL | Jumlah peserta |
| total_amount | DECIMAL(12,2) | NOT NULL | Total harga (duration * price_per_hour) |
| status | VARCHAR(20) | NOT NULL | Status (pending, confirmed, cancelled, completed) |
| special_requests | TEXT | NULLABLE | Permintaan khusus |
| notes | TEXT | NULLABLE | Catatan internal |
| booked_by | BIGINT | FK, NOT NULL | User yang input booking |
| created_at | TIMESTAMP | NOT NULL | Tanggal dibuat |
| updated_at | TIMESTAMP | NOT NULL | Tanggal diupdate |

**Indexes**:
- PRIMARY KEY (id)
- FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE RESTRICT
- FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL  
- FOREIGN KEY (booked_by) REFERENCES users(id) ON DELETE RESTRICT
- UNIQUE KEY (booking_number)
- INDEX (event_date, start_time, end_time)
- INDEX (hall_id, event_date, status)
- INDEX (guest_id)
- INDEX (status)

**Business Rules**:
- `end_time` > `start_time`
- `duration_hours` = difference between end_time and start_time
- `total_amount` = `duration_hours` * hall's `price_per_hour`
- `attendees` ≤ hall's `capacity`
- No overlapping bookings (same hall, same date, time conflict)
- Status flow: pending → confirmed → completed (atau cancelled)

**Booking Number Format**: `HB-20260225-0001`

---

### 3. MODIFY: `payments` - Tambah Support untuk Hall Bookings

**Purpose**: Extend payments table untuk support hall bookings juga

**Kolom yang DITAMBAHKAN**:

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| hall_booking_id | BIGINT | FK, NULLABLE | Relasi ke hall_bookings (NEW) |

**Indexes yang DITAMBAHKAN**:
- FOREIGN KEY (hall_booking_id) REFERENCES hall_bookings(id) ON DELETE RESTRICT
- INDEX (hall_booking_id)

**Business Rules**:
- `booking_id` XOR `hall_booking_id` - salah satu harus NULL, tidak bisa keduanya terisi
- Jika `hall_booking_id` terisi, maka payment untuk hall booking
- Jika `booking_id` terisi, maka payment untuk room booking
- Payment type, method, dan flow tetap sama dengan yang sudah ada

**Struktur payments table yang sudah ada (untuk referensi)**:
```
- id
- payment_number
- booking_id (existing - untuk room booking)
- hall_booking_id (NEW - untuk hall booking)  
- payment_type
- payment_method
- amount
- bank_name
- account_number
- reference_number
- payment_date
- notes
- processed_by
- created_at
- updated_at
```

---

### 4. MODIFY: `housekeeping_tasks` - Tambah Support untuk Hall Cleaning

**Purpose**: Extend housekeeping untuk cleaning hall juga

**Kolom yang DITAMBAHKAN**:

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| hall_id | BIGINT | FK, NULLABLE | Relasi ke halls (NEW) |
| task_type | VARCHAR(20) | NOT NULL | Type: room_cleaning, hall_cleaning (NEW) |

**Indexes yang DITAMBAHKAN**:
- FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE RESTRICT  
- INDEX (hall_id)
- INDEX (task_type)

**Business Rules**:
- `room_id` XOR `hall_id` - salah satu harus terisi
- Jika `task_type` = "room_cleaning", maka `room_id` terisi, `hall_id` NULL
- Jika `task_type` = "hall_cleaning", maka `hall_id` terisi, `room_id` NULL
- Task priority, status, dan flow tetap sama

**Struktur housekeeping_tasks yang sudah ada (untuk referensi)**:
```
- id
- room_id (existing)
- hall_id (NEW)
- task_type (MODIFIED - tambah enum: hall_cleaning)
- booking_id (untuk room)
- task_description
- priority
- status
- assigned_to
- scheduled_date
- completed_at
- notes
- created_at
- updated_at
```

**Alternative**: Bisa juga buat table terpisah `hall_housekeeping_tasks` jika mau lebih clean separation.

---

## Simplified Entity Relationship Diagram (ERD)

```
┌─────────────────┐
│     halls       │
│─────────────────│
│ PK id           │
│    name         │
│    hall_type    │
│    capacity     │
│    price_per_hr │
│    facilities   │◄───┐
│    status       │    │
└────────┬────────┘    │
         │             │
         │ 1:N         │ 1:N
         │             │
┌────────▼──────────────────┐
│    hall_bookings          │
│───────────────────────────│
│ PK id                     │
│ FK hall_id                │
│ FK guest_id (nullable)    │
│ FK booked_by              │
│    booking_number         │
│    customer_*             │
│    event_name/date        │
│    start/end_time         │
│    total_amount           │
│    status                 │
└──┬─────────────┬──────────┘
   │             │
   │ 1:N         │ 1:N
   │             │
┌──▼────────────┐│   ┌──────▼─────────────────┐
│   payments    ││   │  housekeeping_tasks    │
│───────────────││   │────────────────────────│
│ PK id         ││   │ PK id                  │
│ FK booking_id ││   │ FK room_id (existing)  │
│ FK hall_booking_id (NEW)  │ FK hall_id (NEW)       │
│    amount     ││   │    task_type (MODIFIED)│
│ ...existing.. ││   │    status              │
└───────────────┘│   │ ...existing...         │
                 │   └────────────────────────┘
                 │
          ┌──────▼────────┐
          │    guests     │
          │───────────────│
          │ PK id         │
          │    name       │
          │    email      │
          │    phone      │
          └───────────────┘
```

---

## Business Logic & Validation Rules

### 1. **Hall Availability Check**

Before creating/updating hall booking:
```sql
SELECT COUNT(*) FROM hall_bookings
WHERE hall_id = :hall_id
  AND event_date = :event_date
  AND status NOT IN ('cancelled')
  AND (
    (start_time < :end_time AND end_time > :start_time)
  )
```
If COUNT > 0, hall tidak tersedia (conflict).

### 2. **Capacity Validation**

```php
if ($booking->attendees > $hall->capacity) {
    throw new Exception("Attendees exceeds hall capacity");
}
```

### 3. **Price Calculation (Simple)**

```php
// Calculate duration
$start = Carbon::parse($start_time);
$end = Carbon::parse($end_time);
$duration_hours = $end->diffInMinutes($start) / 60;

// Calculate total
$total_amount = $duration_hours * $hall->price_per_hour;
```

### 4. **Booking Number Generation**

Format: `HB-YYYYMMDD-XXXX`
```php
$date = date('Ymd');
$count = HallBooking::whereDate('created_at', today())->count() + 1;
$booking_number = "HB-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
// Example: HB-20260225-0001
```

### 5. **Payment Tracking**

Menggunakan table `payments` yang sudah ada:
```php
// Create payment untuk hall booking
Payment::create([
    'payment_number' => generatePaymentNumber(),
    'hall_booking_id' => $hallBooking->id,  // NEW column
    'booking_id' => null,
    'payment_type' => $request->payment_type,
    'payment_method' => $request->payment_method,
    'amount' => $request->amount,
    'payment_date' => $request->payment_date,
    'processed_by' => auth()->id(),
]);

// Check if fully paid
$total_paid = Payment::where('hall_booking_id', $hallBooking->id)
    ->sum('amount');
    
if ($total_paid >= $hallBooking->total_amount) {
    $hallBooking->update(['status' => 'paid']);
}
```

### 6. **Housekeeping for Halls**

Menggunakan table `housekeeping_tasks` yang sudah ada:
```php
// Create cleaning task untuk hall
HousekeepingTask::create([
    'hall_id' => $hall->id,  // NEW column
    'room_id' => null,
    'task_type' => 'hall_cleaning',  // NEW enum value
    'task_description' => "Clean and setup {$hall->name} for {$booking->event_name}",
    'priority' => 'high',
    'status' => 'pending',
    'scheduled_date' => $booking->event_date,
    'assigned_to' => $housekeepingStaff->id,
]);
```

### 7. **Status Workflow**

```
pending → confirmed → completed
   ↓
cancelled
```

Rules:
- Status flow mirip dengan room booking
- Cancel bisa dilakukan sebelum event_date
- Completed otomatis setelah event_date lewat (atau manual)

---

## API Endpoints (Future Implementation)

### Halls Management
- `GET /api/halls` - List halls (filters: hall_type, status)
- `GET /api/halls/{id}` - Show hall detail
- `POST /api/halls` - Create hall
- `PUT /api/halls/{id}` - Update hall
- `DELETE /api/halls/{id}` - Delete hall (soft delete recommended)
- `GET /api/halls/{id}/availability?date={date}` - Check availability

### Hall Bookings
- `GET /api/hall-bookings` - List bookings (filters: date, status, hall_id)
- `GET /api/hall-bookings/{id}` - Show booking detail
- `POST /api/hall-bookings` - Create booking (dengan availability check)
- `PUT /api/hall-bookings/{id}` - Update booking
- `PATCH /api/hall-bookings/{id}/confirm` - Confirm booking
- `PATCH /api/hall-bookings/{id}/cancel` - Cancel booking
- `PATCH /api/hall-bookings/{id}/complete` - Mark as completed
- `GET /api/hall-bookings/export` - Export to Excel

### Payments (Extended)
- `GET /api/payments` - List payments (include hall bookings)
  - Filter: `type=hall` untuk hall payments only
  - Filter: `type=room` untuk room payments only
- `POST /api/payments` - Create payment (support hall_booking_id)
- Existing payment endpoints tetap sama

### Housekeeping (Extended)
- `GET /api/housekeeping-tasks` - List tasks (include hall cleaning)
  - Filter: `task_type=hall_cleaning`
  - Filter: `task_type=room_cleaning`
- `POST /api/housekeeping-tasks` - Create task (support hall_id)
- Existing housekeeping endpoints tetap sama

---

## Frontend Views (Future Implementation)

### Admin/Staff Views

**1. Halls Management** (`/admin/halls`)
- List view dengan table: Name, Type, Floor, Capacity, Price/Hour, Status
- Create/Edit form:
  - Basic info: Name, Hall Type, Floor
  - Capacity & Size: Capacity, Area (sqm)
  - Pricing: Price per Hour
  - Facilities: JSON editor atau checklist
  - Description & Image
  - Status: Available, Maintenance, Unavailable
- Delete dengan confirmation
- View details (readonly)

**2. Hall Bookings** (`/hall-bookings`)
- **Calendar View**: Full calendar menampilkan bookings per hall
  - Color-coded by status (pending, confirmed, completed)
  - Click untuk detail atau create new
  
- **List View**: Table dengan filters
  - Columns: Booking Number, Hall, Customer, Event Name, Event Date, Time, Status
  - Filters: Date range, Hall, Status
  - Actions: View, Edit, Confirm, Cancel, Complete
  
- **Create/Edit Booking Form**:
  - Step 1: Select Hall & Date/Time
    - Show availability calendar
    - Input: event_date, start_time, end_time
    - Auto-calculate duration & total amount
  - Step 2: Customer Information
    - Search existing guest atau input manual
    - Fields: customer_name, email, phone, company
  - Step 3: Event Details
    - event_name, attendees (validate vs capacity)
    - special_requests, notes
  - Step 4: Review & Confirm
    - Summary dengan pricing breakdown
    - Create booking
  
- **Booking Details View**:
  - Hall info, customer info, event details
  - Payment history (from payments table)
  - Status timeline
  - Actions: Print, Edit, Cancel, Mark Complete

**3. Payments** (`/payments`) - EXISTING, EXTENDED
- Tambah filter "Booking Type": All, Room Bookings, Hall Bookings
- Table columns tambah "Type" (Room/Hall)
- Create payment form tambah option untuk hall booking
- Export Excel include hall bookings

**4. Housekeeping** (`/housekeeping-tasks`) - EXISTING, EXTENDED
- Tambah filter "Task Type": All, Room Cleaning, Hall Cleaning
- Table columns show Room/Hall name (conditional)
- Create task form tambah option untuk hall
- Calendar view show both room & hall tasks

**5. Dashboard** (`/dashboard`) - EXTENDED
- Tambah widget: Hall Occupancy Today
- Tambah widget: Upcoming Hall Events
- Revenue chart include hall bookings

---

## Integration Points

### 1. **Guests Table** ✅
- Hall bookings bisa link ke existing guests
- Auto-fill customer info jika select guest
- `hall_bookings.guest_id → guests.id` (nullable)

### 2. **Users & Roles** ✅
- Use existing role permissions:
  - Admin: Full access
  - Front Desk: Create/edit bookings, payments
  - Manager: Approve, reports
  - Housekeeping: View schedule, update tasks

### 3. **Payments System** ✅
- Reuse `payments` table dengan tambahan `hall_booking_id`
- Same payment methods, workflow
- Unified reporting (room + hall revenue)

### 4. **Housekeeping System** ✅
- Reuse `housekeeping_tasks` dengan tambahan `hall_id` dan `task_type`
- Same workflow: assign, update status, complete
- Unified scheduling

### 5. **Audit Logs** ✅
- Use existing audit log if any
- Track: booking created, confirmed, cancelled, completed
- Track: payments, refunds
- Track: status changes

---

## Database Migrations Summary

### New Tables (2):
1. `halls` - Master data hall/ruangan
2. `hall_bookings` - Booking hall

### Modified Tables (2):
1. `payments` - Add `hall_booking_id` column
2. `housekeeping_tasks` - Add `hall_id` column and modify `task_type` enum

### Migration Order:
1. Create `halls` table
2. Create `hall_bookings` table
3. Alter `payments` table (add hall_booking_id, foreign key, index)
4. Alter `housekeeping_tasks` table (add hall_id, update task_type enum, foreign key, index)

---

## Sample Data for Testing

### Halls:
```sql
INSERT INTO halls (name, hall_type, floor, capacity, area_sqm, price_per_hour, facilities, status) VALUES
('Ballroom A', 'Ballroom', 'Ground Floor', 300, 250.00, 2000000,  '{"av_equipment":["Projector","Sound System","4 Microphones"],"furniture":["Stage","50 Tables","300 Chairs"],"tech":["WiFi","AC","LED Wall"]}', 'available'),
('Meeting Room 1', 'Meeting Room Small', '2nd Floor', 20, 40.00, 300000, '{"av_equipment":["Projector","Screen","Whiteboard"],"tech":["WiFi","AC","Video Conference"]}', 'available'),
('Conference Hall', 'Conference Hall', '3rd Floor', 100, 120.00, 800000, '{"av_equipment":["Projector","Sound System","4 Mics"],"furniture":["Tables","Chairs","Podium"],"tech":["WiFi","AC"]}', 'available');
```

### Hall Bookings:
```sql
INSERT INTO hall_bookings (booking_number, hall_id, guest_id, customer_name, customer_email, customer_phone, customer_company, event_name, event_date, start_time, end_time, duration_hours, attendees, total_amount, status, booked_by) VALUES
('HB-20260225-0001', 1, NULL, 'John Doe', 'john@company.com', '081234567890', 'ABC Corp', 'Annual Meeting 2026', '2026-03-01', '09:00:00', '17:00:00', 8.00, 250, 16000000, 'confirmed', 1),
('HB-20260225-0002', 2, 1, 'Jane Smith', 'jane@email.com', '081298765432', NULL, 'Product Launch', '2026-03-05', '14:00:00', '17:00:00', 3.00, 15, 900000, 'pending', 1);
```

---

## Testing Scenarios

### 1. **Create Hall**
- Input: Name, Type, Capacity, Price
- Expected: Hall created with status "available"

### 2. **Book Hall - Success**
- Input: Hall 1, Date: 2026-03-10, Time: 10:00-12:00, Attendees: 50
- Check: No conflict, attendees ≤ capacity
- Expected: Booking created with status "pending", total calculated

### 3. **Book Hall - Conflict**
- Existing: Hall 1, 2026-03-10, 10:00-12:00
- Try create: Hall 1, 2026-03-10, 11:00-13:00 (overlap)
- Expected: Error - Hall not available

### 4. **Book Hall - Exceeds Capacity**
- Hall capacity: 20
- Try create booking with 30 attendees
- Expected: Error - Exceeds capacity

### 5. **Payment for Hall Booking**
- Create payment with `hall_booking_id` = 1
- Expected: Payment created, linked to hall booking

### 6. **Housekeeping for Hall**
- Create task with `hall_id` = 1, `task_type` = "hall_cleaning"
- Expected: Task created for hall, not room

---

## Questions & Clarifications

1. **Deposit**: Apakah hall booking perlu deposit seperti room booking?
2. **Cancellation**: Berapa lama sebelum event bisa cancel? Ada penalty?
3. **Overtime**: Jika event lewat waktu, charge per jam atau flat?
4. **Minimum Duration**: Ada minimum booking hours? (e.g., min 2 jam)
5. **Working Hours**: Jam operasional hall? (e.g., 08:00-22:00)
6. **Setup Time**: Perlu buffer time sebelum/sesudah event?

---

## Implementation Priority

### Phase 1: Core CRUD (Week 1)
- [ ] Database migrations (halls, hall_bookings)
- [ ] Models & relationships
- [ ] Basic API endpoints (CRUD halls, CRUD bookings)
- [ ] Frontend: Halls management page
- [ ] Frontend: Hall bookings list & create

### Phase 2: Integration (Week 2)
- [ ] Modify payments table & code
- [ ] Modify housekeeping table & code
- [ ] Payment for hall bookings
- [ ] Housekeeping for halls
- [ ] Availability calendar view

### Phase 3: Enhancement (Week 3)
- [ ] Excel export for hall bookings
- [ ] Reports & analytics
- [ ] Email notifications
- [ ] Testing & bug fixes

---

## Conclusion

Design yang **simplified** ini:
- ✅ **Minimal tables**: Hanya 2 new tables (halls, hall_bookings)
- ✅ **Reuse existing**: Payments, housekeeping, guests, users
- ✅ **Simple flow**: Mirip dengan room booking yang sudah jalan
- ✅ **Easy to maintain**: Less complexity, less code
- ✅ **Scalable**: Bisa ditambah features nanti jika perlu

Next: Review & approval, then implement migrations & models.

---

**Document Version**: 2.0 (Simplified)  
**Last Updated**: February 25, 2026  
**Prepared by**: GitHub Copilot  
**Status**: Ready for Implementation

---

## Summary of Changes from v1.0 to v2.0

### Removed (Simplified):
- ❌ `hall_types` table - Jadi field di halls
- ❌ `hall_facilities` table - Jadi JSON di halls
- ❌ `hall_facility_pivot` table - Tidak perlu
- ❌ `hall_booking_facilities` table - Simplified
- ❌ `hall_payments` table - **Pakai `payments` table yang sudah ada**
- ❌ `hall_price_rules` table - Too complex untuk v1
- ❌ Multiple capacity fields - Jadi 1 capacity field
- ❌ Complex pricing (halfday/fullday) - Hourly only

### Added (Essential):
- ✅ `halls` table - Simple CRUD dengan facilities JSON
- ✅ `hall_bookings` table - Mirip bookings untuk rooms
- ✅ `payments.hall_booking_id` - Extend existing table
- ✅ `housekeeping_tasks.hall_id` - Extend existing table
- ✅ `housekeeping_tasks.task_type` enum - Add "hall_cleaning"

### Benefits:
- 🚀 **Faster implementation**: 2 new tables vs 8 tables
- 🔧 **Easier maintenance**: Reuse existing code
- 📊 **Unified reporting**: Payment & housekeeping in 1 system  
- 🎯 **Simpler UX**: Same flow as room bookings
- 💰 **Lower cost**: Less development time

### Migration Path:
1. Create 2 new tables: `halls`, `hall_bookings`
2. Alter 2 existing tables: `payments`, `housekeeping_tasks`
3. Update models & controllers untuk support hall bookings
4. Create frontend views (leverage existing components)
5. Test & deploy

**Ready to implement!** 🎉
