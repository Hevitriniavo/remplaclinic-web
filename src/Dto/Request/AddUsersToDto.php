<?php
namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AddUsersToDto
{
    public function __construct(
        #[Assert\NotNull()]
        public array $users,
    ) {
    }
}