<?php


if (!function_exists('createSlug')) {
    function createSlug($text)
    {
        // Convert string to a slug (lowercase, replace spaces and special characters with hyphens)
        return preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($text));
    }
}

if (!function_exists('formatFolderId')) {
    function formatFolderId($oauthId, $folderName)
    {
        $folderName = createSlug($folderName);

        return $oauthId . '-' . $folderName;
    }
}


if (!function_exists('formatMessageId')) {
    function formatMessageId($oauthId, $folderName, $messageUid) {
        $folderName = createSlug($folderName);
        return $oauthId . '-' . $folderName . '-' . $messageUid;
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