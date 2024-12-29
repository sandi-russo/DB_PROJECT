<?php
require_once 'functions.php';
require_once 'auth.php';

$giorniConVincoli = []; // Inizializza come array vuoto

// Se il modulo è stato inviato, chiamare la funzione CreaDocente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $ssd = $_POST['ssd'] ?? '';
    $unitaOrganizzativa = $_POST['unitaOrganizzativa'] ?? '';
    $maxOreConsentite = $_POST['maxOreConsentite'] ?? 0;

    // Verifica se i giorni con vincoli sono stati selezionati
    // Se non sono selezionati, usa un array vuoto
    $giorniConVincoli = isset($_POST['giorniConVincoli']) ? $_POST['giorniConVincoli'] : [];

    // Disponibilità oraria
    $disponibilita = [];
    $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi'];

    // Aggiungi i giorni con vincolo
    foreach ($giorni as $giorno) {
        if (in_array($giorno, $giorniConVincoli)) {
            // Se il giorno ha un vincolo, raccogli gli orari personalizzati
            if (isset($_POST[$giorno]) && is_array($_POST[$giorno])) {
                $disponibilita[$giorno] = [];
                foreach ($_POST[$giorno] as $orario) {
                    if (isset($orario['oraInizio']) && isset($orario['oraFine'])) {
                        $disponibilita[$giorno][] = [
                            'oraInizio' => $orario['oraInizio'],
                            'oraFine' => $orario['oraFine'],
                        ];
                    }
                }
            }
        } else {
            // Se il giorno non ha vincoli, imposta come "libero" (09:00-18:00)
            $disponibilita[$giorno] = [
                ['oraInizio' => '09:00', 'oraFine' => '18:00']
            ];
        }
    }

    // Chiamare la funzione per creare il docente
    $risultato = CreaDocente($nome, $cognome, $ssd, $unitaOrganizzativa, $disponibilita, $maxOreConsentite);
}

// Recupera gli SSD e le Unità Organizzative
$ssds = OttieniSSD();
$unita = OttieniUnitaOrganizzativa();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Crea Docente</title>
    <!-- Link al CSS esterno -->
    <link rel="stylesheet" type="text/css" href="/assets/style.css">
</head>

<body>
    <h2>Crea un nuovo docente</h2>

    <?php if (isset($risultato)): ?>
        <p><?php echo $risultato; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br>

        <label for="cognome">Cognome:</label>
        <input type="text" id="cognome" name="cognome" required><br>

        <label for="ssd">SSD:</label>
        <select id="ssd" name="ssd" required>
            <option value="">Seleziona SSD</option>
            <?php foreach ($ssds as $ssd_item): ?>
                <option value="<?php echo $ssd_item['SSD']; ?>">
                    <?php echo $ssd_item['SSD'] . " - " . $ssd_item['NomeSettore']; ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="unitaOrganizzativa">Unità Organizzativa:</label>
        <select id="unitaOrganizzativa" name="unitaOrganizzativa" required>
            <option value="">Seleziona Unità Organizzativa</option>
            <?php foreach ($unita as $unita_item): ?>
                <option value="<?php echo $unita_item['Codice']; ?>"><?php echo $unita_item['Nome']; ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="tipoDocente">Tipo di Docente:</label>
        <select id="tipoDocente" name="tipoDocente" required>
            <option value="">Seleziona il tipo di docente</option>
            <option value="docente_associato">Docente Associato - 100 ore</option>
            <option value="docente_ordinario">Docente Ordinario - 120 ore</option>
            <option value="docente_ricercatore">Docente Ricercatore - 90 ore</option>
        </select><br>

        <h3>Disponibilità Oraria</h3>
        <div class="disponibilita-container">
            <?php foreach (['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi'] as $giorno): ?>
                <div class="giorno-container">
                    <div class="giorno-header">
                        <h4><?php echo ucfirst($giorno); ?></h4>
                        <input type="checkbox" id="vincolo_<?php echo $giorno; ?>" name="giorniConVincoli[]"
                            value="<?php echo $giorno; ?>" class="vincolo-checkbox">
                        <label for="vincolo_<?php echo $giorno; ?>">Aggiungi vincoli orari</label>
                    </div>

                    <div class="orari" id="disponibilita_<?php echo $giorno; ?>">
                        <div class="orari-default">
                            <p>Disponibile dalle 09:00 alle 18:00</p>
                        </div>

                        <div class="orari-vincoli" style="display: none;">
                            <div class="fascia-oraria">
                                <div class="input-group">
                                    <label>Ora Inizio:
                                        <input type="time" name="<?php echo $giorno; ?>[0][oraInizio]" class="ora-inizio"
                                            min="09:00" max="18:00">
                                    </label>
                                    <label>Ora Fine:
                                        <input type="time" name="<?php echo $giorno; ?>[0][oraFine]" class="ora-fine"
                                            min="09:00" max="18:00">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">Crea Docente</button>
    </form>


    <h3>ELENCO DOCENTI</h3>
    <?php VisualizzaElencoDocenti() ?>

    <!-- Link al file JavaScript esterno -->
    <script src="/assets/script.js"></script>
</body>

</html>