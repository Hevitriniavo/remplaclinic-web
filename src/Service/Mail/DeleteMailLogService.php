<?php
namespace App\Service\Mail;

use App\Entity\MailLog;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DeleteMailLogService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {}

    public function delete(int $id): ?MailLog
    {   
        $mailLogs = $this->getMailLogs([$id]);

        if (count($mailLogs) > 0) {
            $this->em->remove($mailLogs[0]);
            $this->em->flush();

            return $mailLogs[0];
        }

        return null;
    }

    public function deleteMultiple(array $ids): array
    {
        $result = $this->getMailLogs($ids);

        foreach($result as $mailLog) {
            $this->em->remove($mailLog);
        }

        $this->em->flush();

        return $result;
    }

    private function getMailLogs(array $ids): array
    {
        $res = [];

        foreach($ids as $id) {
            $mailLog = $this->em->find(MailLog::class, $id);

            if (!$mailLog) {
                throw new Exception('No mail log found for #' . $id);
            }

            $res[] = $mailLog;
        }

        return $res;
    }
}