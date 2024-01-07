<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../lib/DatabaseConnection.php';

class Chat implements MessageComponentInterface
{
    protected $clients;
    private $db;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $dbConnection = new DatabaseConnection();
        $this->db = $dbConnection->connect();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        // Display a message in the server's console
        echo "New connection established! (Resource ID: {$conn->resourceId})\n";

        // Optional: Log the client's IP address
        if ($conn->remoteAddress) {
            echo "Client IP: {$conn->remoteAddress}\n";
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Decode the JSON message
        $data = json_decode($msg, true);
        if (!$data) {
            echo "Invalid message format\n";
            return;
        }

        // Extract message details
        $senderId = $data['senderId'];
        $chatType = $data['chatType']; // 'private' or 'group'
        $groupId = $data['groupId'] ?? null; // Group ID, if it's a group chat

        // Create a notification message
        $notification = json_encode([
            'type' => 'new_message',
            'chatType' => $chatType,
            'groupId' => $groupId,
            'from' => $senderId
        ]);

        // Broadcast the notification to all connected clients
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($notification);
            }
        }
    }


    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

?>
