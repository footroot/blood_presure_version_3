    // Function to toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const toggleSpan = field.nextElementSibling;
        if (field.type === "password") {
            field.type = "text";
            toggleSpan.textContent = "Hide";
        } else {
            field.type = "password";
            toggleSpan.textContent = "Show";
        }
    }

    // Optional: A simple function for a logout button we will create later
function logout() {
    window.location.href = "logout.php";
}


