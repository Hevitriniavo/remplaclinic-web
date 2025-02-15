<?php

namespace App\DataFixtures;

use App\Entity\Speciality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SpecialityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // create parent
        $parents = [];
        for ($i = 0; $i < 10; $i++) {
            $speciality = new Speciality();
            $speciality->setName('Speciality Parent '. $i);
            $manager->persist($speciality);

            $parents[] = $speciality;
        }
        // create child
        foreach ($parents as $index => $parent) {
            for ($i = 0; $i < 10; $i++) {
                $speciality = new Speciality();
                $speciality->setName('Speciality Child '. $index . $i);
                $speciality->setSpecialityParent($parent);
                $manager->persist($speciality);
            }
        }
        $manager->flush();
    }
}
