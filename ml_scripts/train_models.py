"""
Train ML Models for Hotel Predictions
- Room demand forecasting
- Hall peak detection
- Menu popularity prediction
"""
import sys
import json
import joblib
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder

from config import MODEL_DIR, MODEL_NAMES, DATA_DIR
from utils import query_to_dataframe, save_json, format_timestamp, calculate_accuracy

def extract_room_data():
    """Extract booking data for room demand prediction"""
    query = """
    SELECT 
        b.check_in_date,
        rt.name as room_type,
        rt.id as room_type_id,
        COUNT(br.id) as bookings_count,
        SUM(b.nights) as total_nights,
        AVG(b.total_amount) as avg_price
    FROM bookings b
    JOIN booking_rooms br ON b.id = br.booking_id
    JOIN rooms r ON br.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE b.status IN ('confirmed', 'checked_in', 'checked_out')
        AND b.check_in_date >= CURRENT_DATE - INTERVAL '12 months'
    GROUP BY b.check_in_date, rt.id, rt.name
    ORDER BY b.check_in_date
    """
    
    df = query_to_dataframe(query)
    
    if df is None or len(df) == 0:
        print("No booking data found")
        return None
    
    # Feature engineering
    df['check_in_date'] = pd.to_datetime(df['check_in_date'])
    df['day_of_week'] = df['check_in_date'].dt.dayofweek
    df['month'] = df['check_in_date'].dt.month
    df['is_weekend'] = df['day_of_week'].isin([4, 5, 6]).astype(int)
    df['quarter'] = df['check_in_date'].dt.quarter
    
    print(f"✓ Extracted {len(df)} booking records")
    return df

def extract_hall_data():
    """Extract hall booking data for peak detection"""
    query = """
    SELECT 
        event_date,
        COUNT(*) as bookings_count,
        SUM(duration_hours) as total_hours,
        AVG(total_amount) as avg_amount
    FROM hall_bookings
    WHERE status IN ('confirmed', 'completed')
        AND event_date >= CURRENT_DATE - INTERVAL '12 months'
    GROUP BY event_date
    ORDER BY event_date
    """
    
    df = query_to_dataframe(query)
    
    if df is None or len(df) == 0:
        print("No hall booking data found")
        return None
    
    # Feature engineering
    df['event_date'] = pd.to_datetime(df['event_date'])
    df['day_of_week'] = df['event_date'].dt.dayofweek
    df['month'] = df['event_date'].dt.month
    df['is_weekend'] = df['day_of_week'].isin([4, 5, 6]).astype(int)
    
    print(f"✓ Extracted {len(df)} hall booking records")
    return df

def extract_menu_data():
    """Extract restaurant order data for menu popularity"""
    query = """
    SELECT 
        ro.created_at::date as order_date,
        mi.name as menu_name,
        mi.id as menu_id,
        mi.category,
        COUNT(roi.id) as order_count,
        SUM(roi.quantity) as total_quantity,
        SUM(roi.quantity * roi.price) as total_revenue
    FROM restaurant_orders ro
    JOIN restaurant_order_items roi ON ro.id = roi.restaurant_order_id
    JOIN menu_items mi ON roi.menu_item_id = mi.id
    WHERE ro.created_at >= CURRENT_DATE - INTERVAL '6 months'
    GROUP BY ro.created_at::date, mi.id, mi.name, mi.category
    ORDER BY order_date
    """
    
    df = query_to_dataframe(query)
    
    if df is None or len(df) == 0:
        print("No restaurant order data found")
        return None
    
    # Feature engineering
    df['order_date'] = pd.to_datetime(df['order_date'])
    df['day_of_week'] = df['order_date'].dt.dayofweek
    df['month'] = df['order_date'].dt.month
    df['is_weekend'] = df['order_date'].isin([4, 5, 6]).astype(int)
    
    print(f"✓ Extracted {len(df)} menu order records")
    return df

def train_room_model(df):
    """Train room demand prediction model"""
    if df is None or len(df) < 50:
        print("Insufficient data for room model")
        return None, None
    
    # Prepare features
    le = LabelEncoder()
    df['room_type_encoded'] = le.fit_transform(df['room_type'])
    
    features = ['day_of_week', 'month', 'is_weekend', 'quarter', 'room_type_encoded']
    X = df[features]
    y = df['bookings_count']
    
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train model
    model = RandomForestRegressor(n_estimators=100, max_depth=10, random_state=42)
    model.fit(X_train, y_train)
    
    # Evaluate
    y_pred = model.predict(X_test)
    metrics = calculate_accuracy(y_test, y_pred)
    
    # Save model and encoder
    model_data = {
        'model': model,
        'label_encoder': le,
        'room_types': df['room_type'].unique().tolist()
    }
    
    print(f"✓ Room model trained - Accuracy: {metrics['accuracy']}%")
    return model_data, metrics

def train_hall_model(df):
    """Train hall peak detection model"""
    if df is None or len(df) < 30:
        print("Insufficient data for hall model")
        return None, None
    
    # Prepare features
    features = ['day_of_week', 'month', 'is_weekend']
    X = df[features]
    y = df['bookings_count']
    
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train model
    model = RandomForestRegressor(n_estimators=50, max_depth=8, random_state=42)
    model.fit(X_train, y_train)
    
    # Evaluate
    y_pred = model.predict(X_test)
    metrics = calculate_accuracy(y_test, y_pred)
    
    print(f"✓ Hall model trained - Accuracy: {metrics['accuracy']}%")
    return model, metrics

def train_menu_model(df):
    """Train menu popularity prediction model"""
    if df is None or len(df) < 30:
        print("Insufficient data for menu model")
        return None, None
    
    # Prepare features
    le_menu = LabelEncoder()
    le_category = LabelEncoder()
    
    df['menu_encoded'] = le_menu.fit_transform(df['menu_name'])
    df['category_encoded'] = le_category.fit_transform(df['category'])
    
    features = ['day_of_week', 'month', 'is_weekend', 'menu_encoded', 'category_encoded']
    X = df[features]
    y = df['total_quantity']
    
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train model
    model = RandomForestRegressor(n_estimators=80, max_depth=8, random_state=42)
    model.fit(X_train, y_train)
    
    # Evaluate
    y_pred = model.predict(X_test)
    metrics = calculate_accuracy(y_test, y_pred)
    
    # Save model and encoders
    model_data = {
        'model': model,
        'menu_encoder': le_menu,
        'category_encoder': le_category,
        'menu_names': df['menu_name'].unique().tolist(),
        'categories': df['category'].unique().tolist()
    }
    
    print(f"✓ Menu model trained - Accuracy: {metrics['accuracy']}%")
    return model_data, metrics

def main():
    """Main training pipeline"""
    print("=" * 60)
    print("HOTEL ML TRAINING PIPELINE")
    print("=" * 60)
    print(f"Started at: {format_timestamp()}\n")
    
    results = {
        'status': 'success',
        'models': [],
        'total_samples': 0,
        'average_accuracy': 0,
        'trained_at': format_timestamp()
    }
    
    try:
        # 1. Extract data
        print("\n[1/4] Extracting data...")
        room_df = extract_room_data()
        hall_df = extract_hall_data()
        menu_df = extract_menu_data()
        
        # 2. Train room model
        print("\n[2/4] Training room demand model...")
        if room_df is not None:
            room_model, room_metrics = train_room_model(room_df)
            if room_model:
                joblib.dump(room_model, f"{MODEL_DIR}/{MODEL_NAMES['room']}")
                results['models'].append({
                    'name': 'room_demand',
                    'accuracy': room_metrics['accuracy'],
                    'samples': len(room_df)
                })
                results['total_samples'] += len(room_df)
        
        # 3. Train hall model
        print("\n[3/4] Training hall peak model...")
        if hall_df is not None:
            hall_model, hall_metrics = train_hall_model(hall_df)
            if hall_model:
                joblib.dump(hall_model, f"{MODEL_DIR}/{MODEL_NAMES['hall']}")
                results['models'].append({
                    'name': 'hall_peak',
                    'accuracy': hall_metrics['accuracy'],
                    'samples': len(hall_df)
                })
                results['total_samples'] += len(hall_df)
        
        # 4. Train menu model
        print("\n[4/4] Training menu popularity model...")
        if menu_df is not None:
            menu_model, menu_metrics = train_menu_model(menu_df)
            if menu_model:
                joblib.dump(menu_model, f"{MODEL_DIR}/{MODEL_NAMES['menu']}")
                results['models'].append({
                    'name': 'menu_popularity',
                    'accuracy': menu_metrics['accuracy'],
                    'samples': len(menu_df)
                })
                results['total_samples'] += len(menu_df)
        
        # Calculate average accuracy
        if results['models']:
            results['average_accuracy'] = round(
                sum(m['accuracy'] for m in results['models']) / len(results['models']), 2
            )
        
        print("\n" + "=" * 60)
        print("✓ TRAINING COMPLETED")
        print(f"Models trained: {len(results['models'])}")
        print(f"Total samples: {results['total_samples']}")
        print(f"Average accuracy: {results['average_accuracy']}%")
        print("=" * 60)
        
    except Exception as e:
        results['status'] = 'error'
        results['error'] = str(e)
        print(f"\n✗ Training failed: {e}")
    
    # Output JSON for Laravel
    print(json.dumps(results))
    return 0 if results['status'] == 'success' else 1

if __name__ == '__main__':
    sys.exit(main())
