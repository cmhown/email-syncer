<?php

namespace App\Jobs;

use App\Facades\ImapDataParser;
use App\Models\Elastic\EmailFolder;
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

class SyncEmailFolders implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $oauthAccount;
    public $esEmailFolderModel;

    private $skipFolders = [
        '[Gmail]'
    ];

    private $initialDelay = 120; // Default starting delay
    private $delayIncrement = 60; // Delay increment in seconds

    /**
     * Create a new job instance.
     */
    public function __construct(OauthAccount $oauthAccount, $initialDelay = 0)
    {
        $this->oauthAccount = $oauthAccount;
        $this->initialDelay = $initialDelay; // Allow custom initial delay
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->oauthAccount->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Syncing folders for account: " . $this->oauthAccount->id);

        try {
            $this->esEmailFolderModel = new EmailFolder();

            $mailServiceInstance = new MailService($this->oauthAccount);
            $mailServiceClient = $mailServiceInstance->getClient();

            $folders = $mailServiceClient->getFolders();

            // Deleting existing folders for the account
            $this->esEmailFolderModel->deleteAcountFolders($this->oauthAccount->id);

            // Recursively handle folders and subfolders
            $this->syncFoldersRecursively($folders, null, $this->initialDelay);

        } catch (Exception $e) {
            Log::error("Exception in account: " . $this->oauthAccount->id);
            Log::error($e->getMessage());

            $this->fail($e);
        }
        
        EmailUpdateService::sendEmailUpdate($this->oauthAccount->id, $this->oauthAccount->provider, 'sync_folders');

        Log::info("Synced folders for account: " . $this->oauthAccount->id);
    }

    /**
     * Recursively sync folders and their children.
     */
    private function syncFoldersRecursively($folders, $parentFolderName = null, $currentDelay)
    {
        foreach ($folders as $folder) {
            $folderName = $parentFolderName ? $parentFolderName . '/' . $folder->name : $folder->name;

            Log::info("Syncing account: " . $this->oauthAccount->id . " and folder: " . $folderName);

            $folderData = ImapDataParser::parseFolderData($folder);

            if (!in_array($folderName, $this->skipFolders)) {
                
                $this->esEmailFolderModel->add($this->oauthAccount, $folderData);

                // Dispatch the jobs with the current delay
                SyncFolderMessages::dispatch($this->oauthAccount, $folderName)->delay(now()->addSeconds($currentDelay));
                IdleEmailFolder::dispatch($this->oauthAccount, $folderName)->onQueue('imap_idle')->delay(now()->addSeconds($currentDelay));

                // Increment the delay for the next job
                $currentDelay += $this->delayIncrement;
            }

            // Recursively process child folders, if any
            if ($folder->hasChildren()) {
                $this->syncFoldersRecursively($folder->getChildren(), $folderName, $currentDelay);
            }
        }
    }
}
