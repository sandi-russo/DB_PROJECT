document.addEventListener("DOMContentLoaded", function () {
  // Gestione dei checkbox per i vincoli
  document.querySelectorAll(".vincolo-checkbox").forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      const container = this.closest(".giorno-container");
      const defaultOrari = container.querySelector(".orari-default");
      const vincoliOrari = container.querySelector(".orari-vincoli");

      if (this.checked) {
        defaultOrari.style.display = "none";
        vincoliOrari.style.display = "block";
      } else {
        defaultOrari.style.display = "block";
        vincoliOrari.style.display = "none";
      }
    });
  });

  // Aggiungi validazione sugli orari
  document.querySelectorAll('input[type="time"]').forEach((input) => {
    input.addEventListener("input", function () {
      const oraInizio =
        this.closest(".fascia-oraria").querySelector(".ora-inizio");
      const oraFine = this.closest(".fascia-oraria").querySelector(".ora-fine");

      if (oraInizio && oraFine) {
        // Verifica che l'ora di inizio non sia maggiore di quella di fine
        if (
          oraInizio.value &&
          oraFine.value &&
          oraInizio.value >= oraFine.value
        ) {
          oraFine.setCustomValidity(
            "L'ora di fine deve essere maggiore dell'ora di inizio."
          );
        } else {
          oraFine.setCustomValidity("");
        }
      }
    });
  });
});

// Gestione dell'aggiunta di fasce orarie
document.querySelectorAll(".aggiungi-orario").forEach((button) => {
  button.addEventListener("click", function () {
    const giorno = this.dataset.giorno;
    const container = this.closest(".orari-vincoli");
    const fasceOrarie = container.querySelectorAll(".fascia-oraria");
    const nuovoIndice = fasceOrarie.length;

    const nuovaFascia = document.createElement("div");
    nuovaFascia.className = "fascia-oraria";

    nuovaFascia.innerHTML = `
                <div class="input-group">
                    <label>Ora Inizio:
                        <input type="time" name="${giorno}[${nuovoIndice}][oraInizio]" class="ora-inizio">
                    </label>
                    <label>Ora Fine:
                        <input type="time" name="${giorno}[${nuovoIndice}][oraFine]" class="ora-fine">
                    </label>
                </div>
            `;

    container.insertBefore(nuovaFascia, this);
  });
});

// Funzione per visualizzare/nascondere la password
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

// Validazione lato client
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
