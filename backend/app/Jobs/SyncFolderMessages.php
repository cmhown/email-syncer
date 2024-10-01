<?php

namespace App\Jobs;

use App\Facades\ImapDataParser;
use App\Models\Elastic\EmailMessage;
use App\Models\OauthAccount;
use App\Services\EmailUpdateService;
use App\Services\MailService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFolderMessages implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $oauthAccount, $folderName, $esEmailMessageModel;

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
        return $this->oauthAccount->id . '-' . $this->folderName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Syncing messages for account: " . $this->oauthAccount->id . " and Folder: " . $this->folderName);

        try {
            $this->esEmailMessageModel = new EmailMessage();

            $mailServiceInstance = new MailService($this->oauthAccount);
            $mailServiceClient = $mailServiceInstance->getClient();

            $folder = $mailServiceClient->getFolder($this->folderName);

            // Fetch existing messages from Elasticsearch
            $existingMessageIds = $this->esEmailMessageModel->getFolderMessageIds(formatFolderId($this->oauthAccount->id, $this->folderName));

            // Create an array to track IDs of new messages to add
            $newMessageIds = [];

            // Sync messages in chunks
            $folder->messages()->all()->chunked(function ($messages, $chunk) use (&$newMessageIds) {
                $messages->each(function ($message) use (&$newMessageIds) {
                    $messageData = ImapDataParser::parseMessageData($message);
                    $newMessageIds[] = $messageData['message_id']; // Collect new message IDs
                    $this->esEmailMessageModel->add($this->oauthAccount, $messageData, $this->folderName);
                });
            }, $chunk_size = 100, $start_chunk = 1);

            $messagesToDelete = array_diff($existingMessageIds, $newMessageIds);

            if (!empty($messagesToDelete)) {
                $this->esEmailMessageModel->deleteMessages($messagesToDelete, formatFolderId($this->oauthAccount->id, $this->folderName));
            }
        } catch (Exception $e) {
            Log::error("Exception in account: " . $this->oauthAccount->id . " and Folder: " . $this->folderName);
            Log::error($e->getMessage());

            $this->fail($e);
        }

        EmailUpdateService::sendEmailUpdate($this->oauthAccount->id, $this->oauthAccount->provier, $this->folderName);

        Log::info("Synced message for account: " . $this->oauthAccount->id . " and Folder: " . $this->folderName);
    }
}
