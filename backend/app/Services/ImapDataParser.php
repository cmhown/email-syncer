<?php

namespace App\Services;

use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;

class ImapDataParser
{

    public function parseFolderData(Folder $folder)
    {
        return [
            'path' => $folder->path,
            'name' => $folder->name,
            'full_name' => $folder->full_name,
        ];
    }

    public function parseMessageData(Message $message)
    {
        return [
            'uid' => $message->getUid(),
            'message_id' => $message->getMessageId()->toString(),
            'subject' => $message->getSubject()->toString(),
            'from' => $message->getFrom()->toArray(),
            'to' => $message->getTo()->toArray(),
            'date' => $message->getDate()->toDate()->format('Y-m-d H:i:s'),
            // 'message_body' => $message->getHTMLBody(),
            'flags' => array_values($message->getFlags()->toArray()),
        ];
    }
}
