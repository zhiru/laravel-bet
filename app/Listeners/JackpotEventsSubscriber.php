<?php

namespace Aireset\Listeners;

use Aireset\Activity;
use Aireset\Events\Jackpot\NewJackpot;
use Aireset\Events\Jackpot\JackpotEdited;
use Aireset\Events\Jackpot\DeleteJackpot;
use Aireset\Events\User\UserEventContract;
use Aireset\Services\Logging\UserActivity\Logger;

class JackpotEventsSubscriber
{
    /**
     * @var UserActivityLogger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onNewJackpot(NewJackpot $event)
    {
        $jackpot = $event->getNewJackpot();

        $this->logger->log('New Jackpot / ' . $jackpot->name . ', Shop ' . $jackpot->shop_id);
    }

    public function onJackpotEdited(JackpotEdited $event)
    {
        $jackpot = $event->getEditedJackpot();
        $changes = $jackpot->getChanges();

        $text = 'Update Jackpot / ' . $jackpot->name . ' / ';

        if( count($changes)){
            foreach($changes AS $key=>$change){
                $text .= $key . '=' . $change . ', ';
            }
        }

        $text = str_replace('  ', ' ', $text);
        $text = trim($text, ' ');
        $text = trim($text, '/');
        $text = trim($text, ',');

        $this->logger->log($text);
    }

    public function onDeleteJackpot(DeleteJackpot $event)
    {
        $jackpot = $event->getDeleteJackpot();

        $this->logger->log('Delete Jackpot / ' . $jackpot->name . ', Shop ' . $jackpot->shop_id);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $class = 'Aireset\Listeners\JackpotEventsSubscriber';

        $events->listen(NewJackpot::class, "{$class}@onNewJackpot");
        $events->listen(JackpotEdited::class, "{$class}@onJackpotEdited");
        $events->listen(DeleteJackpot::class, "{$class}@onDeleteJackpot");
    }
}
