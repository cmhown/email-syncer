<?php

namespace App\Services;

use App\Facades\ImapDataParser;
use Carbon\Carbon;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Connection\Protocols\Response;
use Webklex\PHPIMAP\Exceptions\NotSupportedCapabilityException;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\IMAP;

class CustomImapIdleService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Enhanced idle method to handle new messages, flag changes, deletions, etc.
     */
    public function folderIdle(Folder $folder, callable $callback, int $timeout = 300): void
    {
        $this->client->setTimeout($timeout);

        if (!in_array("IDLE", $this->client->getConnection()->getCapabilities()->validatedData())) {
            throw new NotSupportedCapabilityException("IMAP server does not support IDLE");
        }

        $idle_client = $this->client->clone();
        $idle_client->connect();
        $idle_client->openFolder($folder->path, true);
        $idle_client->getConnection()->idle();

        $last_action = Carbon::now()->addSeconds($timeout);

        while (true) {
            $line = $idle_client->getConnection()->nextLine(Response::empty());

            // Check for new messages
            if ((strpos($line, "EXISTS")) !== false) {
                $this->handleNewMessage($folder, $line, $callback);
            }

            // Check for flag changes
            if (strpos($line, "FETCH") !== false && strpos($line, "FLAGS") !== false) {
                $this->handleFlagChange($folder, $line, $callback);
            }

            // Check for deleted messages
            if (strpos($line, "EXPUNGE") !== false) {
                $this->handleDeletedMessage($folder, $line, $callback);
            }

            // TODO: add further checks

            // Ensure connection is still alive
            if (!$this->client->isConnected() || $last_action->isBefore(Carbon::now())) {
                $this->client->getConnection()->reset();
                $this->client->connect();
            }

            $last_action = Carbon::now()->addSeconds($timeout);
        }
    }

    /**
     * Handle new messages.
     */
    protected function handleNewMessage(Folder $folder, string $line, callable $callback)
    {
        preg_match('/\* (\d+) EXISTS/', $line, $matches);
        $msgn = $matches[1];

        $this->client->openFolder($folder->path, true);

        $sequence = ClientManager::get('options.sequence', IMAP::ST_MSGN);
        $message = $folder->query()->getMessageByMsgn($msgn);
        $message->setSequence($sequence);

        $messageData = ImapDataParser::parseMessageData($message);

        $callback('message_new', ['message' => $messageData]);

    }

    /**
     * Handle flag changes (e.g., \Seen, \Answered, etc.).
     */
    protected function handleFlagChange(Folder $folder, string $line, callable $callback)
    {
        preg_match('/\* (\d+) FETCH \(FLAGS \((.*?)\)\)/', $line, $matches);
        $msgn = $matches[1];
        $flags = str_replace('\\', '', $matches[2]);

        $this->client->openFolder($folder->path, true);

        $sequence = ClientManager::get('options.sequence', IMAP::ST_MSGN);
        $message = $folder->query()->getMessageByMsgn($msgn);
        $message->setSequence($sequence);

        $messageData = ImapDataParser::parseMessageData($message);

        $callback('flag_new', ['message' => $messageData, 'flags' => explode(' ', $flags)]);

    }

    /**
     * Handle message deletions.
     */
    protected function handleDeletedMessage(Folder $folder, string $line, callable $callback)
    {
        preg_match('/\* (\d+) EXPUNGE/', $line, $matches);
        $msgn = $matches[1];

        // Dispatch custom callback for the deleted message
        $callback('message_deleted', ["messageUid" => $msgn]);

    }
}
