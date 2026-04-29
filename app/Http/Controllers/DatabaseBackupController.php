<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DatabaseBackupController extends Controller
{
    /**
     * Download database backup as .sql file
     */
    public function download(Request $request)
    {
        // SECURITY: Hanya Super Admin yang bisa mengakses fitur backup database
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk fitur ini.');
        }

        $databaseName = env('DB_DATABASE');
        $fileName = 'Backup_' . $databaseName . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $filePath = $tempDir . '/' . $fileName;

        // Generate SQL native via PHP
        $sqlScript = "-- Database Backup\n";
        $sqlScript .= "-- Generated at: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $sqlScript .= "-- Database: {$databaseName}\n\n";

        $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tablesKey = "Tables_in_{$databaseName}";

        foreach ($tables as $tableRow) {
            // Support varying property names depending on MySQL version/config
            $tableArray = (array)$tableRow;
            $table = array_values($tableArray)[0];

            // Get Create Table statement
            $createTableStmt = DB::select("SHOW CREATE TABLE `{$table}`");
            $createTableKey = 'Create Table';
            
            $sqlScript .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlScript .= ((array)$createTableStmt[0])[$createTableKey] . ";\n\n";

            // Get Data
            $rows = DB::table($table)->get();

            if ($rows->count() > 0) {
                $sqlScript .= "-- Data for table `{$table}`\n";
                $sqlScript .= "INSERT INTO `{$table}` VALUES \n";

                $insertStatements = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ((array)$row as $value) {
                        if (is_null($value)) {
                            $rowValues[] = 'NULL';
                        } else {
                            $value = addslashes($value);
                            $value = str_replace("\n", "\\n", $value);
                            $value = str_replace("\r", "\\r", $value);
                            $rowValues[] = "'" . $value . "'";
                        }
                    }
                    $insertStatements[] = "(" . implode(',', $rowValues) . ")";
                }

                // Chunk inserts to avoid massive single lines if table is big
                $chunks = array_chunk($insertStatements, 100);
                foreach ($chunks as $index => $chunk) {
                    $sqlScript .= implode(",\n", $chunk);
                    if ($index < count($chunks) - 1) {
                        $sqlScript .= ";\nINSERT INTO `{$table}` VALUES \n";
                    } else {
                        $sqlScript .= ";\n\n";
                    }
                }
            }
        }

        $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Write to file
        file_put_contents($filePath, $sqlScript);

        // Log aktivitas backup
        \App\Helpers\ActivityLogger::logAdminAction('Mengunduh backup database');

        // Download and delete
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
