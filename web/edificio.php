<?php
include('functions.php');

// Ottieni le zone con gli indirizzi
$zone = [
    'Annunziata' => "Viale Giovanni Palatucci, 13",
    'Centro' => "Piazza Pugliatti, 1",
    'Papardo' => "Viale Ferdinando Stagno d'Alcontres 31"
];

// Mappatura esplicita dei prefissi per ogni zona
$prefissiZona = [
    'Annunziata' => 'ANN',
    'Centro' => 'CENT',
    'Papardo' => 'PAP'
];

// Inizializza variabili per gli edifici e le aule
$edifici = [];
$aule = [];

// Variabile per il messaggio di conferma
$messaggio = "";

// Verifica se è stata selezionata una zona
if (isset($_POST['zona']) && !empty($_POST['zona'])) {
    $zona = $_POST['zona'];
    // Ottieni l'indirizzo completo della zona selezionata
    $indirizzoZona = $zone[$zona];
    // Ottieni gli edifici per la zona selezionata usando l'indirizzo
    $edifici = OttieniEdificiPerZona($indirizzoZona);

    // Debug
    if (empty($edifici)) {
        error_log("Nessun edificio trovato per la zona: $zona con indirizzo: $indirizzoZona");
    }
}

// Verifica se è stato selezionato un edificio
if (isset($_POST['edificio']) && !empty($_POST['edificio'])) {
    $edificio = $_POST['edificio'];
    // Ottieni i dettagli per l'edificio selezionato
    $edificioInfo = OttieniDettagliEdificio($edificio);
}

// Aggiungi un nuovo edificio
if (isset($_POST['aggiungiEdificio'])) {
    $nome = $_POST['nomeEdificio'];
    $indirizzo = $_POST['indirizzoEdificio'];
    $capacitaTotale = $_POST['capacitaTotaleEdificio'];
    $zonaSelezionata = $_POST['zona']; // La zona selezionata per generare l'ID

    // Usa la mappatura esplicita per ottenere il prefisso corretto
    $prefisso = isset($prefissiZona[$zonaSelezionata]) ? $prefissiZona[$zonaSelezionata] : 'GEN'; // Default "GEN" se zona non trovata
    $idEdificio = $prefisso . "-ED-" . strtoupper($nome); // Usa il nome dell'edificio per generare l'ID

    // Salva il nome dell'edificio come "EDIFICIO <nome>"
    $nomeEdificioCompleto = "EDIFICIO " . ucfirst($nome);

    $risultato = aggiungiEdificio($idEdificio, $nomeEdificioCompleto, $indirizzo, $capacitaTotale);
    if ($risultato) {
        echo "Edificio aggiunto con successo!";
    } else {
        echo "Errore nell'aggiungere l'edificio.";
    }
}

// Modifica l'edificio
if (isset($_POST['ModificaEdificio'])) {
    $nome = $_POST['nome'];
    $indirizzo = $_POST['indirizzo'];
    $capacitaTotale = $_POST['capacitaTotale'];
    $idEdificio = $_POST['idEdificio'];

    // Salva il nome dell'edificio come "EDIFICIO <nome>"
    $nomeEdificioCompleto = "EDIFICIO " . ucfirst($nome);

    // Modifica l'edificio
    $risultato = modificaEdificio($idEdificio, $nomeEdificioCompleto, $indirizzo, $capacitaTotale);

    // Imposta il messaggio di conferma
    if ($risultato) {
        $messaggio = "Edificio modificato con successo!";
    } else {
        $messaggio = "Errore nella modifica dell'edificio.";
    }
}
?>

<!-- Form con i 3 menù a cascata: Zona, Edificio, Aula -->
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <!-- Seleziona la zona -->
    <select name="zona" onchange="this.form.submit()">
        <option value="">Seleziona Zona</option>
        <?php foreach ($zone as $nomeZona => $indirizzo): ?>
            <option value="<?php echo htmlspecialchars($nomeZona); ?>" <?php echo (isset($_POST['zona']) && $_POST['zona'] === $nomeZona) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($nomeZona) . ' (' . htmlspecialchars($indirizzo) . ')'; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Debug output -->
    <?php if (isset($_POST['zona']) && empty($edifici)): ?>
        <p>Nessun edificio trovato per questa zona.</p>
    <?php endif; ?>

    <!-- Seleziona l'edificio in base alla zona -->
    <?php if (isset($edifici) && !empty($edifici)): ?>
        <select name="edificio" onchange="this.form.submit()">
            <option value="">Seleziona Edificio</option>
            <?php foreach ($edifici as $edificio): ?>
                <option value="<?= $edificio['ID_Edificio'] ?>" <?= (isset($_POST['edificio']) && $_POST['edificio'] == $edificio['ID_Edificio']) ? 'selected' : '' ?>>
                    <?= $edificio['Nome'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <!-- Visualizza i dettagli dell'edificio e modificali -->
    <?php if (isset($edificioInfo) && !empty($_POST['edificio'])): ?>
        <hr>
        <h3>Dettagli dell'edificio: <?= $edificioInfo['Nome'] ?></h3>
        <input type="hidden" name="idEdificio" value="<?= $edificioInfo['ID_Edificio'] ?>">
        <input type="text" name="nome" value="<?= str_replace("EDIFICIO ", "", $edificioInfo['Nome']) ?>" required>
        <input type="text" name="indirizzo" value="<?= $edificioInfo['Indirizzo'] ?>" required>
        <input type="number" name="capacitaTotale" value="<?= $edificioInfo['CapacitaTotale'] ?>" required>
        <button type="submit" name="ModificaEdificio">Modifica Edificio</button>
    <?php endif; ?>

    <!-- Aggiungi un nuovo edificio -->
    <hr>
    <h3>Aggiungi un nuovo edificio</h3>
    <p><strong>Il nome dell'edificio inizierà sempre con "EDIFICIO" seguito dal nome che inserisci.</strong></p>
    <input type="text" name="nomeEdificio" placeholder="Nome Edificio (es. C)" required>

    <!-- Seleziona l'indirizzo da una lista -->
    <select name="indirizzoEdificio" required>
        <option value="">Seleziona Indirizzo</option>
        <?php foreach ($zone as $nomeZona => $indirizzo): ?>
            <option value="<?php echo $indirizzo; ?>" <?php echo (isset($_POST['indirizzoEdificio']) && $_POST['indirizzoEdificio'] === $indirizzo) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($nomeZona) . ' (' . htmlspecialchars($indirizzo) . ')'; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="capacitaTotaleEdificio" placeholder="Capacità Totale" required>
    <button type="submit" name="aggiungiEdificio">Aggiungi Edificio</button>
</form>

<!-- Messaggio di conferma o errore -->
<?php if ($messaggio): ?>
    <p><?php echo $messaggio; ?></p>
<?php endif; ?>