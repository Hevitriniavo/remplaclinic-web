<?php
namespace App\Service\User;

use App\Repository\UserRoleRepository;
use Doctrine\ORM\EntityNotFoundException;

class RoleService
{
    public function __construct(private UserRoleRepository $userRoleRepository) {}

    public function getRoles(?array $roleIds): array
    {
        $roles = [];
        if (is_array($roleIds)) {
            foreach ($roleIds as $roleId) {
                $role = $this->userRoleRepository->find($roleId);
                if (!$role) {
                    throw new EntityNotFoundException("No role found for ID: $roleId");
                }
                $roles[] = $role;
            }
        }
        return $roles;
    }
}
