<?php
namespace App\Service\Taches;

use App\Entity\AppConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AppConfigurationService
{
    const REQUIRED = [
        'APP_EMAIL_FROM_NAME',
        'APP_EMAIL_FROM_EMAIL',
    ];

    /**
     * Store all loaded configurations as array ($name => $value).
     */
    private $configurations = [];

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * Load all configuration
     */
    public function loadAll(array $names = [], bool $cacheResult = true, bool $inludeInactive = false): array
    {
        $result = [];

        $qb = $this->em->createQueryBuilder()
            ->from(AppConfiguration::class, 'a')
            ->select('a');

        if (!$inludeInactive) {
            $qb->andWhere('a.active = :active')
                ->setParameter('active', true);
        }
        
        $or = $qb->expr()->orX();

        $nameIndex = 0;
        foreach($names as $name) {
            $paramName = 'name_' . $nameIndex;
            $or->add('a.name = :' . $paramName);
            $qb->setParameter($paramName, $name);

            $nameIndex++;
        }

        $qb->andWhere($or);

        $result = $qb->getQuery()->getResult();

        $resultAssoc = [];

        foreach($result as $row) {
            $resultAssoc[$row->getName()] = $row->getValue();

            if ($cacheResult) {
                $this->configurations[$row->getName()] = $row->getValue();
            }
        }

        return $resultAssoc;
    }

    public function getValue(string $name, bool $inludeInactive = false, bool $throwExceptionIfNotFound = false): ?string
    {
        if (!array_key_exists($name, $this->configurations)) {
            $this->loadAll([$name], true, $inludeInactive);
        }

        $result = array_key_exists($name, $this->configurations) ? $this->configurations[$name] : null;
        if ($throwExceptionIfNotFound && is_null($result)) {
            throw new Exception('No app configuration with name {' . $name . '}');
        }

        return $result;
    }

    public function checkRequiredValues(): array
    {
        $values = $this->loadAll(self::REQUIRED, false);
        $missing = [];

        foreach(self::REQUIRED as $name) {
            if (!array_key_exists($name, $values)) {
                $missing[] = $name;
            }
        }

        return $missing;
    }

    public function resetCache()
    {
        $this->configurations = [];
    }
}