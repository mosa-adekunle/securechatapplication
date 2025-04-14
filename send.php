<?php include "chat-controller.php"; ?>
<html>
<head>
    <?php include "includes/headers.php"; ?>
</head>
<body>

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

            <a href="receive.php" style="text-decoration: none;">
                <button class="btn  btn-dark btn-sm">
                    Receive Message
                </button>
            </a>

            <a href="clear-session.php" style="text-decoration: none;">
                <btn class="btn btn-sm btn-dark">
                    Clear Session
                </btn>
            </a>
            <a href="logout.php">
                <btn class="btn btn-sm btn-dark">
                    Logout
                </btn>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <p>
                <svg width="35" height="55">
                    <circle cx="25" cy="25" r="8" fill="green" class="blinking"/>
                </svg>
                <strong>
                    Sending
                </strong>
            </p>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            <div class="chat-box">
                <form method="post" action="encrypt.php" onsubmit="return validateForm()">
                    <div class="row mt-2 mb-2">
                        <div class="col-3 col-md-2">
                            <label for="plaintext">Plain Text:</label>
                        </div>
                        <div class="col-9 col-md-10">
                        <textarea rows="6" name="plaintext"
                                  class="form-control message"><?php echo $_SESSION["plaintext"]; ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-2 mt-1">
                        <div class="col-3 col-md-2">
                            <label for="technique">Technique:</label>
                        </div>
                        <div class="col-9 col-md-10">
                            <select name="technique" class="form-control" id="technique">
                                <option value="">
                                    Select
                                </option>
                                <option value="caesar" <?php echo ($_SESSION["technique"] == 'caesar') ? 'selected' : ''; ?>>
                                    Caesar cipher
                                </option>
                                <option value="monoalphabetic" <?php echo ($_SESSION["technique"] == 'monoalphabetic') ? 'selected' : ''; ?>>
                                    Monoalphabetic
                                </option>
                                <option value="polyalphabetic" <?php echo ($_SESSION["technique"] == 'polyalphabetic') ? 'selected' : ''; ?>>
                                    Polyalphabetic (Vigenère)
                                </option>
                                <option value="hill" <?php echo ($_SESSION["technique"] == 'hill') ? 'selected' : ''; ?>>
                                    Hill cipher
                                </option>
                                <option value="playfair" <?php echo ($_SESSION["technique"] == 'playfair') ? 'selected' : ''; ?>>
                                    Playfair
                                </option>
                                <option value="otp" <?php echo ($_SESSION["technique"] == 'otp') ? 'selected' : ''; ?>>
                                    OTP
                                    (One-Time Pad)
                                </option>
                                <option value="rail-fence" <?php echo ($_SESSION["technique"] == 'rail-fence') ? 'selected' : ''; ?>>
                                    Rail fence
                                </option>
                                <option value="columnar" <?php echo ($_SESSION["technique"] == 'columnar') ? 'selected' : ''; ?>>
                                    Columnar
                                </option>
                                <option value="des" <?php echo ($_SESSION["technique"] == 'des') ? 'selected' : ''; ?>>
                                    DES
                                    (Data Encryption Standard)
                                </option>
                                <option value="aes" <?php echo ($_SESSION["technique"] == 'aes') ? 'selected' : ''; ?>>
                                    AES
                                    (Advanced Encryption Standard)
                                </option>
                                <option value="rc4" <?php echo ($_SESSION["technique"] == 'rc4') ? 'selected' : ''; ?>>
                                    RC4
                                </option>
                                <option value="rsa" <?php echo ($_SESSION["technique"] == 'rsa') ? 'selected' : ''; ?>>
                                    RSA
                                </option>
                                <option value="ecc" <?php echo ($_SESSION["technique"] == 'ecc') ? 'selected' : ''; ?>>
                                    ECC
                                    (Elliptic Curve Cryptography)
                                </option>
                            </select>
                        </div>
                    </div>


                    <div class="row mt-2 mb-2">
                        <div class="col-3 col-md-2">
                            <label for="key">Key:</label>
                        </div>
                        <div class="col-9 col-md-10">
                            <input id="key" type="text" name="key" class="key form-control"
                                   value="<?php echo $_SESSION["key"]; ?>"/>
                            <span id="key-error" style="color: red; display: none;">Key must be an integer for Caesar cipher!</span>

                        </div>
                    </div>

                    <div class="row text-end mb-2">
                        <div class="col">
                            <input type="hidden" name="action" value="encrypt"/>
                            <button class="btn btn-dark" id="encrypt-btn">
                                Encrypt
                            </button>
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
                                <button class="btn btn-dark" id="send-message">
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

        function xorDecrypt(ciphertext, key) {
            var decoded = atob(ciphertext); //Decode the Base64-encoded ciphertext.
            var result = "";
            var k = key % 256;
            for (var i = 0; i < decoded.length; i++) {
                result += String.fromCharCode(decoded.charCodeAt(i) ^ k);
            }
            return result;
        }

    </script>

    <script>
        var dhSharedSecret;

        function validateTechniques() {
            var technique = document.getElementById('technique').value;
            var keyInput = document.getElementById('key');
            var keyError = document.getElementById('key-error');

            // If technique is Caesar, check if the key is a valid integer
            if (technique === 'caesar') {
                if (isNaN(keyInput.value) || keyInput.value === "") {
                    keyError.style.display = "inline";
                } else {
                    keyError.style.display = "none";
                }
            } else {
                keyError.style.display = "none";
            }
        }

        function validateForm() {
            const technique = document.getElementById('technique').value;
            const keyInput = document.getElementById('key');
            const keyValue = keyInput.value;
            const keyError = document.getElementById('key-error');

            if (technique === 'caesar') {
                if (isNaN(keyValue) || keyValue === "") {
                    keyError.textContent = "Key must be an integer for Caesar cipher!";
                    keyError.style.display = "inline";
                    return false;
                }
            } else if (technique === 'monoalphabetic') {
                if (!isValidMonoalphabeticKey(keyValue.toUpperCase())) {
                    keyError.textContent = "Key must be 26 unique uppercase letters A–Z for Monoalphabetic cipher!";
                    keyError.style.display = "inline";
                    return false;
                }
            } else if (technique === 'polyalphabetic') {
                if (!isValidVigenereKey(keyValue)) {
                    keyError.textContent = "Key must contain only letters A–Z for Vigenère cipher!";
                    keyError.style.display = "inline";
                    return false;
                }

            } else if (technique === 'hill') {
                const hillError = isValidHillKey(keyValue);
                if (hillError !== "") {
                    keyError.textContent = hillError;
                    keyError.style.display = "inline";
                    return false;
                }
            }
            else if (technique === 'playfair') {
                const playfairError = isValidPlayfairKey(keyValue);
                if (playfairError !== "") {
                    keyError.textContent = playfairError;
                    keyError.style.display = "inline";
                    return false;
                }
            }
            else if (technique === 'otp') {
                const inputField = document.querySelector('[name="plaintext"], [name="ciphertext"]');
                const inputText = inputField ? inputField.value.trim() : '';
                const otpError = isValidOTP(inputText, keyValue);

                if (otpError !== "") {
                    keyError.textContent = otpError;
                    keyError.style.display = "inline";
                    return false;
                }
            }
            else if (technique === 'rail-fence') {
                const inputField = document.querySelector('[name="plaintext"], [name="ciphertext"]');
                const inputText = inputField ? inputField.value.trim() : '';
                const railError = isValidRailFenceKey(keyValue, inputText);

                if (railError !== "") {
                    keyError.textContent = railError;
                    keyError.style.display = "inline";
                    return false;
                }

                keyError.style.display = "none";
            }
            else if (technique === 'columnar') {
                const columnarError = isValidColumnarKey(keyValue);
                if (columnarError !== "") {
                    keyError.textContent = columnarError;
                    keyError.style.display = "inline";
                    return false;
                }

                keyError.style.display = "none";
            }


            keyError.style.display = "none";
            return true;
        }

        function isValidColumnarKey(key) {
            if (!/^[a-zA-Z]+$/.test(key)) {
                return "Key must contain only alphabetic characters (A–Z).";
            }

            if (key.length < 2) {
                return "Key must be at least 2 characters long for Columnar cipher.";
            }

            return ""; // ✅ Valid
        }


        function isValidRailFenceKey(key, inputText) {
            const railCount = parseInt(key);

            if (isNaN(railCount) || railCount < 2) {
                return "Rail Fence key must be an integer ≥ 2.";
            }

            if (inputText.trim().length < railCount) {
                return "Rail Fence key must be less than or equal to the length of the input text.";
            }

            return ""; // ✅ Valid
        }


        function isValidOTP(inputText, key) {
            inputText = inputText.trim();
            key = key.trim();

            if (inputText === "") {
                return "Input text cannot be empty for OTP.";
            }

            if (key === "") {
                return "Key cannot be empty for OTP.";
            }

            if (key.length < inputText.length) {
                return "Key must be at least as long as the input text for One-Time Pad.";
            }

            return ""; // ✅ Valid
        }


        function isValidPlayfairKey(key) {
            const sanitized = key.toUpperCase().replace(/[^A-Z]/g, '').replace(/J/g, 'I');
            const seen = new Set();

            for (let i = 0; i < sanitized.length; i++) {
                seen.add(sanitized[i]);
            }

            const uniqueCount = seen.size;

            if (uniqueCount < 5) {
                return "Key must have at least 5 unique letters.";
            }
            if (uniqueCount > 25) {
                return "Key must have at most 25 unique letters.";
            }

            return "";
        }


        function isValidMonoalphabeticKey(key) {
            // Check if the key is exactly 26 uppercase letters with no repeats
            if (key.length !== 26) return false;
            if (!/^[A-Z]+$/.test(key)) return false;

            const uniqueLetters = new Set(key);
            return uniqueLetters.size === 26;
        }

        function isValidVigenereKey(key) {
            // Must be at least one character and all letters only
            return key.length > 0 && /^[A-Za-z]+$/.test(key);
        }


        function gcd(a, b) {
            while (b !== 0) {
                const temp = b;
                b = a % b;
                a = temp;
            }
            return a;
        }

        function modInverse(a, m) {
            a = ((a % m) + m) % m;
            for (let x = 1; x < m; x++) {
                if ((a * x) % m === 1) {
                    return x;
                }
            }
            return -1;
        }

        function isValidHillKey(key) {
            const values = key.split(',').map(s => s.trim());

            if (values.length !== 4) {
                return "Hill cipher key must have exactly 4 comma-separated numbers.";
            }

            const nums = values.map(Number);

            if (nums.some(n => isNaN(n) || n < 0 || n > 25)) {
                return "Each number must be an integer between 0 and 25.";
            }

            const [a, b, c, d] = nums;
            const det = (a * d - b * c) % 26;
            const positiveDet = (det + 26) % 26;

            if (gcd(positiveDet, 26) !== 1) {
                return `Matrix is not invertible mod 26. Determinant is ${positiveDet}, which shares factors with 26.`;
            }

            return ""; // No error = valid
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


        let socket = null;

        if (sessionStorage.getItem("socket")) {
            socket = JSON.parse(sessionStorage.getItem("socket"));
        } else {
            // Create new WebSocket connection
            socket = new WebSocket('ws://3.147.127.186:8080');
            socket.onopen = () => {
                let connection_message = "WebSocket connection established from <?=$username;?>.";
                console.log(connection_message);
                // socket.send(connection_message);
            };

            socket.onmessage = (event) => {
                console.log(`Message received from server: ${event.data}`);


                if (event.data && event.data.trim() !== "") {
                    try {
                        let data = JSON.parse(event.data);
// alert();
                        // Check if the sender is initiating a Diffie–Hellman exchange.
                        if (data.action && data.action === "dh_exchange") {
                            // Extract the Diffie–Hellman parameters from the sender.
                            const p = data.p; // prime provided by the sender
                            const g = data.g; // generator provided by the sender
                            const senderPublic = data.publicKey; // sender's DH public value

                            // Generate receiver's DH key pair:
                            // Choose a random private key in range [1, p-2]
                            const receiverPrivate = Math.floor(Math.random() * (p - 2)) + 1;
                            const receiverPublic = modExp(g, receiverPrivate, p);

                            // Compute shared secret: (senderPublic ^ receiverPrivate mod p)
                            const sharedSecret = modExp(senderPublic, receiverPrivate, p);
                            dhSharedSecret = sharedSecret;
                            console.log("Receiver: Shared secret computed:", sharedSecret);
                            // (Optionally, store sharedSecret for further encryption operations.)

                            // Now, send a reply back to the sender with your public DH value.
                            let reply = {
                                action: "dh_exchange_reply",
                                receiverPublic: receiverPublic,
                                p: p,
                                g: g
                            };
                            socket.send(JSON.stringify(reply));
                            console.log("Receiver: Sent DH exchange reply:", reply);
                        }

                        // Check if this is an encrypted RSA key message.
                        if (data.action && data.action === "rsa_key_encrypted") {
                            // Ensure that the receiver has computed its DH shared secret.
                            // For example, your DH code should store the shared secret in a global variable:
                            // var dhSharedSecret = <computed shared secret value>;
                            if (typeof dhSharedSecret === 'undefined') {
                                console.error("DH shared secret is not available on receiver side.");
                                return;
                            }

                            // Decrypt the RSA key using the shared secret.
                            var decryptedRsaKeyString = xorDecrypt(data.encryptedRsaKey, dhSharedSecret);
                            console.log("Receiver: Decrypted RSA Key string:", decryptedRsaKeyString);

                            // Parse the decrypted RSA key JSON.
                            var rsaKeyData = JSON.parse(decryptedRsaKeyString);
                            alert("Receiver: RSA key received:\n e: " + rsaKeyData.e + "\n n: " + rsaKeyData.n);
                        }


                    } catch (e) {
                        console.error("Receiver: Error parsing JSON from event.data:", e);
                    }
                }


                if (event.data && event.data.trim() !== "") {
                    let data_received = JSON.parse(event.data);

                    console.log(data_received.action);
                    if (data_received.action === "rsa_key") {
                        alert(data_received.action +
                            " received: " +
                            "e:" +
                            data_received.e +
                            " n:" +
                            data_received.n
                        );
                    }
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

        function sendMessage(message) {
            if (socket.readyState === WebSocket.OPEN) {
                socket.send(message);
                console.log(`Message sent: ${message}`);

                // alert("Encrypted message sent");
                setTimeout(function () {
                    window.location = "send.php";
                    window.location = "clear-session.php";
                }, 1000);
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
