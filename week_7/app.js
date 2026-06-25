document.addEventListener("DOMContentLoaded", () => {
    // Password strength validation
    const pwdInput = document.getElementById("password");
    const helperText = document.getElementById("pwdHelper");
    const form = document.getElementById("authForm");

    if (pwdInput && helperText) {
        pwdInput.addEventListener("input", (e) => {
            const val = e.target.value;
            if (val.length === 0) {
                helperText.textContent = "";
            } else if (val.length < 8) {
                helperText.textContent = "Minimum 8 characters required.";
                helperText.style.color = "#ef4444";
            } else {
                helperText.textContent = "Valid length.";
                helperText.style.color = "#10b981";
            }
        });
    }

    if (form && pwdInput) {
        form.addEventListener("submit", (e) => {
            if (pwdInput.value.length < 8) {
                e.preventDefault();
                alert("Please meet the security requirements before submitting.");
            }
        });
    }
});