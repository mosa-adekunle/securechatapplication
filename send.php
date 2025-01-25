<?php include "chat-controller.php"; ?>
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
        <a href="receive.php">
            <button class="btn btn-success btn-sm">
                Receive Message
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
            <div class="col">
                <h3>
                    Message Sending Session for <?php echo $username; ?>
                </h3>
            </div>
        </div>


        <div class="chat-box">

            <form method="post" action="encrypt.php">

                <div class="row mt-2 mb-2">
                    <div class="col-3 col-md-2">
                        <label for="plaintext">Plain Text:</label>
                    </div>
                    <div class="col-9 col-md-10">
                        <textarea rows="6" name="plaintext"
                                  class="message"><?php echo $_SESSION["plaintext"]; ?></textarea>
                    </div>
                </div>
                <div class="row mt-2 mb-2">
                    <div class="col-3 col-md-2">
                        <label for="key">Key:</label>
                    </div>
                    <div class="col-9 col-md-10">
                        <input type="text" name="key" class="key form-control" value="<?php echo $_SESSION["key"]; ?>"/>
                    </div>
                </div>

                <div class="row mb-2 mt-1">
                    <div class="col-3 col-md-2">
                        <label for="technique">Technique:</label>
                    </div>
                    <div class="col-9 col-md-10">
                        <select name="technique" class="form-control">
                            <option value="caesar" <?php echo ($_SESSION["technique"] == 'caesar') ? 'selected' : ''; ?>>
                                Caesar cipher
                            </option>
                            <option value="monoalphabetic" <?php echo ($_SESSION["technique"] == 'monoalphabetic') ? 'selected' : ''; ?>>
                                Monoalphabetic
                            </option>
                            <option value="polyalphabetic" <?php echo ($_SESSION["technique"] == 'polyalphabetic') ? 'selected' : ''; ?>>
                                Polyalphabetic
                            </option>
                            <option value="hill" <?php echo ($_SESSION["technique"] == 'hill') ? 'selected' : ''; ?>>
                                Hill cipher
                            </option>
                            <option value="playfair" <?php echo ($_SESSION["technique"] == 'playfair') ? 'selected' : ''; ?>>
                                Playfair
                            </option>
                            <option value="otp" <?php echo ($_SESSION["technique"] == 'otp') ? 'selected' : ''; ?>>OTP
                                (One-Time Pad)
                            </option>
                            <option value="rail-fence" <?php echo ($_SESSION["technique"] == 'rail-fence') ? 'selected' : ''; ?>>
                                Rail fence
                            </option>
                            <option value="columnar" <?php echo ($_SESSION["technique"] == 'columnar') ? 'selected' : ''; ?>>
                                Columnar
                            </option>
                            <option value="des" <?php echo ($_SESSION["technique"] == 'des') ? 'selected' : ''; ?>>DES
                                (Data Encryption Standard)
                            </option>
                            <option value="aes" <?php echo ($_SESSION["technique"] == 'aes') ? 'selected' : ''; ?>>AES
                                (Advanced Encryption Standard)
                            </option>
                            <option value="rc4" <?php echo ($_SESSION["technique"] == 'rc4') ? 'selected' : ''; ?>>RC4
                            </option>
                            <option value="rsa" <?php echo ($_SESSION["technique"] == 'rsa') ? 'selected' : ''; ?>>RSA
                            </option>
                            <option value="ecc" <?php echo ($_SESSION["technique"] == 'ecc') ? 'selected' : ''; ?>>ECC
                                (Elliptic Curve Cryptography)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row text-end mb-2">
                    <div class="col">
                        <input type="hidden" name="action" value="encrypt"/>
                        <button class="btn btn-light">
                            Encrypt
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
            if (isset($_SESSION["encrypted_message"]) && $_SESSION["encrypted_message"] != "") {

                ?>

                <div class="row mt-2 mb-2">
                    <div class="col">
                        <label for="encrypted-message">Encrypted Message:</label>
                    </div>
                </div>

                <form method="post" action="send-message.php">

                    <div class="row mt-2 mb-2">
                        <div class="col">

                            <textarea rows="6" readonly name="encrypted-message" id="encrypted-message"
                                      class="form-control encrypted-message"><?php echo $_SESSION["encrypted_message"]; ?></textarea>

                            <div class="encrypted-using text-end">
                                <small>
                                    Encrypted using <?php echo $_SESSION["technique"]; ?>
                                </small>
                            </div>

                        </div>
                    </div>

                    <div class="row text-end">
                        <div class="col">
                            <button class="btn btn-light" id="send-message">
                                Send
                            </button>
                        </div>
                    </div>
                </form>
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
                // socket.send(connection_message);
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
            socket.onopen = function () {
                sessionStorage.setItem("socket", JSON.stringify(socket));
            }
        }

        // Close the WebSocket connection when leaving the page or when explicitly needed
        window.onbeforeunload = function () {
            if (socket) {
                socket.close();
            }
            sessionStorage.removeItem("socket");
        };


        function sendMessage(message) {
            if (socket.readyState === WebSocket.OPEN) {
                socket.send(message);
                console.log(`Message sent: ${message}`);
            } else {
                console.warn('WebSocket is not open. Cannot send message.');
            }
        }

        $("#send-message").click(function () {
            sendMessage($("#encrypted-message").html());
            return false;
        });

    </script>
</body>
</html>
