"""
Generate Predictions from Trained Models
- Room demand forecast (next 30 days)
- Hall peak dates
- Menu popularity ranking
"""
import sys
import json
import joblib
import pandas as pd
import numpy as np
from datetime import datetime, timedelta

from config import MODEL_DIR, MODEL_NAMES, PREDICTIONS_DIR, PREDICTION_DAYS, CONFIDENCE_THRESHOLD
from utils import save_json, format_timestamp

def load_models():
    """Load all trained models"""
    models = {}
    
    try:
        room_model = joblib.load(f"{MODEL_DIR}/{MODEL_NAMES['room']}")
        models['room'] = room_model
        print("✓ Room model loaded")
    except FileNotFoundError:
        print("✗ Room model not found")
    
    try:
        hall_model = joblib.load(f"{MODEL_DIR}/{MODEL_NAMES['hall']}")
        models['hall'] = hall_model
        print("✓ Hall model loaded")
    except FileNotFoundError:
        print("✗ Hall model not found")
    
    try:
        menu_model = joblib.load(f"{MODEL_DIR}/{MODEL_NAMES['menu']}")
        models['menu'] = menu_model
        print("✓ Menu model loaded")
    except FileNotFoundError:
        print("✗ Menu model not found")
    
    return models

def predict_room_demand(model_data):
    """Predict room demand for next 30 days"""
    if model_data is None:
        return None
    
    model = model_data['model']
    le = model_data['label_encoder']
    room_types = model_data['room_types']
    
    predictions = []
    start_date = datetime.now().date()
    
    for days_ahead in range(PREDICTION_DAYS):
        pred_date = start_date + timedelta(days=days_ahead)
        day_of_week = pred_date.weekday()
        month = pred_date.month
        is_weekend = 1 if day_of_week in [4, 5, 6] else 0
        quarter = (month - 1) // 3 + 1
        
        # Predict for each room type
        room_predictions = []
        for room_type in room_types:
            room_type_encoded = le.transform([room_type])[0]
            
            features = np.array([[
                day_of_week,
                month,
                is_weekend,
                quarter,
                room_type_encoded
            ]])
            
            demand = model.predict(features)[0]
            
            room_predictions.append({
                'room_type': room_type,
                'demand': round(float(demand), 2),
                'confidence': 0.85 if is_weekend else 0.75
            })
        
        # Sort by demand
        room_predictions.sort(key=lambda x: x['demand'], reverse=True)
        
        predictions.append({
            'date': pred_date.isoformat(),
            'day_of_week': day_of_week,
            'is_weekend': bool(is_weekend),
            'predictions': room_predictions
        })
    
    print(f"✓ Generated room demand forecast for {PREDICTION_DAYS} days")
    return predictions

def predict_hall_peaks(model):
    """Predict hall booking peaks for next 30 days"""
    if model is None:
        return None
    
    predictions = []
    start_date = datetime.now().date()
    
    for days_ahead in range(PREDICTION_DAYS):
        pred_date = start_date + timedelta(days=days_ahead)
        day_of_week = pred_date.weekday()
        month = pred_date.month
        is_weekend = 1 if day_of_week in [4, 5, 6] else 0
        
        features = np.array([[
            day_of_week,
            month,
            is_weekend
        ]])
        
        bookings = model.predict(features)[0]
        is_peak = bookings > 2 or (is_weekend and bookings > 1)
        
        predictions.append({
            'date': pred_date.isoformat(),
            'day_of_week': day_of_week,
            'is_weekend': bool(is_weekend),
            'expected_bookings': round(float(bookings), 2),
            'is_peak': bool(is_peak),
            'confidence': 0.90 if is_weekend else 0.70
        })
    
    # Get top 10 peak dates
    peak_dates = [p for p in predictions if p['is_peak']]
    peak_dates.sort(key=lambda x: x['expected_bookings'], reverse=True)
    
    print(f"✓ Detected {len(peak_dates)} peak hall dates")
    return {
        'all_predictions': predictions,
        'peak_dates': peak_dates[:10]
    }

def predict_menu_popularity(model_data):
    """Predict menu popularity for next 7 days"""
    if model_data is None:
        return None
    
    model = model_data['model']
    menu_encoder = model_data['menu_encoder']
    category_encoder = model_data['category_encoder']
    menu_names = model_data['menu_names']
    categories = model_data['categories']
    
    # Group menus by category
    menu_by_category = {}
    for menu in menu_names:
        # Infer category from menu name patterns
        if any(x in menu.lower() for x in ['nasi', 'rice', 'pasta']):
            cat = 'Main Course'
        elif any(x in menu.lower() for x in ['juice', 'coffee', 'tea', 'drink']):
            cat = 'Beverage'
        elif any(x in menu.lower() for x in ['cake', 'ice cream', 'dessert']):
            cat = 'Dessert'
        else:
            cat = 'Appetizer'
        
        if cat not in menu_by_category:
            menu_by_category[cat] = []
        menu_by_category[cat].append(menu)
    
    # Predict for next 7 days average
    start_date = datetime.now().date()
    menu_scores = {menu: [] for menu in menu_names}
    
    for days_ahead in range(7):
        pred_date = start_date + timedelta(days=days_ahead)
        day_of_week = pred_date.weekday()
        month = pred_date.month
        is_weekend = 1 if day_of_week in [4, 5, 6] else 0
        
        for menu in menu_names:
            try:
                menu_encoded = menu_encoder.transform([menu])[0]
                
                # Infer category
                if any(x in menu.lower() for x in ['nasi', 'rice', 'pasta']):
                    cat = 'Main Course'
                elif any(x in menu.lower() for x in ['juice', 'coffee', 'tea', 'drink']):
                    cat = 'Beverage'
                elif any(x in menu.lower() for x in ['cake', 'ice cream', 'dessert']):
                    cat = 'Dessert'
                else:
                    cat = 'Appetizer'
                
                category_encoded = category_encoder.transform([cat])[0]
                
                features = np.array([[
                    day_of_week,
                    month,
                    is_weekend,
                    menu_encoded,
                    category_encoded
                ]])
                
                popularity = model.predict(features)[0]
                menu_scores[menu].append(popularity)
            except:
                pass
    
    # Calculate average popularity
    menu_rankings = []
    for menu, scores in menu_scores.items():
        if scores:
            avg_score = np.mean(scores)
            menu_rankings.append({
                'menu_name': menu,
                'popularity_score': round(float(avg_score), 2),
                'confidence': 0.80
            })
    
    # Sort by popularity
    menu_rankings.sort(key=lambda x: x['popularity_score'], reverse=True)
    
    print(f"✓ Ranked {len(menu_rankings)} menu items")
    return {
        'top_10': menu_rankings[:10],
        'all_rankings': menu_rankings
    }

def main():
    """Main prediction pipeline"""
    print("=" * 60)
    print("HOTEL ML PREDICTION PIPELINE")
    print("=" * 60)
    print(f"Started at: {format_timestamp()}\n")
    
    results = {
        'status': 'success',
        'generated_at': format_timestamp(),
        'expires_at': (datetime.now() + timedelta(days=1)).isoformat(),
        'predictions': {}
    }
    
    try:
        # Load models
        print("\n[1/4] Loading models...")
        models = load_models()
        
        if not models:
            raise Exception("No trained models found")
        
        # Generate room demand forecast
        print("\n[2/4] Generating room demand forecast...")
        if 'room' in models:
            room_pred = predict_room_demand(models['room'])
            if room_pred:
                results['predictions']['room_demand'] = room_pred
        
        # Generate hall peak predictions
        print("\n[3/4] Detecting hall peak dates...")
        if 'hall' in models:
            hall_pred = predict_hall_peaks(models['hall'])
            if hall_pred:
                results['predictions']['hall_peaks'] = hall_pred
        
        # Generate menu popularity
        print("\n[4/4] Ranking menu popularity...")
        if 'menu' in models:
            menu_pred = predict_menu_popularity(models['menu'])
            if menu_pred:
                results['predictions']['menu_popularity'] = menu_pred
        
        # Save predictions
        save_json(results, f"{PREDICTIONS_DIR}/predictions.json")
        
        print("\n" + "=" * 60)
        print("✓ PREDICTIONS GENERATED")
        print(f"Room forecasts: {len(results['predictions'].get('room_demand', []))}")
        print(f"Hall peaks: {len(results['predictions'].get('hall_peaks', {}).get('peak_dates', []))}")
        print(f"Menu rankings: {len(results['predictions'].get('menu_popularity', {}).get('top_10', []))}")
        print("=" * 60)
        
    except Exception as e:
        results['status'] = 'error'
        results['error'] = str(e)
        print(f"\n✗ Prediction failed: {e}")
    
    # Output JSON for Laravel
    print(json.dumps(results))
    return 0 if results['status'] == 'success' else 1

if __name__ == '__main__':
    sys.exit(main())
