<?php

namespace App\Listeners;

use App\Events\ImapNewMessageEvent;
use App\Models\Elastic\EmailMessage;
use App\Services\EmailUpdateService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImapNewMessageHandler implements ShouldQueue
{

    public $connection = 'redis';

    /**
     * Handle the event.
     */
    public function handle(ImapNewMessageEvent $event): void
    {
        $oauthAccount = $event->oauthAccount;
        $message = $event->message;
        $folderName = $event->folderName;

        $esEmailMessage = new EmailMessage();
        $esEmailMessage->add($oauthAccount, $message, $folderName);

        // To send information to FE
        EmailUpdateService::sendEmailUpdate($oauthAccount->id, $oauthAccount->provider, $folderName);

    }
}
