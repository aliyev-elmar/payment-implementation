<?php

namespace App\Contracts;

interface ILogger
{
    /**
     * @param string $folder
     * @param array $context
     * @param int $mkDirPermission
     * @return void
     */
    public function log(string $folder, array $context, int $mkDirPermission = 0777): void;
}
