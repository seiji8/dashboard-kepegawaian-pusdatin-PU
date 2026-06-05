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

        $databaseName = config('database.connections.mysql.database');
        $fileName = 'Backup_' . $databaseName . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $filePath = $tempDir . '/' . $fileName;

        // Buka file pointer untuk penulisan langsung (Streaming)
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            abort(500, 'Gagal membuka file backup untuk penulisan.');
        }

        // Tulis header database langsung ke file disk
        fwrite($handle, "-- Database Backup\n");
        fwrite($handle, "-- Generated at: " . Carbon::now()->format('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: {$databaseName}\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

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
            
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, ((array)$createTableStmt[0])[$createTableKey] . ";\n\n");

            // Get Data - Cek apakah tabel ada datanya secara efisien
            $hasData = DB::table($table)->exists();

            if ($hasData) {
                fwrite($handle, "-- Data for table `{$table}`\n");
                
                $insertStatements = [];
                
                // Gunakan cursor() agar kompatibel dengan semua skema tabel tanpa perlu orderBy
                foreach (DB::table($table)->cursor() as $row) {
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

                    // Jika buffer mencapai 100 baris, langsung tulis ke file disk & kosongkan buffer
                    if (count($insertStatements) === 100) {
                        fwrite($handle, "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $insertStatements) . ";\n\n");
                        $insertStatements = [];
                    }
                }

                // Tulis sisa baris yang ada di buffer (jika ada)
                if (count($insertStatements) > 0) {
                    fwrite($handle, "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $insertStatements) . ";\n\n");
                }
            }
        }

        // Tulis penutup dan tutup file pointer
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        // Log aktivitas backup
        \App\Helpers\ActivityLogger::logAdminAction('Mengunduh backup database');

        // Download and delete
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
