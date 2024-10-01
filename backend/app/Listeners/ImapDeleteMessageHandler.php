<?php

namespace App\Listeners;

use App\Events\ImapDeleteMessageEvent;
use App\Models\Elastic\EmailMessage;
use App\Services\EmailUpdateService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImapDeleteMessageHandler implements ShouldQueue
{

    public $connection = 'redis';

    /**
     * Handle the event.
     */
    public function handle(ImapDeleteMessageEvent $event): void
    {
        $folderName = $event->folderName;
        $oauthId = $event->oauthAccount->id;
        $messageUid = $event->messageUid;

        $esEmailMessage = new EmailMessage();
        $esEmailMessage->delete(formatMessageId($oauthId, $folderName, $messageUid));

        // To send information to FE
        EmailUpdateService::sendEmailUpdate($oauthId, $event->oauthAccount->provier, $folderName);

    }
}
