<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Elastic\EmailMessage;
use App\Models\Elastic\EmailFolder;
use Illuminate\Http\Request;

class EmailController extends Controller
{

    protected $elasticsearch;

    public function __construct()
    {

    }

    /** 
     * Fetch folders from Elasticsearch 
    */ 
    public function getFolders($provider, Request $request)
    {
        $oauthAccount = $request->user()->oauthAccountByProvider($provider)->first();

        if(!$oauthAccount) {
            return response()->json(['message' => 'Provider not authorized yet'], 404);
        } 

        $esEmailFolderModel = new EmailFolder();

        return response()->json([
            'folders' => $esEmailFolderModel->getByOauthId($oauthAccount->id, 0, 100)
        ]);
    }

    /** 
     * Fetch emails from a selected folder
    */
    public function getEmailsByFolder($provider, $folderId, Request $request)
    {
        $oauthAccount = $request->user()->oauthAccountByProvider($provider)->first();

        if(!$oauthAccount) {
            return response()->json(['message' => 'Provider not authorized yet'], 404);
        } 

        $page = (int) $request->query('page', 1);
        $size = 10;
        $from = ($page - 1) * $size;

        $esEmailMessagesModel = new EmailMessage();

        $emails = $esEmailMessagesModel->getByFolder($oauthAccount->id, $folderId, $from, $size);

        return response()->json([
            ...$emails,
            'current_page' => $page,
            'per_page' => $size,
        ]);
        
    }

}
