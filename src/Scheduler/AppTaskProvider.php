<?php
namespace App\Scheduler;

use App\Message\Request\RequestArchivageMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('request_archivage')]
class AppTaskProvider implements ScheduleProviderInterface
{
    private $schedule;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= (new Schedule())
            ->with(
                RecurringMessage::cron('@daily', new RequestArchivageMessage()),
                // RecurringMessage::every('2 minutes', new RequestArchivageMessage()),
            );
    }
}