<?php

namespace App\Message\Console;

use App\Entity\AppImportationScript;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
final class RunCommandMessageHandler
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(RunCommandMessage $message)
    {
        $app = new Application($this->kernel);
        $input = new StringInput((string) $message);
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_SILENT, false);

        $app->setAutoExit(false);
        $app->setCatchExceptions($message->catchExceptions);

        $appScript = $this->getAppImportationScrip($message->options);

        try {
            $exitCode = $app->run($input, $output);
        } catch (\Throwable $e) {
            $this->logCommandResult($appScript, Command::FAILURE, implode('\n', [
                $e->getMessage(),
                $output->fetch()
            ]));
        }

        if ($message->throwOnFailure && Command::SUCCESS !== $exitCode) {
            $this->logCommandResult($appScript, $exitCode, implode('\n', [
                \sprintf('Command "%s" exited with code "%s".', $message, $exitCode),
                $output->fetch()
            ]));
        }

        $this->logCommandResult($appScript, $exitCode, $output->fetch()); // TODO: change output to $output->fetch()
    }

    private function getAppImportationScrip(array $options): ?AppImportationScript
    {
        if(!empty($options) && array_key_exists('app_importation_id', $options)) {
            return $this->em->find(AppImportationScript::class, $options['app_importation_id']);
        }

        return null;
    }

    private function logCommandResult(AppImportationScript $script, int $exitCode, string $msg = '')
    {
        if (!is_null($script)) {
            $script
                ->setOutput($msg)
                ->setStatus($exitCode === Command::SUCCESS ? AppImportationScript::SUCCESS : AppImportationScript::FAILED)
            ;

            $this->em->flush();
        }
    }
}
