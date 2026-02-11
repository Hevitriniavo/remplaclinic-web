<?php
namespace App\Scheduler;

use App\Message\Request\RequestArchivageMessage;
use App\Message\User\UserAbonnementMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CallbackMessageProvider;

#[AsSchedule('end_date_checker')]
class AppTaskProvider implements ScheduleProviderInterface
{
    private $schedule;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= (new Schedule())
            ->with(
                RecurringMessage::cron('@daily', new CallbackMessageProvider([$this, 'checkTodayElements'], 'end_date_checker_midnight')),
                // RecurringMessage::every('2 minutes', new CallbackMessageProvider([$this, 'checkTodayElements'], 'end_date_checker_midnight')),
            );
    }

    public function checkTodayElements(MessageContext $context)
    {
        yield new RequestArchivageMessage();
        yield new UserAbonnementMessage();
    }
}