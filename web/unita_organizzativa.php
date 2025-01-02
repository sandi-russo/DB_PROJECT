<?php
require_once 'functions.php'; // Funzioni di gestione database
require_once 'auth.php'; // Autenticazione utente

// Recupera tutte le unità organizzative
$unitaOrganizzative = OttieniUnitaOrganizzative();

// Se è stato inviato un ID per la modifica
if (isset($_GET['edit'])) {
    $codice = $_GET['edit'];
    $unita = OttieniUnitaOrganizzativaPerModifica($codice);
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    // Recupera i dati dal modulo
    $vecchioCodice = $_POST['vecchio_codice'];
    $nuovoCodice = $_POST['nuovo_codice'];
    $nome = $_POST['nome'];

    // Chiamata alla funzione di modifica
    $message = ModificaUnitaOrganizzativa($vecchioCodice, $nuovoCodice, $nome);

    // Ricarica la pagina con il messaggio
    header("Location: unita_organizzativa.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $codice = $_GET['delete'];

    // Chiamata alla funzione di rimozione
    $message = RimuoviUnitaOrganizzativa($codice);

    // Ricarica la pagina con il messaggio
    header("Location: unita_organizzativa.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    // Aggiungi una nuova unità organizzativa
    $codice = $_POST['codice'];
    $nome = $_POST['nome'];

    // Chiamata alla funzione di aggiunta
    $message = AggiungiUnitaOrganizzativa($codice, $nome);

    // Ricarica la pagina per aggiornare la lista
    header("Location: unita_organizzativa.php?message=" . urlencode($message));
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
    <title>Gestione Unità Organizzative</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>Gestione Unità Organizzative</h1>

        <!-- Sezione per creare una nuova unità organizzativa -->
        <h2>Aggiungi Nuova Unità Organizzativa</h2>
        <form method="POST">
            <label for="codice">Codice:</label>
            <input type="text" id="codice" name="codice" required>

            <label for="nome">Nome (sarà aggiunto il prefisso "Dipartimento di"):</label>
            <input type="text" id="nome" name="nome" required placeholder="Es. Informatica">

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($unita)): ?>
            <!-- Modulo di modifica unità organizzativa -->
            <h2>Modifica Unità Organizzativa</h2>
            <form method="POST">
                <input type="hidden" name="vecchio_codice" value="<?php echo htmlspecialchars($unita['Codice']); ?>">

                <label for="nuovo_codice">Nuovo Codice:</label>
                <input type="text" id="nuovo_codice" name="nuovo_codice"
                    value="<?php echo htmlspecialchars($unita['Codice']); ?>" required>

                <label for="nome">Nuovo Nome (senza prefisso):</label>
                <input type="text" id="nome" name="nome"
                    value="<?php echo str_replace("Dipartimento di ", "", htmlspecialchars($unita['Nome'])); ?>" required>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella delle unità organizzative -->
            <h2>Lista delle Unità Organizzative</h2>
            <table>
                <thead>
                    <tr>
                        <th>Codice</th>
                        <th>Nome</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unitaOrganizzative as $unita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($unita['Codice']); ?></td>
                            <td><?php echo htmlspecialchars($unita['Nome']); ?></td>
                            <td>
                                <a href="unita_organizzativa.php?edit=<?php echo htmlspecialchars($unita['Codice']); ?>">
                                    <button>Modifica</button>
                                </a>
                                <a href="unita_organizzativa.php?delete=<?php echo htmlspecialchars($unita['Codice']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questa unità organizzativa?');">
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