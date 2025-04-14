<?php
//$host = '127.0.0.1';
$host = '0.0.0.0';

$port = 8080;

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($server === false) {
    die("Error creating socket: " . socket_strerror(socket_last_error()) . "\n");
}

if (socket_bind($server, $host, $port) === false) {
    die("Error binding socket: " . socket_strerror(socket_last_error()) . "\n");
}

if (socket_listen($server, 5) === false) {
    die("Error listening on socket: " . socket_strerror(socket_last_error()) . "\n");
}

echo "WebSocket server started at ws://$host:$port\n";

$clients = [];  // Array to store connected clients
$handshakes = [];  // Array to track completed handshakes

while (true) {
    // Prepare a list of sockets to check for activity
    $read = $clients;
    $read[] = $server;

    // Monitor sockets for activity
    if (socket_select($read, $write, $except, 0) > 0) {
        // Check for new connection requests
        if (in_array($server, $read)) {
            $newClient = socket_accept($server);
            $clients[] = $newClient; // Add the new client to the clients list
            echo "New client connected.\n";
        }

        // Check each client for activity
        foreach ($clients as $key => $client) {
            if (in_array($client, $read)) {
                $data = @socket_read($client, 2048, PHP_BINARY_READ);
                if ($data === false) {
                    // Handle client disconnection
                    unset($clients[$key]);  // Remove client from the list
                    socket_close($client);   // Close the socket connection
                    echo "Client disconnected.\n";
                    continue;
                }

                // Handle WebSocket handshake if not already completed
                if (!isset($handshakes[$key])) {
                    performHandshake($data, $client);
                    $handshakes[$key] = true; // Mark handshake as completed
                    continue;
                }

                if (strlen($data) < 2) {
                    continue; // Received data is too short. Skip further processing.
                }

                // Decode WebSocket message
                $decodedMessage = decodeWebSocketMessage($data);
//                echo "Received message: $decodedMessage\n";
                echo "$decodedMessage\n";

                // Broadcast the message to all connected clients
                foreach ($clients as $otherClient) {
                    if ($otherClient !== $client) {
                        sendWebSocketMessage($otherClient, $decodedMessage);
                    }
                }
            }
        }
    }
}

function performHandshake($request, $client)
{
    $headers = [];
    $lines = preg_split("/\r\n/", $request);
    foreach ($lines as $line) {
        if (strpos($line, ": ") !== false) {
            list($key, $value) = explode(": ", $line, 2);
            $headers[$key] = trim($value);
        } elseif (preg_match("/GET (.*) HTTP/", $line, $matches)) {
            $headers['GET'] = $matches[1];
        }
    }

    if (!isset($headers['Sec-WebSocket-Key'])) {
        echo "WebSocket key missing.\n";
        return;
    }

    $secWebSocketKey = $headers['Sec-WebSocket-Key'];
    $secWebSocketAccept = base64_encode(pack('H*', sha1($secWebSocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

    $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: $secWebSocketAccept\r\n\r\n";

    socket_write($client, $handshakeResponse, strlen($handshakeResponse));
}

function decodeWebSocketMessage($data)
{
    $bytes = ord($data[1]) & 127;

    if ($bytes === 126) {
        $maskStart = 4;
        $dataStart = 8;
    } elseif ($bytes === 127) {
        $maskStart = 10;
        $dataStart = 14;
    } else {
        $maskStart = 2;
        $dataStart = 6;
    }

    $masks = substr($data, $maskStart, 4);
    $payload = substr($data, $dataStart);

    $decoded = '';
    for ($i = 0; $i < strlen($payload); $i++) {
        $decoded .= $payload[$i] ^ $masks[$i % 4];
    }

    return $decoded;
}

// Function to send a WebSocket message
function sendWebSocketMessage($client, $message)
{
    $length = strlen($message);
    $frame = chr(0x81); // Text frame with FIN set
    if ($length <= 125) {
        $frame .= chr($length);
    } elseif ($length >= 126 && $length <= 65535) {
        $frame .= chr(126) . pack('n', $length); // 2-byte length
    } else {
        $frame .= chr(127) . pack('J', $length); // 8-byte length
    }

    $frame .= $message;
    socket_write($client, $frame, strlen($frame));
}
