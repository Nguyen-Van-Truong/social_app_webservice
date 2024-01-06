<?php
require __DIR__ . '/../../vendor/autoload.php';

use WebSocket\Client;

$wsClient = new Client("ws://192.168.1.4:8080");

try {
    // Sample message
    $testMessage = json_encode([
        "senderId" => 1,
        "receiverId" => 2,
        "message" => "Hello from PHP WebSocket client!"
    ]);

    // Send the message
    $wsClient->send($testMessage);

    // Receive and output the response from the server
    echo "Response from server: " . $wsClient->receive() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    // Close the connection
    $wsClient->close();
}

?>
