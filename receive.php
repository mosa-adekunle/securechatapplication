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

<br>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h2>
                Logged in as <?php echo $username; ?>
            </h2>

        </div>
        <div class="col text-end">
            <a href="send.php" style="text-decoration: none;">
                <button class="btn  btn-dark btn-sm">
                    Send Message
                </button>
            </a>

            <a href="clear-session.php" style="text-decoration: none;">
                <btn class="btn btn-sm btn-dark">
                    Clear Session
                </btn>
            </a>
            <a href="logout.php" style="text-decoration: none;">
                <btn class="btn btn-sm btn-dark">
                    Logout
                </btn>
            </a>
        </div>
    </div>


    <div class="row mt-2">
        <div class="col">


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


                <div class="col">


                    <p>
                        <svg width="35" height="55">
                            <circle cx="25" cy="25" r="8" fill="green" class="blinking"/>
                        </svg>
                        <strong>
                            Receiving
                        </strong>

                    </p>
                </div>

                <div class="col-7 text-end">
                    <!-- Button to display RSA parameter input fields -->
                    <button id="show-rsa-btn" class="btn btn-danger btn-sm">Generate RSA Key</button>

                    <!-- Hidden form for RSA parameters -->
                    <form method="post" action="generate-rsa-key-receiver.php" id="rsa-form">
                        <div id="rsa-params-form" style="display:none; margin-top:10px;">
                            <input type="text" id="rsa_p" name="rsa_p" class="form-control" placeholder="Enter P" style="margin-bottom:5px;">
                            <input type="text" id="rsa_q" name="rsa_q" class="form-control" placeholder="Enter Q" style="margin-bottom:5px;">
                            <button type="submit" id="complete-rsa-btn" class="btn btn-warning btn-sm">Complete RSA Key Generation</button>
                        </div>
                    </form>

                    <!-- If the receiver's keys exist, display them and the Send RSA Key button -->
                    <?php if (isset($_SESSION["receiver_public_key"]["e"]) && isset($_SESSION["receiver_public_key"]["n"])): ?>
                        <button id="send-rsa-key-btn" class="btn btn-dark btn-sm">Send RSA Key with Diffie–Hellman</button>
                        <br/><br/>
                        <small><strong>RSA Public Key</strong>: e=<?= $_SESSION["receiver_public_key"]["e"] ?>, n=<?= $_SESSION["receiver_public_key"]["n"] ?></small>
                        <small><strong>RSA Private Key</strong>: d=<?= $_SESSION["receiver_private_key"]["d"] ?>, n=<?= $_SESSION["receiver_private_key"]["n"] ?></small>
                    <?php endif; ?>


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
                                <option value="sha">SHA (Verify)</option>
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
                            <button class="btn btn-dark">
                                Decrypt
                            </button>
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
                </div>
                <div class="modal-footer">
                    <button id="send-encrypted-key-btn" type="button" class="btn btn-dark" data-bs-dismiss="modal">Send Key</button>

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
        // Function to test if a number is prime.
        function isPrime(num) {
            if (num <= 1) return false;
            if (num <= 3) return true;
            if (num % 2 === 0 || num % 3 === 0) return false;
            for (var i = 5; i * i <= num; i += 6) {
                if (num % i === 0 || num % (i + 2) === 0) return false;
            }
            return true;
        }

        // Reveal the RSA parameter inputs when the "Generate RSA Key" button is clicked.
        document.getElementById('show-rsa-btn').addEventListener('click', function() {
            document.getElementById('rsa-params-form').style.display = 'block';
            this.style.display = 'none';
        });

        document.getElementById('complete-rsa-btn').addEventListener('click', function(event) {
            // Convert inputs to integers.
            var p = parseInt(document.getElementById('rsa_p').value.trim(), 10);
            var q = parseInt(document.getElementById('rsa_q').value.trim(), 10);

            if (!p || p <= 1 || !isPrime(p)) {
                alert("P must be a prime number greater than 1.");
                event.preventDefault();
                return;
            }
            if (!q || q <= 1 || !isPrime(q)) {
                alert("Q must be a prime number greater than 1.");
                event.preventDefault();
                return;
            }
            // If validation passes, the form will submit.
        });
    </script>

    <script>
        function xorEncrypt(plaintext, key) {
            var result = "";
            // Use key modulo 256 as a single byte (this is very weak cryptography, for demo only)
            var k = key % 256;
            for (var i = 0; i < plaintext.length; i++) {
                result += String.fromCharCode(plaintext.charCodeAt(i) ^ k);
            }
            // Encode the result in base64 to ensure it's safe to transmit in JSON
            return btoa(result);
        }

        function isJson(str) {
            try {
                JSON.parse(str);
                return true;
            } catch (e) {
                return false;
            }
        }

    </script>

    <script>
        let socket = new WebSocket('ws://3.147.127.186:8080');

        socket.onopen = () => {
            //let connection_message = "WebSocket connection established from <?php //=$username;?>//.";
            //console.log(connection_message);
            //socket.send(connection_message);
        };

        socket.onmessage = (event) => {

            console.log(`Message received from server: ${event.data}`);


            if (event.data && event.data.trim() !== "") {
                try {

                    if (isJson(event.data)) {
                        let data_received = JSON.parse(event.data);
                        if (data_received.action && data_received.action === "dh_exchange_reply") {
                            // Receiver's DH public value and parameter p are received.
                            let B = data_received.receiverPublic;
                            let p = data_received.p;  // must be the same p we used.
                            // Compute shared secret s = B^(our_private) mod p using our stored DH private key.
                            var sharedSecret = modExp(dhData.privateKey, 1, p);  // placeholder
                            // Actually, compute sharedSecret = (B^our_private mod p)
                            sharedSecret = modExp(B, dhData.privateKey, p);
                            console.log("Shared Secret computed:", sharedSecret);


                            var rsaKeyObject = {
                                e: <?= isset($_SESSION["receiver_public_key"]["e"]) ? $_SESSION["receiver_public_key"]["e"] : 'null'; ?>,
                                n: <?= isset($_SESSION["receiver_public_key"]["n"]) ? $_SESSION["receiver_public_key"]["n"] : 'null'; ?>
                            };


                            // Convert the object to a JSON string.
                            var rsaKeyString = JSON.stringify(rsaKeyObject);
                            // Encrypt the RSA key string using our shared secret (via XOR encryption).
                            var encryptedRsaKey = xorEncrypt(rsaKeyString, sharedSecret);

                            // Create the message to be sent
                            var rsaKeyMessage = {
                                action: "rsa_key_encrypted",
                                encryptedRsaKey: encryptedRsaKey
                                // Optionally, you might remove sharedSecret from the clear; here it's not sent.
                            };



                            socket.send(JSON.stringify(rsaKeyMessage));
                            console.log("Sent RSA key message:", rsaKeyMessage);
                        }
                        else if (data_received.action && data_received.action === "rsa_key") {
                            alert("RSA key received: e:" + data_received.e + " n:" + data_received.n);
                        }
                    }
                    else{
                        $(".encrypted-message").html(event.data);
                        let encrypted_message_to_store = `${event.data}`;

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
                    }

                } catch (e) {
                    console.error("Error parsing JSON from event.data:", e);
                }
            }
        };

        socket.onclose = (event) => {
            console.log(`WebSocket connection closed: ${event.code} - ${event.reason}`);
        };

        socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };

        window.onbeforeunload = function () {
            if (socket && socket.readyState === WebSocket.OPEN) {
                socket.close();
            }
        };

        // Close the WebSocket connection when leaving the page or when explicitly needed
        window.onbeforeunload = function () {
            if (socket) {
                socket.close();
            }
            sessionStorage.removeItem("socket");
        };

        // When "Send RSA Key" is clicked, perform Diffie–Hellman exchange.
        $("#send-rsa-key-btn").click(function(){
            // Generate our Diffie–Hellman key pair.
            dhData = diffieHellmanGenerate();
            console.log("Our DH Data:", dhData);
            // Send our DH public data to the receiver.
            var dhMessage = {
                action: "dh_exchange",
                p: dhData.p,
                g: dhData.g,
                publicKey: dhData.publicKey
            };
            socket.send(JSON.stringify(dhMessage));
            console.log("Sent DH public data:", dhMessage);
        });

        // Generate a simple Diffie–Hellman key pair (for demo purposes with small parameters).
        function diffieHellmanGenerate() {
            const p = 23; // demonstration prime (use large primes in production)
            const g = 5;  // demonstration generator
            const privateKey = Math.floor(Math.random() * (p - 2)) + 1; // random integer in [1, p-2]
            const publicKey = modExp(g, privateKey, p);
            return { p: p, g: g, privateKey: privateKey, publicKey: publicKey };
        }

        // Simple modular exponentiation: calculates base^exp mod mod.
        function modExp(base, exp, mod) {
            var result = 1;
            base = base % mod;
            while (exp > 0) {
                if (exp % 2 === 1) {
                    result = (result * base) % mod;
                }
                exp = Math.floor(exp / 2);
                base = (base * base) % mod;
            }
            return result;
        }


    </script>
</body>
</html>
