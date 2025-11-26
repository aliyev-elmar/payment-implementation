<?php

namespace App\Contracts;

interface ILogger
{
    /**
     * @param string $folder
     * @param string $text
     * @param int $mkDirPermission
     * @return void
     */
    public function log(string $folder, string $text, int $mkDirPermission = 0777): void;
}
