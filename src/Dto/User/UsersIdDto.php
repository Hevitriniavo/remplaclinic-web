<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class UsersIdDto
{
    public function __construct(
        #[Assert\NotNull()]
        public array $ids,
    ) {
    }
}