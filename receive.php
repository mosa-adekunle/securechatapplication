<?php include "chat-receiver-controller.php"; ?>
<html>
<head>
    <?php include "includes/headers.php"; ?>
</head>
<body>

<style>
    #encrypted-key{
        color: #000;
    }
</style>

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
                <div class="col">
                    <h2>
                        Welcome <?php echo $username; ?>
                    </h2>
                </div>
            </div>


<!--            <div class="row">-->
<!--                <div class="col text-end">-->
<!--                    <a href="generate-rsa-key-receiver.php" style="text-decoration: none;">-->
<!--                        <button class="btn btn-danger btn-sm">Generate RSA Key</button>-->
<!--                    </a>-->
<!--                    --><?php //if (isset($_SESSION["receiver_public_key"]["e"]) && isset($_SESSION["receiver_public_key"]["n"])): ?>
<!--                        <button id="send-rsa-key-btn" class="btn btn-primary btn-sm">Send RSA Key</button>-->
<!--                        <br/>-->
<!--                        <small>RSA Public Key: e=--><?php //= $_SESSION["receiver_public_key"]["e"] ?><!--, n=--><?php //= $_SESSION["receiver_public_key"]["n"] ?><!--</small>-->
<!--                        <br/>-->
<!--                        <small>RSA Private Key: d=--><?php //= $_SESSION["receiver_private_key"]["d"] ?><!--, n=--><?php //= $_SESSION["receiver_private_key"]["n"] ?><!--</small>-->
<!--                    --><?php //endif; ?>
<!--                </div>-->
<!--            </div>-->



            <div class="row">
                <div class="col text-end">
                    <!-- Button to display RSA parameter input fields -->
                    <button id="show-rsa-btn" class="btn btn-danger btn-sm">Generate RSA Key</button>
                    <!-- Hidden form for RSA parameters -->
                    <form method="post" action="generate-rsa-key-receiver.php">

                        <div id="rsa-params-form" style="display:none; margin-top:10px;">
                            <input type="text" id="rsa_p" name="rsa_p" class="form-control" placeholder="Enter P" style="margin-bottom:5px;">
                            <input type="text" id="rsa_q" name="rsa_q" class="form-control" placeholder="Enter Q" style="margin-bottom:5px;">
                            <input type="text" id="rsa_e" name="rsa_e" class="form-control" placeholder="Enter E" style="margin-bottom:5px;">
                            <button type="submit" id="complete-rsa-btn" class="btn btn-warning btn-sm">Complete RSA Key Generation</button>
                        </div>
                    </form>

                    <!-- If the receiver's keys exist, display them and the Send RSA Key button -->
                    <?php if (isset($_SESSION["receiver_public_key"]["e"]) && isset($_SESSION["receiver_public_key"]["n"])): ?>
                        <button id="send-rsa-key-btn" class="btn btn-primary btn-sm">Send RSA Key</button>
                        <br/>
                        <small>RSA Public Key: e=<?= $_SESSION["receiver_public_key"]["e"] ?>, n=<?= $_SESSION["receiver_public_key"]["n"] ?></small>
                        <br/>
                        <small>RSA Private Key: d=<?= $_SESSION["receiver_private_key"]["d"] ?>, n=<?= $_SESSION["receiver_private_key"]["n"] ?></small>
                    <?php endif; ?>
                </div>
            </div>















            <div class="row">
                <div class="col-10">
                    <h3>
                        Message Receiving
                    </h3>
                </div>
            </div>


            <div class="chat-box">
                <form method="post" action="decrypt.php">
                    <div class="row mt-2 mb-2">
                        <div class="col-2">
                            <label for="ciphertext">CipherText:</label>
                        </div>
                    </div>
                    <div class="row mt-2 mb-2">
                        <div class="col">
    <textarea rows="6" readonly name="ciphertext"
              class="form-control encrypted-message-to-decode"><?=
        isset($_SESSION["encrypted_message_to_decrypt"]) ? htmlspecialchars($_SESSION["encrypted_message_to_decrypt"], ENT_QUOTES, 'UTF-8') : '';
        ?></textarea>
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
                                <option value="polyalphabetic">Polyalphabetic (Vigenère)</option>
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

                    <div class="row mt-2 mb-2">
                        <div class="col-3 col-md-2">
                            <label for="key">Key:</label>
                        </div>
                        <div class="col-9 col-md-10">
                            <input type="text" name="key" class="key form-control"/>
                        </div>
                    </div>

                    <div class="row text-end mb-2">
                        <div class="col">
                            <input type="hidden" name="action" value="decrypt"/>
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
                            <textarea rows="6" readonly name="plaintext"
                                      class="form-control plaintext"><?php echo $_SESSION["decrypted_message"]; ?></textarea>
                        </div>
                    </div>

                <?php } ?>
            </div>

            <?php include "includes/affiliation.php"; ?>
        </div>
    </div>

    <div class="modal fade" id="key-send-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <p id="encrypted-key">key</p>
                </div>
                <div class="modal-footer">
                    <button id="send-encrypted-key-btn" type="button" class="btn btn-success" data-bs-dismiss="modal">Send Key</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>

    <script>
        // When the "Generate RSA Key" button is clicked,
        // show the RSA parameter input form and hide the button.
        document.getElementById('show-rsa-btn').addEventListener('click', function() {
            document.getElementById('rsa-params-form').style.display = 'block';
            this.style.display = 'none';
        });

    </script>


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
                let encrypted_message_to_store = `${event.data}`;
// console.log(encrypted_message_to_store);
//                 alert(encrypted_message_to_store);
                if (encrypted_message_to_store !== "") {

                    $.ajax({
                        url: "save-session-variable.php",
                        type: "POST",
                        data: {
                            variable: encrypted_message_to_store,
                            variable_name: "encrypted_message_to_decrypt"
                        },
                        success: function (response) {
                            $("#responseDiv").html("Server Response: " + response);

                            $.ajax({
                                url: "save-session-variable.php",
                                type: "POST",
                                data: {
                                    variable: "",
                                    variable_name: "decrypted_message"
                                },
                                success: function (response) {
                                    $("#responseDiv").html("Server Response: " + response);
                                    location.reload();// Refresh page.
                                },
                                error: function (xhr, status, error) {
                                    console.error("Error: " + error);
                                }
                            });


                        },
                        error: function (xhr, status, error) {
                            console.error("Error: " + error);
                        }
                    });



                }
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


        $("#send-rsa-key-btn").click(function(){

            $.ajax({
                url: "encrypt-rsa-key.php",
                type: "POST",
                data: {

                    e: <?= $_SESSION["receiver_public_key"]["e"] ?>,
                    n: <?= $_SESSION["receiver_public_key"]["n"] ?>
                },
                success: function (response) {
                   $("#encrypted-key").html(response);
                    const modal = new bootstrap.Modal(document.getElementById('key-send-modal'));
                    modal.show();


                    $("#send-encrypted-key-btn").click(function(){
                        response_object = JSON.parse(response);

                        response_object.action = "rsa_key";

                        console.log(response_object, response);

                        socket.send(JSON.stringify(response_object));
                    });

                },
                error: function (xhr, status, error) {
                    console.error("Error: " + error);
                }
            });

        });

    </script>

</body>
</html>
