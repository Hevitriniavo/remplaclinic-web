<?php

namespace App\Dto\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserFilesDto
{
    public function __construct(
        public ?UploadedFile $cv,
        public ?UploadedFile $diplom,
        public ?UploadedFile $licence,
    ) {}
}
