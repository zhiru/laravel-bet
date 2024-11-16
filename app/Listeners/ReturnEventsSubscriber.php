<?php

namespace Aireset\Listeners;

use Aireset\Activity;
use Aireset\Events\Returns\ReturnEdited;
use Aireset\Events\Returns\NewReturn;
use Aireset\Events\Returns\DeleteReturn;
use Aireset\Events\User\UserEventContract;
use Aireset\Services\Logging\UserActivity\Logger;

class ReturnEventsSubscriber
{
    /**
     * @var UserActivityLogger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onNewReturn(NewReturn $event)
    {
        $return = $event->getNewReturn();

        $this->logger->log('New Return / ' . $return->id . ', Shop ' . $return->shop_id);
    }

    public function onReturnEdited(ReturnEdited $event)
    {
        $return = $event->getEditedReturn();
        $changes = $return->getChanges();

        $text = 'Update Return / ' . $return->id . ' / ';

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

    public function onDeleteReturn(DeleteReturn $event)
    {
        $return = $event->getDeleteReturn();
        $this->logger->log('Delete Return / ' . $return->id . ', Shop ' . $return->shop_id);
    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $class = 'Aireset\Listeners\ReturnEventsSubscriber';

        $events->listen(NewReturn::class, "{$class}@onNewReturn");
        $events->listen(ReturnEdited::class, "{$class}@onReturnEdited");
        $events->listen(DeleteReturn::class, "{$class}@onDeleteReturn");
    }
}
