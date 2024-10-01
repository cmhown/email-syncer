<?php

namespace App\Console\Commands;

use App\Jobs\SyncEmailFolders;
use App\Models\OauthAccount;
use Webklex\IMAP\Commands\ImapIdleCommand;

class oauthImapIdleCommand extends ImapIdleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:oauth-email-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all linked oauth accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oauthAccounts= OauthAccount::all();
        
        foreach($oauthAccounts as $oauthAccount) {
            SyncEmailFolders::dispatch($oauthAccount);
        }
    }

}
