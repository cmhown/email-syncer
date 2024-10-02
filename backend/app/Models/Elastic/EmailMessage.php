<?php

namespace App\Models\Elastic;

use App\Models\OauthAccount;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailMessage extends ElasticModel
{

    public $index = 'email_messages';

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


     /**
     * Get message IDs for a specific folder using Elasticsearch scroll.
     *
     * @param string $folderId
     * @return array
     */
    public function getFolderMessageIds(string $folderId): array
    {
        $messageIds = [];
        $params = [
            'index' => $this->index,
            'scroll' => '2m', // Keep the scroll context alive for 2 minutes
            'size' => 100, // Number of documents to retrieve per batch
            'body' => [
                '_source' => ['id'], // Only retrieve the id field
                'query' => [
                    'term' => [
                        'folder_id' => $folderId
                    ]
                ]
            ]
        ];

        // Initial search request
        try {
            $response = $this->esClient->search($params);
            // Keep adding results until there are no more
            while (true) {
                // Add the current batch of message IDs to the array
                foreach ($response['hits']['hits'] as $hit) {
                    $messageIds[] = $hit['_source']['id']; // Get only id
                }

                // Check if there are more results
                if (count($response['hits']['hits']) === 0) {
                    break; // Exit if no more messages
                }

                // Prepare the next scroll request
                $scrollId = $response['_scroll_id'];
                $response = $this->esClient->scroll([
                    'scroll_id' => $scrollId,
                    'scroll' => '2m' // Keep the scroll context alive
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error fetching messages for folder {$folderId}: " . $e->getMessage());
        }

        return $messageIds; // Return only message IDs
    }

    /**
     * Delete specific messages from a folder.
     *
     * @param array $messageIds
     * @param string $folderId
     */
    public function deleteMessages(array $messageIds, string $folderId): void
    {
        if (empty($messageIds)) {
            return;
        }

        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    'id' => $messageIds
                                ]
                            ],
                            [
                                'term' => [
                                    'folder_id' => $folderId
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $this->esClient->deleteByQuery($params);
            Log::info("Deleted messages for folder {$folderId} with IDs: " . implode(', ', $messageIds));
        } catch (Exception $e) {
            Log::error("Error deleting messages from folder {$folderId}: " . $e->getMessage());
        }
    }
}
