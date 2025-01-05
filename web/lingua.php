<?php
require_once 'functions.php'; // Funzioni di gestione database
require_once 'auth.php'; // Autenticazione utente

// Recupera tutte le lingue dal database
$lingue = OttieniLingue();

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $codiceLingua = $_GET['edit'];
    // Trova la lingua specifica per la modifica
    foreach ($lingue as $lingua) {
        if ($lingua['CodiceLingua'] === $codiceLingua) {
            $linguaInModifica = $lingua;
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    $vecchioCodice = $_POST['vecchio_codice'];
    $nomeLingua = $_POST['nome'];

    // Genera il nuovo codice
    $nuovoCodice = "L-" . strtoupper(substr($nomeLingua, 0, 3));

    // Rimuovi la lingua vecchia e inserisci quella aggiornata
    $message = RimuoviLingua($vecchioCodice);
    if (strpos($message, 'successo') !== false) {
        $message = InserisciLingua($nuovoCodice, $nomeLingua);
    }

    // Ricarica la pagina con il messaggio
    header("Location: lingua.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $codiceLingua = $_GET['delete'];

    // Chiamata alla funzione di rimozione
    $message = RimuoviLingua($codiceLingua);

    // Ricarica la pagina con il messaggio
    header("Location: lingua.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    $nomeLingua = strtoupper($_POST['nome']);

    // Genera il codice della lingua
    $codiceLingua = "L-" . strtoupper(substr($nomeLingua, 0, 3));

    // Chiamata alla funzione di aggiunta
    $message = InserisciLingua($codiceLingua, $nomeLingua);

    // Ricarica la pagina per aggiornare la lista
    header("Location: lingua.php?message=" . urlencode($message));
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
    <title>Gestione Lingue</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>Gestione Lingue</h1>

        <!-- Sezione per creare una nuova lingua -->
        <h2>Aggiungi Nuova Lingua</h2>
        <form method="POST">
            <label for="nome">Nome della Lingua:</label>
            <input type="text" id="nome" name="nome" required>

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($linguaInModifica)): ?>
            <!-- Modulo di modifica lingua -->
            <h2>Modifica Lingua</h2>
            <form method="POST">
                <input type="hidden" name="vecchio_codice"
                    value="<?php echo htmlspecialchars($linguaInModifica['CodiceLingua']); ?>">

                <label for="nome">Nome della Lingua:</label>
                <input type="text" id="nome" name="nome"
                    value="<?php echo htmlspecialchars($linguaInModifica['NomeLingua']); ?>" required>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella delle lingue -->
            <h2>Lista delle Lingue</h2>
            <table>
                <thead>
                    <tr>
                        <th>Codice</th>
                        <th>Nome</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lingue as $lingua): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($lingua['CodiceLingua']); ?></td>
                            <td><?php echo htmlspecialchars($lingua['NomeLingua']); ?></td>
                            <td>
                                <a href="lingua.php?edit=<?php echo htmlspecialchars($lingua['CodiceLingua']); ?>">
                                    <button>Modifica</button>
                                </a>
                                <a href="lingua.php?delete=<?php echo htmlspecialchars($lingua['CodiceLingua']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questa lingua?');">
                                    <button class="delete">Rimuovi</button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>