<?php

namespace App\DrupalDataMigration;

use Doctrine\DBAL\Connection;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class DrupalMigrationBase implements DrupalMigration
{
    const DRUPAL_MIGRATION_BASE_URI = 'http://remplaclinic-fr-local/migrations.php';

    protected Connection $connection;
    protected HttpClientInterface $httpClient;
    protected ?DrupalMigrationEventHandlerInterface $eventHandler;
    protected array $extraOptions;
    
    public function __construct(array $options = [])
    {
        $this->connection = $options['connection'] ?: null;
        $this->httpClient = $options['http'] ?: null;
        $this->eventHandler = empty($options['event_handler']) ? null : $options['event_handler'];
        $this->extraOptions = empty($options['cmd_options']) ? [] : $options['cmd_options'];
    }

    abstract public function migrate();

    protected function getData(array $queryParams = []): array
    {
        if (is_null($this->httpClient)) {
            return [];
        }

        $response = $this->httpClient->request(
            'GET',
            $this->getOption('base_uri', self::DRUPAL_MIGRATION_BASE_URI),
            [
                'query' => $queryParams,
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new \RuntimeException('Failed to fetch from Drupal migration endpoint. (error code: ' . $statusCode . ', message: ' . $response->getContent(false) . ')');
        }

        return $response->toArray();
    }

    protected function keyData(array $data, string $keyPath, mixed $defaultValue = null): mixed
    {
        $keys = explode('.', $keyPath);
        $dataValues = $data;

        foreach($keys as $key) {
            if (!array_key_exists($key, $dataValues)) {
                return $defaultValue;
            }

            $dataValues = $dataValues[$key];
        }

        return $dataValues;
    }

    protected function truncValue(?string $value, int $length = 255): ?string
    {
        if (is_string($value) && strlen($value) > $length) {
            return substr($value, 0, $length);
        }
        
        return $value;
    }

    protected function hasOption(string $name): bool
    {
        return !empty($this->extraOptions[$name]);
    }

    protected function getOption(string $name, mixed $defaultValue = null): mixed
    {
        if ($this->hasOption($name)) {
            return $this->extraOptions[$name];
        }

        return $defaultValue;
    }

    protected function addExtraCriteria(&$params, string $optionName, string $paramName, $defaultValue = null)
    {
        $value = $this->getOption($optionName, $defaultValue);
        
        if (!empty($value)) {
            $params[$paramName] = $value;
        }
    }

    protected function dispatchEvent(string $eventName, array $options = [])
    {
        if (!is_null($this->eventHandler)) {
            $this->eventHandler->handleEvent($eventName, $options);
        }
    }

    protected function log(string $logType, string $message, array $additionalOptions = [])
    {
        if (!is_null($this->eventHandler)) {
            $options = array_merge($this->extraOptions, $additionalOptions);
            $this->eventHandler->handleEvent(sprintf('<%s>%s</%s>', $logType, $message, $logType), $options);
        }
    }
}