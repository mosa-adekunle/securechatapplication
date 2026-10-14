# Secure Chat Application

A PHP-based **educational cryptography and real-time messaging prototype**. Users can encrypt text with a selected technique, transmit the ciphertext over a WebSocket connection, and manually decrypt it in another browser session.

> **Security notice:** This repository demonstrates cryptographic concepts. It is **not suitable for confidential communication or production deployment**. Several algorithms are obsolete or intentionally simplified, and authentication, key management, transport security, and message handling are not production-grade.

## Features

- Sender and receiver interfaces built with PHP, Bootstrap 5.3.3, and JavaScript/jQuery.
- Username-based PHP sessions for demonstration (not verified user authentication).
- Text encryption, display of ciphertext, transmission, reception, and decryption.
- A standalone PHP WebSocket server that broadcasts messages to other connected clients.
- Demonstration RSA key generation and small-parameter Diffie-Hellman exchange.
- Session clearing and logout controls.

### Available techniques

| Category | Options in the interface |
| --- | --- |
| Classical substitution | Caesar, monoalphabetic, polyalphabetic (Vigenere), Playfair |
| Classical/transposition | Hill, rail fence, columnar |
| Symmetric examples | One-Time Pad (OTP), DES, AES, RC4 |
| Asymmetric examples | RSA, ECC |
| Integrity demonstration | SHA-1 hash and verification |

**SHA-1 is hashing, not encryption.** The SHA option outputs the original message alongside its digest, and the receiver verifies the digest. The repository also includes a separate demonstration of Diffie-Hellman exchanging RSA public-key information. These examples do not provide modern authenticated end-to-end encryption.

## How the application behaves

1. Open `index.php` and enter a username. The application stores it in the PHP session and redirects to `send.php`.
2. In **Send Message**, enter plaintext, choose a technique, and provide a key when required. Click **Encrypt**.
3. `encrypt.php` calls the matching implementation in `techniques/`, stores the result in the PHP session, and returns to `send.php` to show the ciphertext.
4. Click **Send**. A JavaScript handler transmits the ciphertext over a WebSocket if the socket is open.
5. In another independent browser session, open **Receive Message** (`receive.php`) before sending. It listens for ciphertext, saves received text in that session, and refreshes the receiver view.
6. Choose the matching technique, provide the appropriate decryption key, and click **Decrypt**. `decrypt.php` calls the algorithm and displays its output.

The WebSocket server **broadcasts to other connected clients**, not to a specific named recipient. Messages are not persisted in a database and cannot be fetched by recipients who were offline. Successful round trips depend on matching techniques and keys; some algorithms have implementation-specific limitations.

## Requirements

- PHP (PHP 8.x recommended; compatibility has not been comprehensively tested).
- PHP **sockets** extension for `web-socket-server.php`.
- PHP **BCMath** extension for RSA's `bcpowmod()`.
- A PHP-capable local web server, such as Laragon/Apache, or PHP's built-in development server.
- A JavaScript-enabled browser with WebSocket support.
- Internet access for the jQuery dependency loaded from a CDN. Bootstrap is bundled under `resources/`.

Check PHP extensions with `php -m`.

## Quick start (local development)

### PHP built-in server

In the repository root, start the web interface:

```bash
php -S 127.0.0.1:8000 -t .
```

In a **second terminal**, start the WebSocket service:

```bash
php web-socket-server.php
```

**Important:** The checked-in `send.php` and `receive.php` currently contain a **hard-coded remote WebSocket URL**. For local development, replace the URL in `new WebSocket(...)` in **both files** with:

```javascript
new WebSocket('ws://127.0.0.1:8080')
```

Then open **http://127.0.0.1:8000/** in your browser. These examples use port 8000 for HTTP and port 8080 for WebSocket connections.

**Network warning:** `web-socket-server.php` currently binds to `0.0.0.0:8080`. This can make the service reachable from other machines. Run it only in an isolated development environment, or change its bind address to `127.0.0.1` for local-only testing.

### Laragon (Windows)

1. Clone this repository into the Laragon web root, usually `C:\laragon\www\securechatapplication`.
2. Start Laragon's web services.
3. Start `php web-socket-server.php` in a separate terminal with the sockets extension enabled.
4. Configure matching, reachable WebSocket URLs in both `send.php` and `receive.php`.
5. Open **http://localhost/securechatapplication/**.

### Suggested manual smoke test

1. Open two independent browser profiles or a regular and private window so they have different PHP sessions.
2. Log in with different demonstration usernames.
3. Open `receive.php` in one session and make sure its WebSocket is connected.
4. In the sender, enter `HELLO`, select **Caesar**, supply the key `3`, and click **Encrypt**.
5. Verify that ciphertext appears. Click **Send** and confirm that the receiver's ciphertext field updates.
6. At the receiver, select **Caesar**, enter `3`, click **Decrypt**, and check the output.

This is a **manual test procedure**, not a claim that the current project passed a live end-to-end test.

## Repository structure

| Path | Purpose |
| --- | --- |
| `index.php`, `login.php`, `logout.php` | Entry page and demonstration session management |
| `send.php`, `chat-controller.php` | Sender interface and WebSocket client |
| `receive.php`, `chat-receiver-controller.php` | Receiver interface and WebSocket client |
| `encrypt.php`, `decrypt.php` | Dispatch to the selected cryptographic implementation |
| `techniques/` | PHP implementations of the listed algorithms |
| `web-socket-server.php` | Prototype WebSocket broadcast server on port 8080 |
| `tcp-server.php` | Separate simple TCP demonstration on port 12345 |
| `generate-rsa-key-receiver.php`, `generate-ecc-key-receiver.php` | Demonstration receiver key generation |
| `save-session-variable.php`, `clear-session.php` | Session updates and clearing |
| `includes/` | Shared templates and session guard |
| `resources/` | CSS and bundled Bootstrap assets |

## Known limitations and security considerations

- **No real authentication.** Submitting a username sets the session identity. There is no password check or verified account system.
- **No private recipient routing.** The WebSocket server broadcasts to all other connected sockets. It has no access controls, user-based routing, or message history.
- **Unprotected WebSocket transport.** Clients use `ws://`, not TLS-protected `wss://`; the socket server does not authenticate peers. HTTPS sites generally require secure WebSockets.
- **Educational cryptography only.** The RSA example uses tiny primes; ECC uses very small parameters. The Diffie-Hellman demo uses `p = 23`, `Math.random()`, and single-byte XOR. These are insecure for genuine key exchange.
- **Legacy algorithms.** DES, RC4, SHA-1, and classical ciphers are inappropriate for protecting modern private communications. The custom AES implementation uses independent block encryption without authenticated encryption or a randomized IV/nonce.
- **Session and output safety.** Keys and plaintext appear in session/UI state. Some PHP templates echo session data without HTML escaping, and `save-session-variable.php` allows arbitrary session variable names to be set by POST.
- **Missing non-JavaScript fallback.** The sender form targets `send-message.php`, which is not present. JavaScript normally intercepts the Send button; without it, the form action fails.
- **Prototype networking.** The custom WebSocket server lacks full production protocol handling, authentication, reliable message routing, and robust error recovery.
- **Unverified algorithms.** Not all techniques and key-generation flows have been tested end-to-end. Some files emit sample keys or debugging output and may require corrections.
- **Unimplemented brief items.** The original course specification mentioned DSA and image/video encryption, but neither is exposed as a working feature in the current UI.

Do not expose this application publicly or use it for real secrets. A production system would require proven cryptographic libraries, authenticated encryption such as AES-GCM or ChaCha20-Poly1305, authenticated key exchange, verified identity, TLS/WSS, output escaping, CSRF protection, input validation, and a maintained WebSocket implementation.

## Troubleshooting

- **No message arrives:** confirm the receiver is already connected, both web pages use the same WebSocket endpoint, and `web-socket-server.php` is running.
- **Port already in use:** run HTTP and WebSocket servers on different ports, for example 8000 and 8080.
- **RSA errors:** check that BCMath is enabled.
- **Socket errors:** enable PHP sockets and verify port 8080 is available.
- **Session problems:** check PHP session configuration and web server/PHP logs.
- **Decryption mismatch:** confirm the selected technique, key format, and sample message are supported by the chosen implementation.

## Background

Originally prepared for **ISSM533A: Cryptology and Secure Network Communications (Winter 2025)**. This README describes the existing repository rather than presenting every technique in the original coursework brief as complete.

## Project status

**Educational prototype / work in progress.** Documentation is based on source-code inspection, not verified automated tests or an executed two-user browser session.
