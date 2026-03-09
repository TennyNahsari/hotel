"""
Utility functions for ML scripts
"""
import psycopg2
from psycopg2.extras import RealDictCursor
import pandas as pd
import json
from datetime import datetime
from sqlalchemy import create_engine
from config import DB_CONFIG

def get_db_connection():
    """Create database connection (psycopg2 for non-pandas operations)"""
    try:
        conn = psycopg2.connect(**DB_CONFIG)
        return conn
    except psycopg2.Error as error:
        print(f"Failed to connect to database: {error}")
        return None

def get_sqlalchemy_engine():
    """Create SQLAlchemy engine for pandas operations"""
    try:
        # Build connection string from DB_CONFIG
        user = DB_CONFIG['user']
        password = DB_CONFIG['password']
        host = DB_CONFIG['host']
        port = DB_CONFIG['port']
        database = DB_CONFIG['database']
        
        connection_string = f"postgresql://{user}:{password}@{host}:{port}/{database}"
        engine = create_engine(connection_string)
        return engine
    except Exception as error:
        print(f"Failed to create SQLAlchemy engine: {error}")
        return None

def query_to_dataframe(query):
    """Execute SQL query and return pandas DataFrame"""
    engine = get_sqlalchemy_engine()
    if engine is None:
        return None
    
    try:
        df = pd.read_sql(query, engine)
        return df
    except Exception as e:
        print(f"Query failed: {e}")
        return None
    finally:
        engine.dispose()

def save_json(data, filepath):
    """Save data as JSON file"""
    try:
        with open(filepath, 'w') as f:
            json.dump(data, f, indent=2, default=str)
        return True
    except Exception as e:
        print(f"Failed to save JSON: {e}")
        return False

def load_json(filepath):
    """Load JSON file"""
    try:
        with open(filepath, 'r') as f:
            return json.load(f)
    except Exception as e:
        print(f"Failed to load JSON: {e}")
        return None

def format_timestamp():
    """Get current timestamp as string"""
    return datetime.now().strftime('%Y-%m-%d %H:%M:%S')

def calculate_accuracy(y_true, y_pred):
    """Calculate prediction accuracy"""
    from sklearn.metrics import mean_absolute_percentage_error, r2_score
    
    try:
        mape = mean_absolute_percentage_error(y_true, y_pred)
        r2 = r2_score(y_true, y_pred)
        
        # Convert to accuracy percentage
        accuracy = max(0, (1 - mape)) * 100
        
        return {
            'accuracy': round(accuracy, 2),
            'r2_score': round(r2, 4),
            'mape': round(mape, 4)
        }
    except Exception as e:
        print(f"Failed to calculate accuracy: {e}")
        return {'accuracy': 0, 'r2_score': 0, 'mape': 1}
