<?php include "chat-receiver-controller.php"; ?>
<html>
<head>
    <?php include "includes/headers.php"; ?>
</head>
<body>

<?php include "includes/app-title.php"; ?>

<div class="container-fluid">


    <div class="row">
        <div class="col-10 text-end">
            <a href="logout.php">
                <btn class="btn btn-sm btn-dark">
                    Logout
                </btn>
            </a>
        </div>
    </div>

    <div class="row mt-2 nav-btns">
        <div class="col col-sm-8 offset-sm-2 text-end">
            <a href="send.php">
                <button class="btn btn-success btn-sm">
                    Send Message
                </button>
            </a>

            <a href="receive.php">
                <button class="btn btn-danger btn-sm">
                    Key Exchange
                </button>
            </a>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3">


            <div class="row">
                <div class="col-10">
                    <h3>
                        Message Receiving Session for <?php echo $username; ?>
                    </h3>
                </div>
            </div>


            <div class="chat-box">

                <form method="post" action="encrypt.php">

                    <div class="row mt-2 mb-2">
                        <div class="col-2">
                            <label for="ciphertext">CipherText:</label>
                        </div>
                    </div>
                    <div class="row mt-2 mb-2">
                        <div class="col">
                            <textarea rows="6" readonly name="ciphertext" class="form-control encrypted-message"></textarea>
                        </div>
                    </div>
                    <div class="row mt-2 mb-2">
                        <div class="col-3 col-md-2">
                            <label for="key">Key:</label>
                        </div>
                        <div class="col-9 col-md-10">
                            <input type="text" name="key" class="key form-control"/>
                        </div>
                    </div>

                    <div class="row mb-2 mt-1">
                        <div class="col-3 col-md-2">
                            <label for="technique">Technique:</label>
                        </div>
                        <div class="col-9 col-md-10">
                            <select name="technique" type="text" class="form-control">
                                <option value="caesar">Caesar cipher</option>
                                <option value="monoalphabetic">Monoalphabetic</option>
                                <option value="polyalphabetic">Polyalphabetic</option>
                                <option value="hill">Hill cipher</option>
                                <option value="playfair">Playfair</option>
                                <option value="otp">OTP (One-Time Pad)</option>
                                <option value="rail-fence">Rail fence</option>
                                <option value="columnar">Columnar</option>
                                <option value="des">DES (Data Encryption Standard)</option>
                                <option value="aes">AES (Advanced Encryption Standard)</option>
                                <option value="rc4">RC4</option>
                                <option value="rsa">RSA</option>
                                <option value="ecc">ECC (Elliptic Curve Cryptography)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row text-end mb-2">
                        <div class="col">
                            <input type="hidden" name="action" value="encrypt"/>
                            <button class="btn btn-light">
                                Decrypt
                            </button>
                            <br>
                            <small>
                                <a href="clear-session.php">
                                    Clear
                                </a>
                            </small>
                        </div>
                    </div>
                </form>

                <?php

                if (isset($_SESSION["decrypted_message"]) && $_SESSION["decrypted_message"] != "") { ?>

                    <div class="row mt-2 mb-2">
                        <div class="col">
                            <label for="encrypted-message">Plaintext:</label>
                        </div>
                    </div>

                    <div class="row mt-2 mb-2">
                        <div class="col">
                            <textarea readonly name="plaintext"
                                      class="form-control plaintext"><?php echo $_SESSION["decrypted_message"]; ?></textarea>
                        </div>
                    </div>

                <?php } ?>
            </div>

            <?php include "includes/affiliation.php"; ?>
        </div>


    </div>
        <script>

            // Check if a WebSocket connection already exists
            let socket = null;

            if (sessionStorage.getItem("socket")) {
                socket = JSON.parse(sessionStorage.getItem("socket"));
            } else {
                // Create a new WebSocket connection if one doesn't exist
                socket = new WebSocket('ws://127.0.0.1:8080');
                socket.onopen = () => {
                    let connection_message = "WebSocket connection established from <?=$username;?>.";
                    console.log(connection_message);
                    socket.send(connection_message);
                };

                socket.onmessage = (event) => {
                    console.log(`Message received from server: ${event.data}`);
                    $(".encrypted-message").html(event.data);
                };

                socket.onclose = (event) => {
                    console.log(`WebSocket connection closed: ${event.code} - ${event.reason}`);
                };

                socket.onerror = (error) => {
                    console.error('WebSocket error:', error);
                };

                // Store the WebSocket object in sessionStorage to persist it across page reloads
                socket.onopen = function() {
                    sessionStorage.setItem("socket", JSON.stringify(socket));
                }
            }

            // Close the WebSocket connection when leaving the page or when explicitly needed
            window.onbeforeunload = function() {
                if (socket) {
                    socket.close();
                }
                sessionStorage.removeItem("socket");
            };

        </script>


</body>
</html>
