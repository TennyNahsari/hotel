# Hotel AI Prediction System

## 🎯 Overview

AI-powered prediction system for hotel management using Python machine learning + Laravel backend.

**Features:**
- 📊 **Room Demand Forecast**: 30-day booking predictions by room type
- 🏛️ **Hall Peak Detection**: Identify high-demand dates for hall bookings  
- 🍽️ **Menu Popularity**: Rank restaurant menu items by predicted orders

## 🏗️ Architecture

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│  Frontend   │─────▶│   Laravel    │─────▶│   Python    │
│  Vue.js 3   │      │   Backend    │      │   ML Scripts│
│             │      │  (API + DB)  │      │  (Training) │
└─────────────┘      └──────────────┘      └─────────────┘
                            │
                            ▼
                     ┌──────────────┐
                     │  PostgreSQL  │
                     │  (Predictions│
                     │  + Models)   │
                     └──────────────┘
```

**Stack:**
- **Frontend**: Vue.js 3 Composition API
- **Backend**: Laravel 10 with Process facade
- **ML Engine**: Python 3.10+ (scikit-learn, pandas)
- **Database**: PostgreSQL with JSON storage
- **Models**: Hybrid versioning (active + 3 backups)

## 📁 Directory Structure

```
hotel/
├── backend/
│   ├── app/Http/Controllers/Api/
│   │   └── MLController.php          # API endpoints
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── *_create_ai_predictions_table.php
│   │   │   └── *_create_ml_model_versions_table.php
│   │   └── seeders/
│   │       └── MLDummyDataSeeder.php # Generate 6 months data
│   ├── storage/ml/
│   │   ├── models/                   # .pkl model files
│   │   ├── data/                     # Processed datasets
│   │   └── predictions/              # JSON predictions
│   └── routes/api.php                # ML routes
├── ml_scripts/
│   ├── config.py                     # DB config & paths
│   ├── utils.py                      # Helper functions
│   ├── train_models.py               # Main training script
│   ├── predict.py                    # Prediction generator
│   └── requirements.txt              # Python dependencies
└── frontend/
    ├── src/
    │   ├── api/ml.js                 # ML API service
    │   ├── components/
    │   │   └── AIPredictionsCard.vue # Dashboard widget
    │   └── views/
    │       └── DashboardView.vue     # Main dashboard
    └── ...
```

## 🚀 Installation & Setup

### 1. Backend Setup (Laravel)

```bash
cd backend

# Run migrations
php artisan migrate

# Generate dummy data (6 months, 100+ records)
php artisan db:seed --class=MLDummyDataSeeder

# Create ML directories
mkdir -p storage/ml/models storage/ml/data storage/ml/predictions
```

### 2. Python Setup

```bash
# Install Python 3.10+ (Ubuntu 24.04 includes Python 3.12)
python3 --version

# Install dependencies
cd ../ml_scripts
pip3 install -r requirements.txt

# Test Python connection
python3 -c "import pandas; import sklearn; print('✓ Python setup OK')"
```

### 3. Environment Configuration

Add to `backend/.env`:

```env
# Python path (default: python3)
PYTHON_PATH=python3

# Database credentials (for Python scripts)
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hotel_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 4. Frontend Setup

```bash
cd ../frontend

# Install dependencies (if not already installed)
npm install

# Run development server
npm run dev
```

## 📊 Usage

### Admin Workflow

1. **Login as Admin**
   - Navigate to Dashboard
   - Scroll to "AI Predictions" section

2. **Train Models (First Time)**
   - Click "Fetch & Train" button
   - Wait 5-10 minutes for training
   - ✅ Models saved to `storage/ml/models/`

3. **Generate Predictions**
   - Click "Generate Predictions" button
   - Wait ~30 seconds
   - ✅ Predictions displayed on dashboard

4. **Retrain Models** (when new data available)
   - Click "Fetch & Train" again
   - Old models automatically archived

### User Access

- ✅ **Admin**: Train models, generate predictions, view results
- ✅ **All Users**: View predictions (read-only)

### API Endpoints

```http
# Admin only (rate limited)
POST /api/ml/train              # Train models (2/hour)
POST /api/ml/predict            # Generate predictions (10/hour)

# All authenticated users
GET  /api/ml/predictions        # Get latest predictions
GET  /api/ml/info               # Get model metadata
```

## 🔍 Predictions Details

### 1. Room Demand Forecast

**Output**: 30-day forecast with room type rankings

```json
{
  "room_demand": {
    "data": [
      {
        "date": "2024-03-15",
        "day_of_week": 4,
        "is_weekend": true,
        "predictions": [
          {
            "room_type": "Deluxe Suite",
            "demand": 8.5,
            "confidence": 0.85
          },
          ...
        ]
      }
    ]
  }
}
```

**Features Used:**
- Day of week (0-6)
- Month (1-12)
- Is weekend (0/1)
- Quarter (1-4)
- Room type (encoded)

**Algorithm**: Random Forest Regressor (100 estimators, max_depth=10)

### 2. Hall Peak Detection

**Output**: Peak dates with expected bookings

```json
{
  "hall_peaks": {
    "peak_dates": [
      {
        "date": "2024-03-16",
        "day_of_week": 5,
        "is_weekend": true,
        "expected_bookings": 3.2,
        "is_peak": true,
        "confidence": 0.90
      }
    ]
  }
}
```

**Peak Criteria:**
- Expected bookings > 2 **OR**
- Weekend + bookings > 1

**Algorithm**: Random Forest Regressor (50 estimators, max_depth=8)

### 3. Menu Popularity

**Output**: Top 10 menu items by predicted orders

```json
{
  "menu_popularity": {
    "top_10": [
      {
        "menu_name": "Nasi Goreng Spesial",
        "popularity_score": 45.8,
        "confidence": 0.80
      }
    ]
  }
}
```

**Features Used:**
- Day of week
- Month
- Is weekend
- Menu item (encoded)
- Category (encoded)

**Algorithm**: Random Forest Regressor (80 estimators, max_depth=8)

## 🧪 Training Process

### Data Requirements

- **Minimum**: 50 booking records, 30 hall bookings, 30 restaurant orders
- **Recommended**: 6 months of historical data
- **Optimal**: 12+ months for seasonal patterns

### Training Steps

1. **Data Extraction**
   ```sql
   SELECT check_in_date, room_type, COUNT(*) as bookings_count
   FROM bookings b
   JOIN rooms r ON b.room_id = r.id
   WHERE status IN ('confirmed', 'checked_in', 'checked_out')
   GROUP BY check_in_date, room_type
   ```

2. **Feature Engineering**
   - Date features: day_of_week, month, quarter, is_weekend
   - Label encoding: room_type, menu_name, category
   - Aggregations: bookings_count, total_nights, avg_price

3. **Model Training**
   - Algorithm: Random Forest (ensemble learning)
   - Train/test split: 80/20
   - Validation: MAPE (Mean Absolute Percentage Error), R² score

4. **Model Versioning**
   - Deactivate previous versions
   - Save new model with metadata:
     - `model_name`, `version`, `accuracy`, `trained_samples`
     - `file_path`, `file_size`, `trained_at`

### Accuracy Metrics

- **MAPE** < 20%: Excellent
- **MAPE** 20-30%: Good
- **MAPE** > 30%: Needs more data

## 🔧 Troubleshooting

### Training Failed: "No booking data found"

**Solution**: Run dummy data seeder
```bash
php artisan db:seed --class=MLDummyDataSeeder
```

### Python Module Not Found

**Solution**: Install requirements
```bash
cd ml_scripts
pip3 install -r requirements.txt
```

### "Training script failed" Error

**Debug**: Test Python script directly
```bash
cd ml_scripts
python3 train_models.py
```

Check output for specific error (DB connection, missing tables, etc.)

### Predictions Not Showing

**Checklist**:
1. ✅ Models trained? Check `storage/ml/models/` for `.pkl` files
2. ✅ Predictions generated? Check `storage/ml/predictions/predictions.json`
3. ✅ Database entries? `SELECT * FROM ai_predictions;`
4. ✅ Not expired? Check `expires_at` column (24 hours TTL)

### Rate Limit Exceeded

**Error**: "Too Many Attempts"

**Solution**: Wait 1 hour or adjust throttle in `routes/api.php`:
```php
Route::post('/ml/train', ...)->middleware('throttle:2,60');  // 2 per hour
```

## 📈 Performance

### Training Time

- **Room model**: ~30-60 seconds (100 estimators)
- **Hall model**: ~20-40 seconds (50 estimators)
- **Menu model**: ~40-80 seconds (80 estimators)
- **Total**: ~2-3 minutes for 6 months data

### Prediction Time

- **Room forecast**: ~5-10 seconds (30 days)
- **Hall peaks**: ~3-5 seconds (30 days)
- **Menu ranking**: ~5-8 seconds (7 days avg)
- **Total**: ~15-25 seconds

### Storage

- **Model files**: ~500KB - 2MB per model
- **Predictions JSON**: ~50-200KB
- **Database**: ~10-50KB per prediction record

## 🔐 Security

### Access Control

- ✅ Admin-only training (role check)
- ✅ Rate limiting (2 trains/hour, 10 predictions/hour)
- ✅ Sanctum authentication required
- ✅ Audit logging for all ML operations

### Data Privacy

- ✅ No guest PII in predictions
- ✅ Aggregated data only
- ✅ 24-hour prediction expiry
- ✅ Model versioning for rollback

## 🚀 Deployment Notes

### Production Checklist

- [ ] Install Python 3.10+ on server
- [ ] Run `pip3 install -r ml_scripts/requirements.txt`
- [ ] Set correct `PYTHON_PATH` in `.env`
- [ ] Ensure `storage/ml/` is writable (chmod 775)
- [ ] Run migrations: `php artisan migrate`
- [ ] Seed dummy data OR wait for real data accumulation
- [ ] Test training: `php artisan tinker` → `Process::run('python3 ml_scripts/train_models.py')`
- [ ] Configure cron (optional): Daily prediction refresh

### Optional: Automated Training

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Retrain models weekly (Sunday 2 AM)
    $schedule->call(function () {
        Process::run('python3 ' . base_path('ml_scripts/train_models.py'));
    })->weekly()->sundays()->at('02:00');

    // Refresh predictions daily (6 AM)
    $schedule->call(function () {
        Process::run('python3 ' . base_path('ml_scripts/predict.py'));
    })->dailyAt('06:00');
}
```

## 📝 License

Part of Hotel Management System - Internal Use Only

## 🤝 Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Test Python scripts: `python3 ml_scripts/train_models.py`
3. Verify database: `SELECT * FROM ml_model_versions;`

---

**Version**: 1.0.0  
**Last Updated**: March 2024  
**Maintained by**: Development Team
