<?php
namespace App\Service\Taches;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\Taches\AppImportationScriptDto;
use App\Entity\AppImportationScript;
use App\Message\Console\AppCommandDispatcher;
use App\Service\DeleteEntityService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AppImportationScriptService
{
    private DeleteEntityService $deleteEntityService;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppConfigurationService $appConfig,
        private readonly AppCommandDispatcher $cmdDispatcher,
    )
    {
        $this->deleteEntityService = new DeleteEntityService($em, AppImportationScript::class);
    }

    public function findAll(DataTableParams $params): DataTableResponse
    {
        return $this->em->getRepository(AppImportationScript::class)->findAllDataTables($params);
    }

    public function store(AppImportationScriptDto $importationScript): AppImportationScript
    {
        $this->checkScriptLabel($importationScript->label);

        $appScript = new AppImportationScript();
        $appScript
            ->setLabel($importationScript->label)
            ->setScript($importationScript->script)
            ->setOptions($importationScript->options)
            ->setStatus(AppImportationScript::CREATED)
            ->setLastId($importationScript->lastId);
        
        $this->em->persist($appScript);
        $this->em->flush();

        return $appScript;
    }

    public function retrieve(int $id): AppImportationScript
    {
        $appScript = $this->em->find(AppImportationScript::class, $id);

        if (is_null($appScript)) {
            throw new Exception('No importation script found for #' . $id);
        }

        return $appScript;
    }

    public function execute(int $id): AppImportationScript
    {
        $appScript = $this->retrieve($id);

        try {
            $appScript->setExecutedAt(new DateTimeImmutable());
            $this->em->flush();

            $options = $appScript->getOptions();
            if (is_null($options)) {
                $options = [];
            }

            $options['app_importation_id'] = $appScript->getId();

            $this->cmdDispatcher->runCommad($appScript->getScript(), $options);

            // refresh the entity
            $this->em->refresh($appScript);

            $appScript
                ->setStatus(AppImportationScript::STARTED)
                ->setOutput('');
            
            $this->em->flush();
        } catch (Exception $e) {
            $appScript
                ->setStatus(AppImportationScript::FAILED)
                ->setOutput($e->getMessage());
            $this->em->flush();

            throw $e;
        }

        return $appScript;
    }

    /**
     * @return AppImportationScript[]
     */
    public function generateDefault(): void
    {
        $defaultScripts = [
            [
                'label' => 'Import roles',
                'script' => 'app:drupal-import-data roles',
                'options' => [],
            ],
            [
                'label' => 'Import specialities',
                'script' => 'app:drupal-import-data specialities',
                'options' => [],
            ],
            [
                'label' => 'Import regions',
                'script' => 'app:drupal-import-data regions',
                'options' => [],
            ],
            [
                'label' => 'Import logo des partenaires (copie des fichiers necessaire apres ce script)',
                'script' => 'app:drupal-import-data logo_partenaires',
                'options' => [],
            ],
            [
                'label' => 'Import temoingnages',
                'script' => 'app:drupal-import-data references',
                'options' => [],
            ],
            [
                'label' => 'Import utilisateurs',
                'script' => 'app:drupal-import-data users',
                'options' => [],
            ],
            [
                'label' => 'Import utilisateurs (copie des CV necessaire apres ce script)',
                'script' => 'app:drupal-import-data user_clinics',
                'options' => [],
            ],
            [
                'label' => "Import des proposiotions d'installations & des demandes de remplacement",
                'script' => 'app:drupal-import-data requests',
                'options' => [],
            ],
            // [
            //     'label' => "Import des candidatures",
            //     'script' => 'app:drupal-import-data request_responses',
            //     'options' => [
            //         'type' => 'candidature_installation'
            //     ],
            // ],
        ];

        $responseTypes = [
            'candidature_installation' => 2,
            'candidature_remplacement' => 10,
        ];
        $limit = 200_000;

        foreach($responseTypes as $responseType => $pageCount) {
            $offset = 0;
            $pageNumber = 0;

            for($i = 0; $i < $pageCount - 1; $i++) {
                $pageNumber = $i + 1;
                $defaultScripts[] = [
                    'label' => "Import des candidatures (part. {$pageNumber}, type: {$responseType})",
                    'script' => 'app:drupal-import-data request_responses',
                    'options' => [
                        'type' => $responseType,
                        'max_count' => $limit,
                        'offset' => $offset,
                    ],
                ];

                $offset += $limit;
            }

            $pageNumber++;

            $defaultScripts[] = [
                'label' => "Import des candidatures (part. {$pageNumber}, type: {$responseType})",
                'script' => 'app:drupal-import-data request_responses',
                'options' => [
                    'type' => $responseType,
                    'offset' => $offset,
                ],
            ];
        }

        foreach($defaultScripts as $defaultScript) {
            $this->checkScriptLabel($defaultScript['label']);

            $defaultScript['options']['base_uri'] = $this->appConfig->getValue('DRUPAL_SITE_MIGRATION_URL');

            $appScript = new AppImportationScript();
            $appScript
                ->setLabel($defaultScript['label'])
                ->setScript($defaultScript['script'])
                ->setOptions($defaultScript['options'])
                ->setStatus(AppImportationScript::CREATED)
            ;

            $this->em->persist($appScript);
        }

        $this->em->flush();
    }

    public function delete(int $id): ?AppImportationScript
    {   
        return $this->deleteEntityService->delete($id);
    }

    public function deleteMultiple(array $ids): array
    {
        return $this->deleteEntityService->deleteMultiple($ids);
    }

    private function checkScriptLabel(string $label)
    {
        $repository = $this->em->getRepository(AppImportationScript::class);
        $appScript = $repository->findOneBy(['label' => $label]);

        if (!is_null($appScript)) {
            throw new Exception('There is already a script with label {' . $appScript->getLabel() . '}');
        }
    }
}