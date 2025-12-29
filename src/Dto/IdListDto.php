<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class IdListDto
{
    public function __construct(
        #[Assert\NotNull()]
        public array $ids,
    ) {
    }
}