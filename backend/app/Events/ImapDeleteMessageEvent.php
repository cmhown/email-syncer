<?php

namespace App\Events;

use App\Models\OauthAccount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImapDeleteMessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $oauthAccount;
    public $messageUid;
    public $folderName;

    /**
     * Create a new event instance.
     * @return void
     */
    public function __construct(OauthAccount $oauthAccount, String | Int $messageUid, String $folderName) {
        $this->oauthAccount = $oauthAccount;
        $this->messageUid = $messageUid;
        $this->folderName = $folderName;
    }


}
