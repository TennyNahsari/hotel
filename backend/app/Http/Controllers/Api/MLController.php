<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class MLController extends Controller
{
    /**
     * Train ML models
     * Admin only - rate limited
     */
    public function trainModels(Request $request): JsonResponse
    {
        // Check owner permission
        if (!$request->user()->hasRole('owner')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Owner access required.'
            ], 403);
        }

        try {
            // Execute Python training script
            $pythonPath = env('PYTHON_PATH', 'python3');
            $scriptPath = base_path('../ml_scripts/train_models.py');

            $result = Process::timeout(600) // 10 minute timeout
                ->run("$pythonPath $scriptPath");

            if ($result->failed()) {
                throw new \Exception('Training script failed: ' . $result->errorOutput());
            }

            // Parse JSON output from Python
            $output = $result->output();
            $lines = explode("\n", trim($output));
            $jsonOutput = end($lines); // Last line is JSON
            
            $trainingResult = json_decode($jsonOutput, true);

            if (!$trainingResult || $trainingResult['status'] !== 'success') {
                throw new \Exception($trainingResult['error'] ?? 'Training failed');
            }

            // Save model versions to database
            foreach ($trainingResult['models'] as $modelInfo) {
                $modelFile = $this->getModelFileName($modelInfo['name']);
                $filePath = storage_path("ml/models/$modelFile");

                if (file_exists($filePath)) {
                    // Deactivate previous versions
                    DB::table('ml_model_versions')
                        ->where('model_name', $modelInfo['name'])
                        ->update(['is_active' => false]);

                    // Insert new version
                    DB::table('ml_model_versions')->insert([
                        'model_name' => $modelInfo['name'],
                        'version' => now()->format('Ymd_His'),
                        'accuracy' => $modelInfo['accuracy'],
                        'trained_samples' => $modelInfo['samples'],
                        'file_path' => $filePath,
                        'file_size' => filesize($filePath),
                        'is_active' => true,
                        'trained_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Audit log
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'train_ml_models',
                'model_type' => 'MLModel',
                'model_id' => null,
                'changes' => json_encode([
                    'models_trained' => count($trainingResult['models']),
                    'total_samples' => $trainingResult['total_samples'],
                    'average_accuracy' => $trainingResult['average_accuracy']
                ]),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Models trained successfully',
                'data' => [
                    'models_trained' => count($trainingResult['models']),
                    'total_samples' => $trainingResult['total_samples'],
                    'average_accuracy' => $trainingResult['average_accuracy'],
                    'trained_at' => $trainingResult['trained_at']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Training failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate predictions
     * Admin only
     */
    public function generatePredictions(Request $request): JsonResponse
    {
        // Check owner permission
        if (!$request->user()->hasRole('owner')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Owner access required.'
            ], 403);
        }

        try {
            // Execute Python prediction script
            $pythonPath = env('PYTHON_PATH', 'python3');
            $scriptPath = base_path('../ml_scripts/predict.py');

            $result = Process::timeout(120) // 2 minute timeout
                ->run("$pythonPath $scriptPath");

            if ($result->failed()) {
                throw new \Exception('Prediction script failed: ' . $result->errorOutput());
            }

            // Parse JSON output
            $output = $result->output();
            $lines = explode("\n", trim($output));
            $jsonOutput = end($lines);
            
            $predictionResult = json_decode($jsonOutput, true);

            if (!$predictionResult || $predictionResult['status'] !== 'success') {
                throw new \Exception($predictionResult['error'] ?? 'Prediction failed');
            }

            // Save predictions to database
            foreach ($predictionResult['predictions'] as $type => $data) {
                // Delete old predictions of same type
                DB::table('ai_predictions')
                    ->where('prediction_type', $type)
                    ->delete();

                // Insert new prediction
                DB::table('ai_predictions')->insert([
                    'prediction_type' => $type,
                    'prediction_data' => json_encode($data),
                    'confidence_score' => $this->calculateAvgConfidence($data),
                    'generated_at' => now(),
                    'expires_at' => now()->addDay(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Audit log
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'generate_predictions',
                'model_type' => 'AIPrediction',
                'model_id' => null,
                'changes' => json_encode([
                    'predictions_generated' => count($predictionResult['predictions'])
                ]),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Predictions generated successfully',
                'data' => [
                    'generated_at' => $predictionResult['generated_at'],
                    'expires_at' => $predictionResult['expires_at'],
                    'predictions_count' => count($predictionResult['predictions'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prediction generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest predictions
     * All authenticated users
     */
    public function getPredictions(Request $request): JsonResponse
    {
        try {
            $predictions = DB::table('ai_predictions')
                ->where('expires_at', '>', now())
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->prediction_type => [
                            'data' => json_decode($item->prediction_data, true),
                            'confidence_score' => $item->confidence_score,
                            'generated_at' => $item->generated_at
                        ]
                    ];
                });

            if ($predictions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No predictions available. Please generate predictions first.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $predictions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch predictions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get model information
     * All authenticated users
     */
    public function getModelInfo(Request $request): JsonResponse
    {
        try {
            $models = DB::table('ml_model_versions')
                ->where('is_active', true)
                ->orderBy('trained_at', 'desc')
                ->get()
                ->map(function ($model) {
                    return [
                        'model_name' => $model->model_name,
                        'version' => $model->version,
                        'accuracy' => $model->accuracy,
                        'trained_samples' => $model->trained_samples,
                        'file_size_mb' => round($model->file_size / 1024 / 1024, 2),
                        'trained_at' => $model->trained_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'models' => $models,
                    'total_models' => $models->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch model info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get model file name from model name
     */
    private function getModelFileName(string $modelName): string
    {
        $map = [
            'room_demand' => 'room_demand_model.pkl',
            'hall_peak' => 'hall_peak_model.pkl',
            'menu_popularity' => 'menu_popularity_model.pkl'
        ];

        return $map[$modelName] ?? "$modelName.pkl";
    }

    /**
     * Helper: Calculate average confidence score
     */
    private function calculateAvgConfidence($data): float
    {
        if (is_array($data)) {
            $scores = [];
            
            // Extract confidence scores recursively
            array_walk_recursive($data, function ($value, $key) use (&$scores) {
                if ($key === 'confidence') {
                    $scores[] = $value;
                }
            });

            return !empty($scores) ? round(array_sum($scores) / count($scores), 2) : 0.75;
        }

        return 0.75;
    }
}
