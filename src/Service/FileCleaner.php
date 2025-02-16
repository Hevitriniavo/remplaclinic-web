<?php

namespace App\Service;

class FileCleaner
{
    public function __construct(
        private string $targetDirectory
    ) {}

    public function remove(?string $file): bool
    {
        if (empty($file)) {
            return false;
        }
        $filePath = $this->getTargetDirectory() . '/' . $file;

        if (unlink($filePath)) {
            return true;
        }

        return false;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
