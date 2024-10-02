<?php

namespace App\Jobs;

use App\Events\ImapDeleteMessageEvent;
use App\Events\ImapNewMessageEvent;
use App\Models\OauthAccount;
use App\Services\CustomImapIdleService;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class IdleEmailFolder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $oauthAccount, $folderName, $esEmailMessageModel;
    protected $maxRetries = 100; // Max number of retries
    protected $retryDelay = 30; // Delay in seconds before retry

    /**
     * Create a new job instance.
     */
    public function __construct(OauthAccount $oauthAccount, string $folderName)
    {
        $this->oauthAccount = $oauthAccount;
        $this->folderName = $folderName;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return formatFolderId($this->oauthAccount->id, $this->folderName);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $attempts = 0;
        
        while ($attempts < $this->maxRetries) {
            try {
                $mailServiceInstance = new MailService($this->oauthAccount);
                $mailServiceClient = $mailServiceInstance->getClient();
                $folder = $mailServiceClient->getFolder($this->folderName);

                $customImapIdleService = new CustomImapIdleService($mailServiceClient);

                $customImapIdleService->folderIdle($folder, function ($eventName, $payload) {

                    Log::info("New event $eventName with payload: " . json_encode($payload));

                    if ($eventName == 'message_new') {
                        event(new ImapNewMessageEvent($this->oauthAccount, $payload['message'], $this->folderName));
                    } elseif ($eventName == 'message_deleted') {
                        event(new ImapDeleteMessageEvent($this->oauthAccount, $payload['messageUid'], $this->folderName));
                    } elseif ($eventName == 'flag_new') {
                        event(new ImapNewMessageEvent($this->oauthAccount, $payload['message'], $this->folderName));
                    }

                }, 3600);

            } catch (Exception $e) {
                $attempts++;
                Log::error("Attempt $attempts:  Exception in account: " . $this->oauthAccount->id . " and Folder: " . $this->folderName);

                // Check if max retries reached
                if ($attempts >= $this->maxRetries) {
                    Log::error("Max retry attempts reached. Exception in account: " . $this->oauthAccount->id . " and Folder: " . $this->folderName);
                    Log::error($e->getMessage());
                    $this->fail($e);
                }

                // Sleep before retrying
                sleep($this->retryDelay);
            }
        }
    }
}
