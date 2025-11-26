<?php

namespace App\Services;

use App\Contracts\ILogger;

class LogService implements ILogger
{
    /**
     * @param string $folder
     * @param string $text
     * @param int $mkDirPermission
     * @return void
     */
    public function log(string $folder, string $text, int $mkDirPermission = 0777): void
    {
        $folderPath = storage_path("logs/$folder");

        if (!is_dir($folderPath)) {
            mkdir($folderPath, $mkDirPermission, true);
        }

        $filePath = $folderPath . '/' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . " : $text\n";

        file_put_contents($filePath, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
