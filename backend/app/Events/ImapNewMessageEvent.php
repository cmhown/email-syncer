<?php

namespace App\Events;

use App\Models\OauthAccount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImapNewMessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $oauthAccount;
    public $message;
    public $folderName;

    /**
     * Create a new event instance.
     * @return void
     */
    public function __construct(OauthAccount $oauthAccount, array $message, String $folderName) {
        $this->oauthAccount = $oauthAccount;
        $this->message = $message;
        $this->folderName = $folderName;
    }

}
