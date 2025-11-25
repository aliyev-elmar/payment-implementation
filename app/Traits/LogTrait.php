<?php

namespace App\Traits;

trait LogTrait
{
    /**
     * @param string $logFolder
     * @param string $text
     * @param int $mkDirPermission
     * @return void
     */
    public function writeLogs(string $logFolder, string $text, int $mkDirPermission = 0777): void
    {
        $folderPath = storage_path("logs/$logFolder");
        if (!file_exists($folderPath)) {
            mkdir($folderPath, $mkDirPermission, true);
        }

        $fileName = date('Y-m-d') . '.log';
        $filePath = storage_path("logs/$logFolder/$fileName");
        $textRow = "\n" . date('Y-m-d H:i:s') . ' : ' . $text;

        if (!file_exists($filePath)) {
            file_put_contents($filePath, $textRow);
        } else {
            $currentFile = fopen($filePath, 'a');
            fwrite($currentFile, $textRow);
            fclose($currentFile);
        }
    }
}
