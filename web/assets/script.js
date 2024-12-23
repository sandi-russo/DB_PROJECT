document.addEventListener('DOMContentLoaded', function() {
    // Gestione dei checkbox per i vincoli
    document.querySelectorAll('.vincolo-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const container = this.closest('.giorno-container');
            const defaultOrari = container.querySelector('.orari-default');
            const vincoliOrari = container.querySelector('.orari-vincoli');

            if (this.checked) {
                defaultOrari.style.display = 'none';
                vincoliOrari.style.display = 'block';
            } else {
                defaultOrari.style.display = 'block';
                vincoliOrari.style.display = 'none';
            }
        });
    });
});


    // Gestione dell'aggiunta di fasce orarie
    document.querySelectorAll('.aggiungi-orario').forEach(button => {
        button.addEventListener('click', function() {
            const giorno = this.dataset.giorno;
            const container = this.closest('.orari-vincoli');
            const fasceOrarie = container.querySelectorAll('.fascia-oraria');
            const nuovoIndice = fasceOrarie.length;

            const nuovaFascia = document.createElement('div');
            nuovaFascia.className = 'fascia-oraria';
            
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