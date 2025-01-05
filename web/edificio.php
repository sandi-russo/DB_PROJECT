<?php
require_once 'functions.php'; // Funzioni di gestione database
require_once 'auth.php'; // Autenticazione utente

// Recupera tutti gli edifici
$edifici = OttieniEdifici();

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $codice = $_GET['edit'];
    foreach ($edifici as $edificio) {
        if ($edificio['ID_Edificio'] === $codice) {
            $edificioInModifica = $edificio;
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    $vecchioCodice = $_POST['vecchio_codice'];
    $nome = $_POST['nome'];
    $indirizzo = $_POST['indirizzo'];
    $capacitaTotale = $_POST['capacita_totale'];

    // Generazione del codice edificio
    $prefisso = '';
    if ($indirizzo === 'Viale Ferdinando Stagno d\'Alcontres 31 (Papardo)') {
        $prefisso = 'PAP';
    } elseif ($indirizzo === 'Piazza Pugliatti, 1 (Centro)') {
        $prefisso = 'CENT';
    } elseif ($indirizzo === 'Viale Giovanni Palatucci, 13 (Annunziata)') {
        $prefisso = 'ANN';
    }
    $nuovoCodice = $prefisso . '-ED-' . strtoupper($nome);

    // Aggiungi "EDIFICIO" al nome
    $nomeCompleto = 'EDIFICIO ' . strtoupper($nome);

    $message = RimuoviEdificio($vecchioCodice);
    if (strpos($message, 'successo') !== false) {
        $message = InserisciEdificio($nuovoCodice, $nomeCompleto, $indirizzo, $capacitaTotale);
    }

    header("Location: edificio.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    $nome = $_POST['nome'];
    $indirizzo = $_POST['indirizzo'];
    $capacitaTotale = $_POST['capacita_totale'];

    // Generazione del codice edificio
    $prefisso = '';
    if ($indirizzo === 'Viale Ferdinando Stagno d\'Alcontres 31 (Papardo)') {
        $prefisso = 'PAP';
    } elseif ($indirizzo === 'Piazza Pugliatti, 1 (Centro)') {
        $prefisso = 'CENT';
    } elseif ($indirizzo === 'Viale Giovanni Palatucci, 13 (Annunziata)') {
        $prefisso = 'ANN';
    }
    $codice = $prefisso . '-ED-' . strtoupper($nome);

    // Aggiungi "EDIFICIO" al nome
    $nomeCompleto = 'EDIFICIO ' . strtoupper($nome);

    $message = InserisciEdificio($codice, $nomeCompleto, $indirizzo, $capacitaTotale);

    header("Location: edificio.php?message=" . urlencode($message));
    exit;
}

// Visualizza il messaggio se presente
if (isset($_GET['message'])) {
    echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Gestione Edifici</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>Gestione Edifici</h1>

        <!-- Sezione per creare un nuovo edificio -->
        <h2>Aggiungi Nuovo Edificio</h2>
        <form method="POST">
            <label for="nome">Nome Edificio:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="indirizzo">Indirizzo:</label>
            <select id="indirizzo" name="indirizzo" required>
                <option value="Viale Ferdinando Stagno d'Alcontres 31 (Papardo)">Viale Ferdinando Stagno d'Alcontres 31
                    (Papardo)</option>
                <option value="Piazza Pugliatti, 1 (Centro)">Piazza Pugliatti, 1 (Centro)</option>
                <option value="Viale Giovanni Palatucci, 13 (Annunziata)">Viale Giovanni Palatucci, 13 (Annunziata)
                </option>
            </select>

            <label for="capacita_totale">Capacità Totale:</label>
            <input type="number" id="capacita_totale" name="capacita_totale" required min="0" step="any">

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($edificioInModifica)): ?>
            <!-- Modulo di modifica edificio -->
            <h2>Modifica Edificio</h2>
            <form method="POST">
                <input type="hidden" name="vecchio_codice"
                    value="<?php echo htmlspecialchars($edificioInModifica['ID_Edificio']); ?>">

                <label for="nome">Nome Edificio:</label>
                <input type="text" id="nome" name="nome"
                    value="<?php echo htmlspecialchars($edificioInModifica['Nome']); ?>" required>

                <label for="indirizzo">Indirizzo:</label>
                <select id="indirizzo" name="indirizzo" required>
                    <option value="Viale Ferdinando Stagno d'Alcontres 31 (Papardo)" <?php echo ($edificioInModifica['Indirizzo'] === 'Viale Ferdinando Stagno d\'Alcontres 31 (Papardo)') ? 'selected' : ''; ?>>Viale Ferdinando Stagno d'Alcontres 31 (Papardo)</option>
                    <option value="Piazza Pugliatti, 1 (Centro)" <?php echo ($edificioInModifica['Indirizzo'] === 'Piazza Pugliatti, 1 (Centro)') ? 'selected' : ''; ?>>Piazza Pugliatti, 1 (Centro)</option>
                    <option value="Viale Giovanni Palatucci, 13 (Annunziata)" <?php echo ($edificioInModifica['Indirizzo'] === 'Viale Giovanni Palatucci, 13 (Annunziata)') ? 'selected' : ''; ?>>Viale Giovanni Palatucci, 13 (Annunziata)</option>
                </select>

                <label for="capacita_totale">Capacità Totale:</label>
                <input type="number" id="capacita_totale" name="capacita_totale"
                    value="<?php echo htmlspecialchars($edificioInModifica['CapacitaTotale']); ?>" required min="0"
                    step="any">

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella degli edifici -->
            <h2>Lista degli Edifici</h2>
            <table>
                <thead>
                    <tr>
                        <th>Codice</th>
                        <th>Nome</th>
                        <th>Indirizzo</th>
                        <th>Capacità Totale</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($edifici as $edificio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($edificio['ID_Edificio']); ?></td>
                            <td><?php echo htmlspecialchars($edificio['Nome']); ?></td>
                            <td><?php echo htmlspecialchars($edificio['Indirizzo']); ?></td>
                            <td><?php echo htmlspecialchars($edificio['CapacitaTotale']); ?></td>
                            <td>
                                <a
                                    href="edificio.php?edit=<?php echo htmlspecialchars($edificio['ID_Edificio']); ?>"><button>Modifica</button></a>
                                <a href="edificio.php?delete=<?php echo htmlspecialchars($edificio['ID_Edificio']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questo edificio?');"><button
                                        class="delete">Rimuovi</button></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>