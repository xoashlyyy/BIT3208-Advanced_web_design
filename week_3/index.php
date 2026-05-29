<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Form Validation</title>
    <style>
        /* Basic styling to make the error message look like an error */
        #pwdError {
            color: red;
            display: none; /* Hidden by default, just like your JS expects */
            font-size: 0.85em;
            margin-top: 5px;
        }
        body { font-family: sans-serif; padding: 20px; }
        div { margin-bottom: 15px; }
    </style>
</head>
<body>

    <h2>Welcome, <span id="preview">Guest</span>!</h2>

    <form id="testForm">
        
        <div>
            <label for="username">Username:</label><br>
            <input type="text" id="username" placeholder="Type your name...">
        </div>
        
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" placeholder="Min 8 characters">
            
            <div id="pwdError">Error: Password must be at least 8 characters.</div>
        </div>

        <button type="submit">Test Validation</button>

    </form>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const usernameInput = document.getElementById("username");
            const previewSpan = document.getElementById("preview");
            const form = document.getElementById("testForm");
            const pwdInput = document.getElementById("password");
            const pwdError = document.getElementById("pwdError");

            if (usernameInput && previewSpan) {
                usernameInput.addEventListener("input", (e) => {
                    previewSpan.textContent = e.target.value.trim() || "Guest";
                });
            }

            if (form) {
                form.addEventListener("submit", (e) => {
                    if (pwdInput.value.length < 8) {
                        e.preventDefault();
                        pwdError.style.display = "block";
                    } else {
                        pwdError.style.display = "none";
                        alert("Validation Passed.");
                    }
                });
            }
        });
    </script>

</body>
</html>