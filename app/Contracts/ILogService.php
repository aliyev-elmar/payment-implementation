<?php

namespace App\Contracts;

interface ILogService
{
    /**
     * @param string $logFolder
     * @param string $text
     * @param int $mkDirPermission
     * @return void
     */
    public function log(string $logFolder, string $text, int $mkDirPermission = 0777): void;
}
