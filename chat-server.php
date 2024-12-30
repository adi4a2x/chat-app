<?php
require __DIR__  . '/vendor/autoload.php';
require "db_connection.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

// Store clients connected
$clients = new SplObjectStorage();

// Create a class implementing the WebSocket server interface
$chat = new class implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
        global $clients;
        $clients->attach($conn); // Add the client to the clients list
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        global $clients, $conn;
        $messageData = json_decode($msg, true);

        if (isset($messageData['sender']) && isset($messageData['text'])) {
            $sender = $messageData['sender'];
            $text = $messageData['text'];

            // Store the message in the database
            $stmt = $conn->prepare("INSERT INTO messages (sender, text) VALUES (?, ?)");
            $stmt->bind_param("ss", $sender, $text);
            $stmt->execute();
            $stmt->close();

            // Send the message to all other clients except the sender
            foreach ($clients as $client) {
                if ($client !== $from) {
                    $client->send($msg);
                }
            }
        } else {
            echo "Invalid message format received\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        global $clients;
        $clients->detach($conn); // Remove the client from the clients list
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close(); // Close connection on error
    }
};

// Start the WebSocket server on port 8080
$server = IoServer::factory(
    new HttpServer(
        new WsServer($chat)
    ),
    8080
);

echo "Server started on ws://localhost:8080\n";
$server->run();
