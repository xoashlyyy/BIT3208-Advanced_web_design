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