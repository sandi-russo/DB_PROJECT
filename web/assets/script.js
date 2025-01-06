// FUNZIONE PER VISUALIZZARE O NASCONDERE LA PASSWORD
function togglePasswordVisibility() {
  const passwordField = document.getElementById("password");
  const eyeIcon = document.getElementById("eye-icon");

  if (passwordField.type === "password") {
    passwordField.type = "text";
    eyeIcon.classList.remove("bx-show");
    eyeIcon.classList.add("bx-hide");
  } else {
    passwordField.type = "password";
    eyeIcon.classList.remove("bx-hide");
    eyeIcon.classList.add("bx-show");
  }
}

// FUNZIONE PER VALIDARE LA PASSWORD
function validatePassword(password) {
  const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/;
  return pattern.test(password);
}

document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("password");
  const submitButton = document.querySelector('button[type="submit"]');
  const errorDisplay = document.getElementById("password-error");

  passwordInput.addEventListener("input", function () {
    if (validatePassword(passwordInput.value)) {
      errorDisplay.textContent = "";
      submitButton.disabled = false;
    } else {
      errorDisplay.textContent = "La password non soddisfa i requisiti.";
      submitButton.disabled = true;
    }
  });
});