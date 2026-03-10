<?php
namespace App\Service\User;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityNotFoundException;

class RegionService
{
    public function __construct(private RegionRepository $regionRepository) {}

    public function getRegions(?array $regionIds): array
    {
        $regions = [];
        if (is_array($regionIds)) {
            foreach ($regionIds as $regionId) {
                $region = $this->regionRepository->find($regionId);
                if (!$region) {
                    throw new EntityNotFoundException("No region found for ID: $regionId");
                }
                $regions[] = $region;
            }
        }
        return $regions;
    }

    public function getRegion(?int $regionId): ?Region
    {
        if (is_null($regionId)) {
            return null;
        }

        return $this->getRegions([$regionId])[0];
    }

    public function getRegionByCodePostal(string $codePostale): ?Region
    {
        $numDept = $this->getDepartmentCodeFromPostalCode($codePostale);
        if (is_null($numDept)) {
            return null;
        }

        $regionsNumDept = [];

        $regionsNumDept['Alsace'] = '67,68';
        $regionsNumDept['Aquitaine'] = '24,33,40,47,64';
        $regionsNumDept['Auvergne'] = '03,15,43,63';
        $regionsNumDept['Basse-Normandie'] = '14,50,61';
        $regionsNumDept['Bourgogne'] = '21,58,71,89';
        $regionsNumDept['Bretagne'] = '22,29,35,56';
        $regionsNumDept['Centre'] = '18,28,36,37,41,45';
        $regionsNumDept['Champagne-Ardenne'] = '08,10,51,52';
        $regionsNumDept['Corse'] = '2A,2B,20';
        $regionsNumDept['Franche-Comté'] = '25,39,70,90';
        $regionsNumDept['Haute-Normandie'] = '27,76';
        $regionsNumDept['Ile-de-France'] = '75,77,78,91,92,93,94,95';
        $regionsNumDept['Languedoc-Roussillon'] = '11,30,34,48,66';
        $regionsNumDept['Limousin'] = '19,23,87';
        $regionsNumDept['Lorraine'] = '54,55,57,88';
        $regionsNumDept['Midi-Pyrénées'] = '09,12,31,32,46,65,81,82';
        $regionsNumDept['Nord-Pas-de-Calais'] = '59,62';
        $regionsNumDept['Pays de la Loire'] = '44,49,53,72,85';
        $regionsNumDept['Picardie'] = '02,60,80';
        $regionsNumDept['Poitou-Charentes'] = '16,17,79,86';
        $regionsNumDept['Provence-Alpes-Côte-d\'Azur'] = '04,05,06,13,83,84';
        $regionsNumDept['Rhône-Alpes'] = '01,07,26,38,42,69,73,74';
        $regionsNumDept['Guadeloupe'] = '971';
        $regionsNumDept['Guyane'] = '973';
        $regionsNumDept['La Réunion'] = '974';
        $regionsNumDept['Martinique'] = '972';
        $regionsNumDept['Mayotte'] = '976';
        $regionsNumDept['Nouvelle-Calédonie'] = '988';
        $regionsNumDept['Polynésie Française'] = '987';
        $regionsNumDept['Terres Australes et Antarctiques'] = '984';
        $regionsNumDept['Wallis et Futuna'] = '986';

        $departementRegions = array();

        foreach($regionsNumDept as $key => $numDepartements){	
            $departements = explode(',', $numDepartements);
            foreach($departements as $dept){
                $departementRegions[$dept] = $key;
            }
        }

        if (!array_key_exists($numDept, $departementRegions)) {
            return null;
        }

        return $this->regionRepository->findOneByName($departementRegions[$numDept]);
    }

    public function getDepartmentCodeFromPostalCode(string $postalCode): ?string
    {
        $postalCode = substr($postalCode, 0, 5);

        if (!preg_match('/^\d{5}$/', $postalCode)) {
            return null;
        }

        // Departement d'outre mer (971–989)
        if (preg_match('/^(97|98)/', $postalCode)) {
            return substr($postalCode, 0, 3);
        }

        // Corse
        if (str_starts_with($postalCode, '20')) {
            $num = (int) $postalCode;

            if ($num <= 20199) {
                return '2A';
            }

            return '2B';
        }

        // Departement metropolitaine
        return substr($postalCode, 0, 2);
    }
}
