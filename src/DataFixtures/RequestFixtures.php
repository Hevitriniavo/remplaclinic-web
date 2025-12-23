<?php

namespace App\DataFixtures;

use App\Entity\Region;
use App\Entity\Request;
use App\Entity\RequestReplacementType;
use App\Entity\RequestType;
use App\Entity\Speciality;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RequestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setName("User 1")
            ->setSurname('Test')
            ->setEmail('user1.test@test.com')
            ->setCivility('Mr')
            ->setPassword('test')
            ->setStatus(true)
        ;
        $manager->persist($user);

        $region = (new Region())
            ->setName("Region Test");
        
        $manager->persist($region);

        $speciality = (new Speciality())
            ->setName("Speciality Test");
        
        $manager->persist($speciality);

        $includedValues = [1, 2, 3];
        $replacementTypes = RequestReplacementType::cases();

        for ($i = 1; $i <= 10; $i++) {
            $start = new DateTime("-{$i}days");
            $end = new DateTime("+{$i}days");
            $startFr = date('d/m/Y H:i', strtotime("-{$i}days ago"));
            $endFr = date('d/m/Y H:i', strtotime("+{$i}days ago"));

            $request = (new Request())
                ->setTitle("Demande de remplacement du " . $startFr . ' au ' . $endFr)
                ->setRequestType(RequestType::REPLACEMENT)
                ->setCreatedAt($start)
                ->setStartedAt($start)
                ->setShowEndAt(true)
                ->setEndAt($end)
                ->setLastSentAt($start)
                ->setStatus(1)
                ->setApplicant($user)
                ->setRegion($region)
                ->setSpeciality($speciality)
                ->setRemuneration(($i * 1000) . ' par mois')
                ->setComment("Comment Test")
                ->setPositionCount(11 - $i)
                ->setAccomodationIncluded($includedValues[array_rand($includedValues)])
                ->setTransportCostRefunded($includedValues[array_rand($includedValues)])
                ->setReplacementType($replacementTypes[array_rand($replacementTypes)])
            ;

            $manager->persist($request);
        }

        for ($i = 1; $i <= 10; $i++) {
            $start = new DateTime("-{$i}days");
            $end = new DateTime("+{$i}days");
            $startFr = date('d/m/Y H:i', strtotime("-{$i}days ago"));
            $endFr = date('d/m/Y H:i', strtotime("+{$i}days ago"));

            $request = (new Request())
                ->setTitle("Proposition d'installation du " . $startFr . ' au ' . $endFr)
                ->setRequestType(RequestType::INSTALLATION)
                ->setCreatedAt($start)
                ->setStartedAt($start)
                ->setShowEndAt(false)
                ->setLastSentAt($start)
                ->setStatus(1)
                ->setApplicant($user)
                ->setRegion($region)
                ->setSpeciality($speciality)
                ->setRemuneration(($i * 1000) . ' par mois')
                ->setComment("Comment Test")
            ;

            $manager->persist($request);
        }

        $manager->flush();
    }
}
