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
        'DRUPAL_SITE_MIGRATION_URL',
        'USER_INSCRIPTION_TARGET_EMAIL',
        'REQUEST_NOTIFICATION_TARGET_EMAIL',
        'CONTACT_NOTIFICATION_TARGET_EMAIL',
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
            // reload to avoid proxy object
            $this->em->refresh($row);

            $resultAssoc[$row->getName()] = $row->getValue();

            if ($cacheResult) {
                $this->configurations[$row->getName()] = $row->getValue();
            }
        }

        return $resultAssoc;
    }

    public function getValue(string $name, bool $inludeInactive = false, bool $throwExceptionIfNotFound = false, bool $ignoreCache = false): ?string
    {
        if (!array_key_exists($name, $this->configurations) || $ignoreCache) {
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

    public function setValue(string $name, ?string $value, bool $active = true): AppConfiguration
    {
        $repo = $this->em->getRepository(AppConfiguration::class);
        $config = $repo->findOneBy(['name' => $name]);

        if (is_null($config)) {
            $config = new AppConfiguration();
            $config->setName($name);
        }

        $config->setValue($value);
        $config->setActive($active);

        $this->em->persist($config);
        $this->em->flush();

        // update cache
        $this->configurations[$name] = $value;

        return $config;
    }

    public function deleteValue(string $name): void
    {
        $repo = $this->em->getRepository(AppConfiguration::class);
        $config = $repo->findOneBy(['name' => $name]);

        if (!is_null($config)) {
            $this->em->remove($config);
            $this->em->flush();
        }

        // update cache
        if (array_key_exists($name, $this->configurations)) {
            unset($this->configurations[$name]);
        }
    }

    public function hasValue(string $name, bool $inludeInactive = false): bool
    {
        $value = $this->getValue($name, $inludeInactive);

        return !is_null($value);
    }

    public function resetCache()
    {
        $this->configurations = [];
    }
}