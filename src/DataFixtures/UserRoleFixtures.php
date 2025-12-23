<?php

namespace App\DataFixtures;

use App\Entity\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserRoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $role = new UserRole();
        $role->setId(3);
        $role->setRole("Administrator");
        $manager->persist($role);

        $role = new UserRole();
        $role->setId(4);
        $role->setRole("Remplaçant");
        $manager->persist($role);

        $role = new UserRole();
        $role->setId(5);
        $role->setRole("Clinique / Service clinique");
        $manager->persist($role);

        $role = new UserRole();
        $role->setId(6);
        $role->setRole("Cabinet");
        $manager->persist($role);

        $role = new UserRole();
        $role->setId(7);
        $role->setRole("Directeur");
        $manager->persist($role);

        $role = new UserRole();
        $role->setId(8);
        $role->setRole("Webmaster");
        $manager->persist($role);

        $manager->flush();
    }
}
