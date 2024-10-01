<?php

namespace App\Models\Elastic;

use Elastic\Client\ClientBuilderInterface;
use Illuminate\Support\Facades\Log;


class ElasticModel 
{
    public $esClient;
    public $index;

    public function __construct()
    {
        $this->esClient = app(ClientBuilderInterface::class)->default();
    }

    public function indexDocument(String $id, array $body)
    {
        $this->esClient->index(
            [
                'index' => $this->index,
                'id' => $id,
                'body' => $body
            ]
        );
    }

    public function delete(String $id) {
        $this->esClient->delete($id);
    }

    public function deleteByParams($params)
    {
        $params = [
            'index' => $this->index,
            'body'  => [
                'query' => [
                    'match' => $params,
                ],
            ],
        ];

        try {
            $response = $this->esClient->deleteByQuery($params);
            return $response;
        } catch (\Exception $e) {
            Log::error("Elasticsearch delete by query failed: " . $e->getMessage());
        }
    }
    
}
