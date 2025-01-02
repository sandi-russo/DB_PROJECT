<?php
require_once 'functions.php'; // Funzioni di gestione database
require_once 'auth.php'; // Autenticazione utente

// Recupera tutti i settori scientifici
$settoriScientifici = OttieniSettoriScientifici();

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $ssd = $_GET['edit'];
    // Trova il settore specifico per la modifica
    foreach ($settoriScientifici as $settore) {
        if ($settore['SSD'] === $ssd) {
            $settoreInModifica = $settore;
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    // Recupera i dati dal modulo
    $vecchioSsd = $_POST['vecchio_ssd']; // SSD originale
    $nuovoSsd = $_POST['nuovo_ssd']; // Nuovo SSD
    $nuovoNome = $_POST['nuovo_nome'];

    // Rimuovi il settore vecchio e inserisci quello aggiornato
    $message = RimuoviSettoreScientifico($vecchioSsd);
    if (strpos($message, 'successo') !== false) {
        $message = InserisciSettoreScientifico($nuovoSsd, $nuovoNome);
    }

    // Ricarica la pagina con il messaggio
    header("Location: settore_scientifico.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $ssd = $_GET['delete'];

    // Chiamata alla funzione di rimozione
    $message = RimuoviSettoreScientifico($ssd);

    // Ricarica la pagina con il messaggio
    header("Location: settore_scientifico.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    // Aggiungi un nuovo settore scientifico
    $ssd = $_POST['ssd'];
    $nome = $_POST['nome'];

    // Chiamata alla funzione di aggiunta
    $message = InserisciSettoreScientifico($ssd, $nome);

    // Ricarica la pagina per aggiornare la lista
    header("Location: settore_scientifico.php?message=" . urlencode($message));
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
    <title>Gestione Settori Scientifici</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>Gestione Settori Scientifici</h1>

        <!-- Sezione per creare un nuovo settore scientifico -->
        <h2>Aggiungi Nuovo Settore Scientifico</h2>
        <form method="POST">
            <label for="ssd">SSD:</label>
            <input type="text" id="ssd" name="ssd" required>

            <label for="nome">Nome Settore:</label>
            <input type="text" id="nome" name="nome" required>

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($settoreInModifica)): ?>
            <!-- Modulo di modifica settore scientifico -->
            <h2>Modifica Settore Scientifico</h2>
            <form method="POST">
                <input type="hidden" name="vecchio_ssd" value="<?php echo htmlspecialchars($settoreInModifica['SSD']); ?>">

                <label for="nuovo_ssd">Nuovo SSD:</label>
                <input type="text" id="nuovo_ssd" name="nuovo_ssd"
                    value="<?php echo htmlspecialchars($settoreInModifica['SSD']); ?>" required>

                <label for="nuovo_nome">Nuovo Nome Settore:</label>
                <input type="text" id="nuovo_nome" name="nuovo_nome"
                    value="<?php echo htmlspecialchars($settoreInModifica['NomeSettore']); ?>" required>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella dei settori scientifici -->
            <h2>Lista dei Settori Scientifici</h2>
            <table>
                <thead>
                    <tr>
                        <th>SSD</th>
                        <th>Nome Settore</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($settoriScientifici as $settore): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($settore['SSD']); ?></td>
                            <td><?php echo htmlspecialchars($settore['NomeSettore']); ?></td>
                            <td>
                                <a href="settore_scientifico.php?edit=<?php echo htmlspecialchars($settore['SSD']); ?>">
                                    <button>Modifica</button>
                                </a>
                                <a href="settore_scientifico.php?delete=<?php echo htmlspecialchars($settore['SSD']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questo settore scientifico?');">
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