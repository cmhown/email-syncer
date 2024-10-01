<?php

namespace App\Models\Elastic;

use App\Models\OauthAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailMessage extends ElasticModel
{

    public $index = 'email_messages';

    // public function getByAccount($oauthId, $from = 0, $size = 10)
    // {
    //     $params = [
    //         'index' => $this->index,
    //         'body' => [
    //             'from' => $from,
    //             'size' => $size,
    //             'query' => [
    //                 'term' => ['oauth_id' => $oauthId]
    //             ]
    //         ]
    //     ];

    //     $results = $this->esClient->search($params);

    //     // Get the hits data
    //     $emails = esGetHits($results);

    //     return [
    //         'emails' => $emails,
    //         'total' => $results['hits']['total']['value'],
    //     ];
    // }

    public function getByFolder($oauthId, $folderId, $from = 0, $size = 10)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'from' => $from,
                'size' => $size,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['oauth_id' => $oauthId]],
                            ['term' => ['folder_id' => $folderId]]
                        ]
                    ]
                ],
                'sort' => [
                    ['date' => ['order' => 'desc']]
                ]
            ]
        ];

        $results = $this->esClient->search($params);

        // Get the hits data
        $emails = esGetHits($results);

        return [
            'emails' => $emails,
            'total' => $results['hits']['total']['value'],
        ];
    }

    public function add(OauthAccount $oauthAccount, array $message, string $folderName)
    {

        $messageData = [
            'user_id' => $oauthAccount->user_id,
            'oauth_id' => $oauthAccount->id,
            'provider' => $oauthAccount->provider,
            'folder_id' => formatFolderId($oauthAccount->id, $folderName),
            'id' => formatMessageId($oauthAccount->id, $folderName, $message['uid']),
            ...$message
        ];

        try {
            $this->indexDocument($messageData['id'], $messageData);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error(json_encode($messageData));
        }
    }

    public function deleteFolderMessages(string $folderId)
    {
        $this->deleteByParams(['folder_id' => $folderId]);
    }
}
