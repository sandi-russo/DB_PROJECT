<?php
require_once 'functions.php'; // Funzioni di gestione database e utility
require_once 'auth.php'; // Autenticazione utente

// Recupera la lista degli indirizzi disponibili dal database o da un array predefinito
$indirizziDisponibili = OttieniIndirizzi(); // Funzione per ottenere indirizzi disponibili

// Recupera tutti gli edifici
$edifici = OttieniEdifici();

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $idEdificio = $_GET['edit'];
    foreach ($edifici as $edificio) {
        if ($edificio['ID_Edificio'] === $idEdificio) {
            $edificioInModifica = $edificio;
            // Rimuovi il prefisso per visualizzare solo il nome
            $edificioInModifica['Nome'] = RimuoviPrefissoEdificio($edificioInModifica['Nome']);
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    $vecchioCodice = $_POST['id_edificio'];
    $nome = AggiungiPrefissoEdificio($_POST['nome']); // Aggiungi il prefisso al nome
    $indirizzo = trim($_POST['indirizzo']);

    if (!is_numeric($_POST['capacita_totale'])) {
        $message = "Errore: Capacità Totale deve essere un numero valido!";
    } else {
        $capacitaTotale = intval($_POST['capacita_totale']); // Assicurati che sia un numero intero
        $message = ModificaEdificio($vecchioCodice, $nome, $indirizzo, $capacitaTotale);
    }

    header("Location: edificio.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    $nome = trim($_POST['nome']); // Usa il nome diretto senza aggiungere "EDIFICIO"
    $indirizzo = trim($_POST['indirizzo']);
    $idEdificio = GeneraCodiceEdificio($nome, $indirizzo); // Genera il codice edificio

    if (!is_numeric($_POST['capacita_totale'])) {
        $message = "Errore: Capacità Totale deve essere un numero valido!";
    } else {
        $capacitaTotale = intval($_POST['capacita_totale']); // Assicurati che sia un numero intero
        $message = InserisciEdificio($idEdificio, $nome, $indirizzo, $capacitaTotale);
    }

    header("Location: edificio.php?message=" . urlencode($message));
    exit;
}


// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $idEdificio = $_GET['delete'];
    $message = RimuoviEdificio($idEdificio);
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
                <option value="">Seleziona un indirizzo</option>
                <?php foreach ($indirizziDisponibili as $indirizzo): ?>
                    <option value="<?= htmlspecialchars($indirizzo) ?>"><?= htmlspecialchars($indirizzo) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="capacita_totale">Capacità Totale:</label>
            <input type="number" id="capacita_totale" name="capacita_totale" required min="0" step="1">

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($edificioInModifica)): ?>
            <!-- Modulo di modifica edificio -->
            <h2>Modifica Edificio</h2>
            <form method="POST">
                <input type="hidden" name="id_edificio" value="<?= htmlspecialchars($edificioInModifica['ID_Edificio']) ?>">

                <label for="nome">Nome Edificio:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($edificioInModifica['Nome']) ?>"
                    required>

                <label for="indirizzo">Indirizzo:</label>
                <select id="indirizzo" name="indirizzo" required>
                    <option value="">Seleziona un indirizzo</option>
                    <?php foreach ($indirizziDisponibili as $indirizzo): ?>
                        <option value="<?= htmlspecialchars($indirizzo) ?>" <?= $edificioInModifica['Indirizzo'] === $indirizzo ? 'selected' : '' ?>>
                            <?= htmlspecialchars($indirizzo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="capacita_totale">Capacità Totale:</label>
                <input type="number" id="capacita_totale" name="capacita_totale"
                    value="<?= htmlspecialchars($edificioInModifica['CapacitaTotale']) ?>" required min="0" step="1">

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella degli edifici -->
            <h2>Lista degli Edifici</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Indirizzo</th>
                        <th>Capacità Totale</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($edifici) && count($edifici) > 0): ?>
                        <?php foreach ($edifici as $edificio): ?>
                            <tr>
                                <td><?= htmlspecialchars($edificio['ID_Edificio']) ?></td>
                                <td><?= htmlspecialchars($edificio['Nome']) ?></td>
                                <td><?= htmlspecialchars($edificio['Indirizzo']) ?></td>
                                <td><?= htmlspecialchars($edificio['CapacitaTotale']) ?></td>
                                <td>
                                    <a
                                        href="edificio.php?edit=<?= htmlspecialchars($edificio['ID_Edificio']) ?>"><button>Modifica</button></a>
                                    <a href="edificio.php?delete=<?= htmlspecialchars($edificio['ID_Edificio']) ?>"
                                        onclick="return confirm('Sei sicuro di voler eliminare questo edificio?');">
                                        <button class="delete">Rimuovi</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nessun edificio trovato.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>