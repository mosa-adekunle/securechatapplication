<?php
// Set the host and port to listen on
$host = '127.0.0.1'; // Localhost (or use a public IP)
$port = 12345; // Port to listen on

// Create a TCP socket
$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($server === false) {
    echo "Error creating socket: " . socket_strerror(socket_last_error()) . "\n";
    exit;
}

// Bind the socket to the specified host and port
if (socket_bind($server, $host, $port) === false) {
    echo "Error binding socket: " . socket_strerror(socket_last_error($server)) . "\n";
    exit;
}

// Start listening for incoming connections
if (socket_listen($server, 5) === false) {
    echo "Error listening on socket: " . socket_strerror(socket_last_error($server)) . "\n";
    exit;
}

echo "Server listening on $host:$port...\n";

// Accept incoming client connections
while (true) {
    // Accept a client connection
    $client = socket_accept($server);
    if ($client === false) {
        echo "Error accepting connection: " . socket_strerror(socket_last_error($server)) . "\n";
        continue;
    }

    // Read data sent by the client
    $client_message = socket_read($client, 1024);
    echo "Received message: $client_message\n";

    // Send a response to the client
    $response = "Message received!";
    socket_write($client, $response, strlen($response));

    // Close the client connection
    socket_close($client);
}

// Close the server socket
socket_close($server);
