<?php

namespace App\Models\Elastic;

use App\Models\OauthAccount;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailFolder extends ElasticModel
{

    public $index = 'email_folders';

    public function getByOauthId($authId, $from = 0, $size = 10)
    {

        $params = [
            'index' => $this->index,
            'body' => [
                'from' => $from,
                'size' => $size,
                'query' => [
                    'term' => [
                        'oauth_id' => $authId
                    ]
                ],
                'sort' => [
                    ['date' => ['order' => 'desc']]
                ]
            ]
        ];

        $results = $this->esClient->search($params);

        // Get the hits data
        $response = esGetHits($results);

        return $response;
    }

    public function add(OauthAccount $oauthAccount, array $folder)
    {

        $folderData = [
            'user_id' => $oauthAccount->user_id,
            'oauth_id' => $oauthAccount->id,
            'provider' => $oauthAccount->provider,
            'id' => formatFolderId($oauthAccount->id, $folder['full_name']),
            ...$folder
        ];

        try {
            $this->indexDocument($folderData['id'], $folderData);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error(json_encode($folder));
        }
    }

    public function deleteAcountFolders(int $oauthAccountId)
    {
        $this->deleteByParams(['oauth_id' => $oauthAccountId]);
    }
}
