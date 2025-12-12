<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait Logger
{
    /**
     * @param string $folder
     * @param array $context
     * @param int $mkDirPermission
     * @return void
     */
    public function log(string $folder, array $context, int $mkDirPermission = 0777): void
    {
        $folderPath = storage_path("logs/$folder");

        if (!is_dir($folderPath)) {
            File::makeDirectory($folderPath, $mkDirPermission, true);
        }

        $filePath = $folderPath . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');

        $logData = [];
        foreach ($context as $key => $value) {
            $logData[] = "{$key} : " . (is_null($value) ? 'null' : (string)$value);
        }

        $logText = implode(', ', $logData);
        $logEntry = "{$timestamp} : {$logText}\n";
        File::append($filePath, $logEntry);
    }
}
