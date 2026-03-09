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
    'user': os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'port': int(os.getenv('DB_PORT', 3306))
}

# Paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
BACKEND_DIR = os.path.join(BASE_DIR, '..', 'backend')
MODEL_DIR = os.path.join(BACKEND_DIR, 'storage', 'ml', 'models')
DATA_DIR = os.path.join(BACKEND_DIR, 'storage', 'ml', 'data')
PREDICTIONS_DIR = os.path.join(BACKEND_DIR, 'storage', 'ml', 'predictions')

# Ensure directories exist
os.makedirs(MODEL_DIR, exist_ok=True)
os.makedirs(DATA_DIR, exist_ok=True)
os.makedirs(PREDICTIONS_DIR, exist_ok=True)

# Model Configuration
MODEL_NAMES = {
    'room': 'room_model.pkl',
    'hall': 'hall_model.pkl',
    'menu': 'menu_model.pkl'
}

# Prediction Settings
PREDICTION_DAYS = 30  # Predict next 30 days
CONFIDENCE_THRESHOLD = 0.7  # Minimum confidence for predictions
