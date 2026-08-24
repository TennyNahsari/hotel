"""
Configuration for ML Scripts
Database connection and paths
"""
import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv('../backend/.env')

# Database Configuration
DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'database': os.getenv('DB_DATABASE', 'hotel'),
    'user': os.getenv('DB_USERNAME', 'postgres'),
    'password': os.getenv('DB_PASSWORD', ''),
    'port': int(os.getenv('DB_PORT', 5432))
}

# Paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
BACKEND_DIR = os.path.abspath(os.path.join(BASE_DIR, '..', 'backend'))
MODEL_DIR = os.path.abspath(os.path.join(BACKEND_DIR, 'storage', 'ml', 'models'))
DATA_DIR = os.path.abspath(os.path.join(BACKEND_DIR, 'storage', 'ml', 'data'))
PREDICTIONS_DIR = os.path.abspath(os.path.join(BACKEND_DIR, 'storage', 'ml', 'predictions'))

# Ensure directories exist with proper permissions
for directory in [MODEL_DIR, DATA_DIR, PREDICTIONS_DIR]:
    os.makedirs(directory, mode=0o775, exist_ok=True)

# Model Configuration
MODEL_NAMES = {
    'room_demand': 'room_demand_model.pkl',
    'hall_peak': 'hall_peak_model.pkl',
    'menu_popularity': 'menu_popularity_model.pkl'
}

# Prediction Settings
PREDICTION_DAYS = 7  # Predict next 7 days
CONFIDENCE_THRESHOLD = 0.7  # Minimum confidence for predictions
