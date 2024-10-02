<?php 

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class EmailUpdateService
{
    // Store email updates in Redis (or any other caching system)
    public static function sendEmailUpdate($accountId, $provider, $folderName)
    {
        $folderId = formatFolderId($accountId, $folderName);
        Redis::lpush("email_updates_{$accountId}-{$provider}", $folderId);
        Redis::expire("email_updates_{$accountId}", 10); // Keep updates for 10 seconds
        return "email_updates_{$accountId}-{$provider}";
    }

    public static function getEmailUpdatesForAccount($accountId, $provider)
    {
        // Retrieve all pending email updates for the account
        $folder_id = Redis::lrange("email_updates_{$accountId}-{$provider}", 0, -1);

        // Clear updates once retrieved
        // Redis::del("email_updates_{$accountId}-{$provider}");

        return $folder_id;
    }
}
