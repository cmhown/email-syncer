<?php 


if (!function_exists('formatFolderId')) {
    function formatFolderId($oauthId, $folderNmae) {
        return $oauthId . '-' . $folderNmae;
    }
}


if (!function_exists('formatMessageId')) {
    function formatMessageId($oauthId, $folderNmae, $messageUid) {
        return $oauthId . '-' . $folderNmae . '-' . $messageUid;
    }
}

if(!function_exists('esGetHits')) {
    function esGetHits($results) {
        $data = array_map(function ($hit) {
            return $hit['_source'];
        }, $results['hits']['hits']);
        return $data;
    }
}