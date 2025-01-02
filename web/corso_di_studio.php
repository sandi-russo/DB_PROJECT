<?php
require_once 'functions.php'; // Funzioni di gestione database
require_once 'auth.php'; // Autenticazione utente

// Recupera tutti i corsi di studio
$corsiDiStudio = OttieniCorsiDiStudio();

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $codice = $_GET['edit'];
    // Trova il corso specifico per la modifica
    foreach ($corsiDiStudio as $corso) {
        if ($corso['Codice'] === $codice) {
            $corsoInModifica = $corso;
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    // Recupera i dati dal modulo
    $vecchioCodice = $_POST['vecchio_codice'];
    $nuovoCodice = $_POST['nuovo_codice'];
    $nome = $_POST['nome'];
    $percorso = $_POST['percorso'];
    $annoCorso = $_POST['anno_corso'];

    // Rimuovi il corso vecchio e inserisci quello aggiornato
    $message = RimuoviCorsoDiStudio($vecchioCodice);
    if (strpos($message, 'successo') !== false) {
        $message = InserisciCorsoDiStudio($nuovoCodice, $nome, $percorso, $annoCorso);
    }

    // Ricarica la pagina con il messaggio
    header("Location: corso_di_studio.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $codice = $_GET['delete'];

    // Chiamata alla funzione di rimozione
    $message = RimuoviCorsoDiStudio($codice);

    // Ricarica la pagina con il messaggio
    header("Location: corso_di_studio.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    // Aggiungi un nuovo corso di studio
    $codice = $_POST['codice'];
    $nome = $_POST['nome'];
    $percorso = $_POST['percorso'];
    $annoCorso = $_POST['anno_corso'];

    // Chiamata alla funzione di aggiunta
    $message = InserisciCorsoDiStudio($codice, $nome, $percorso, $annoCorso);

    // Ricarica la pagina per aggiornare la lista
    header("Location: corso_di_studio.php?message=" . urlencode($message));
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
    <title>Gestione Corsi di Studio</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>Gestione Corsi di Studio</h1>

        <!-- Sezione per creare un nuovo corso di studio -->
        <h2>Aggiungi Nuovo Corso di Studio</h2>
        <form method="POST">
            <label for="codice">Codice:</label>
            <input type="text" id="codice" name="codice" required>

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="percorso">Percorso:</label>
            <input type="text" id="percorso" name="percorso">

            <label for="anno_corso">Anno Corso:</label>
            <select id="anno_corso" name="anno_corso" required>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($corsoInModifica)): ?>
            <!-- Modulo di modifica corso di studio -->
            <h2>Modifica Corso di Studio</h2>
            <form method="POST">
                <input type="hidden" name="vecchio_codice"
                    value="<?php echo htmlspecialchars($corsoInModifica['Codice']); ?>">

                <label for="nuovo_codice">Nuovo Codice:</label>
                <input type="text" id="nuovo_codice" name="nuovo_codice"
                    value="<?php echo htmlspecialchars($corsoInModifica['Codice']); ?>" required>

                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($corsoInModifica['Nome']); ?>"
                    required>

                <label for="percorso">Percorso:</label>
                <input type="text" id="percorso" name="percorso"
                    value="<?php echo htmlspecialchars($corsoInModifica['Percorso']); ?>">

                <label for="anno_corso">Anno Corso:</label>
                <select id="anno_corso" name="anno_corso" required>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($corsoInModifica['AnnoCorso'] == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella dei corsi di studio -->
            <h2>Lista dei Corsi di Studio</h2>
            <table>
                <thead>
                    <tr>
                        <th>Codice</th>
                        <th>Nome</th>
                        <th>Percorso</th>
                        <th>Anno Corso</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($corsiDiStudio as $corso): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($corso['Codice']); ?></td>
                            <td><?php echo htmlspecialchars($corso['Nome']); ?></td>
                            <td><?php echo htmlspecialchars($corso['Percorso']); ?></td>
                            <td><?php echo htmlspecialchars($corso['AnnoCorso']); ?></td>
                            <td>
                                <a href="corso_di_studio.php?edit=<?php echo htmlspecialchars($corso['Codice']); ?>">
                                    <button>Modifica</button>
                                </a>
                                <a href="corso_di_studio.php?delete=<?php echo htmlspecialchars($corso['Codice']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questo corso di studio?');">
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