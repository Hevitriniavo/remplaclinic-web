<?php
namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class RequestsIdDto
{
    public function __construct(
        #[Assert\NotNull()]
        public array $ids,
    ) {
    }
}