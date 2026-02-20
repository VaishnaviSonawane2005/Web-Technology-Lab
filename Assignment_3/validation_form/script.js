document.getElementById("registerForm").addEventListener("submit", function(e) {

    e.preventDefault();

    // prompt required by assignment
    let confirmSubmit = prompt("Type YES to submit");

    if (confirmSubmit !== "YES") {
        alert("Submission cancelled");
        return;
    }

    // inputs
    const name = document.getElementById("name");
    const email = document.getElementById("email");
    const phone = document.getElementById("phone");
    const password = document.getElementById("password");

    // error fields
    const nameError = document.getElementById("nameError");
    const emailError = document.getElementById("emailError");
    const phoneError = document.getElementById("phoneError");
    const passwordError = document.getElementById("passwordError");

    // clear old errors
    clearErrors();

    let firstInvalidField = null;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phonePattern = /^[0-9]{10}$/;
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

    // NAME
    if (name.value.trim() === "") {
        setError(name, nameError, "Name is required");
        firstInvalidField = firstInvalidField || name;
    }

    // EMAIL
    if (!emailPattern.test(email.value.trim())) {
        setError(email, emailError, "Enter valid email");
        firstInvalidField = firstInvalidField || email;
    }

    // PHONE
    if (!phonePattern.test(phone.value.trim())) {
        setError(phone, phoneError, "Enter 10 digit phone number");
        firstInvalidField = firstInvalidField || phone;
    }

    // PASSWORD
    if (!passwordPattern.test(password.value.trim())) {
        setError(password, passwordError,
            "Min 8 chars, uppercase, lowercase, number & symbol");
        firstInvalidField = firstInvalidField || password;
    }

    // focus first wrong field
    if (firstInvalidField) {
        firstInvalidField.focus();
        return;
    }

    alert("✅ Registration Successful!");
});

function setError(input, errorElement, message) {
    input.classList.add("errorInput");
    errorElement.innerText = message;
}

function clearErrors() {
    document.querySelectorAll("input").forEach(i => i.classList.remove("errorInput"));
    document.querySelectorAll(".error").forEach(e => e.innerText = "");
}