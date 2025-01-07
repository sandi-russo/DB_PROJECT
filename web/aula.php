<?php
require_once 'functions.php'; // Funzioni di gestione database
require_once 'auth.php'; // Autenticazione utente

// Recupera tutti gli edifici
$edifici = OttieniEdifici();

// Recupera tutti gli indirizzi unici
$indirizzi = array_unique(array_column($edifici, 'Indirizzo'));

// Recupera tutte le aule
$aule = OttieniAule();

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $idAula = $_GET['edit'];
    foreach ($aule as $aula) {
        if ($aula['ID_Aula'] === $idAula) {
            $aulaInModifica = $aula;
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    $idAula = $_POST['id_aula'];
    $nome = $_POST['nome'];
    $capacita = $_POST['capacita'];
    $tipologia = $_POST['tipologia'];
    $edificio = $_POST['edificio'];

    $message = ModificaAula($idAula, $nome, $capacita, $tipologia, $edificio);

    header("Location: aula.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    $nome = $_POST['nome'];
    $capacita = $_POST['capacita'];
    $tipologia = $_POST['tipologia'];
    $edificio = $_POST['edificio'];

    $message = InserisciAula($nome, $capacita, $tipologia, $edificio);

    header("Location: aula.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $idAula = $_GET['delete'];

    $message = RimuoviAula($idAula);

    header("Location: aula.php?message=" . urlencode($message));
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
    <title>Gestione Aule</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <script src="assets/script.js"></script>
</head>

<body>
    <div class="container">
        <h1>Gestione Aule</h1>

        <!-- Sezione per creare una nuova aula -->
        <h2>Aggiungi Nuova Aula</h2>
        <form method="POST">
            <label for="nome">Nome Aula:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="capacita">Capacità:</label>
            <input type="number" id="capacita" name="capacita" required min="0" step="any">

            <label for="tipologia">Tipologia:</label>
            <select id="tipologia" name="tipologia" required>
                <option value="teorica">Teorica</option>
                <option value="laboratorio">Laboratorio</option>
            </select>

            <label for="indirizzo">Indirizzo:</label>
            <select id="indirizzo" name="indirizzo" required onchange="caricaEdifici()">
                <option value="">Seleziona un indirizzo</option>
                <?php foreach ($indirizzi as $indirizzo): ?>
                    <option value="<?php echo htmlspecialchars($indirizzo); ?>">
                        <?php echo htmlspecialchars($indirizzo); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edificio">Edificio:</label>
            <select id="edificio" name="edificio" required>
                <option value="">Seleziona un edificio</option>
            </select>

            <button type="submit" name="submit_creazione">Aggiungi</button>
        </form>

        <?php if (isset($aulaInModifica)): ?>
            <!-- Modulo di modifica aula -->
            <h2>Modifica Aula</h2>
            <form method="POST">
                <input type="hidden" name="id_aula" value="<?php echo htmlspecialchars($aulaInModifica['ID_Aula']); ?>">

                <label for="nome">Nome Aula:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($aulaInModifica['Nome']); ?>"
                    required>

                <label for="capacita">Capacità:</label>
                <input type="number" id="capacita" name="capacita"
                    value="<?php echo htmlspecialchars($aulaInModifica['Capacita']); ?>" required min="0" step="any">

                <label for="tipologia">Tipologia:</label>
                <select id="tipologia" name="tipologia" required>
                    <option value="teorica" <?php echo ($aulaInModifica['Tipologia'] === 'teorica') ? 'selected' : ''; ?>>
                        Teorica</option>
                    <option value="laboratorio" <?php echo ($aulaInModifica['Tipologia'] === 'laboratorio') ? 'selected' : ''; ?>>Laboratorio</option>
                </select>

                <label for="indirizzo">Indirizzo:</label>
                <select id="indirizzo" name="indirizzo" required onchange="caricaEdifici()">
                    <option value="">Seleziona un indirizzo</option>
                    <?php foreach ($indirizzi as $indirizzo): ?>
                        <option value="<?php echo htmlspecialchars($indirizzo); ?>" <?php echo ($aulaInModifica['Indirizzo'] === $indirizzo) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($indirizzo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="edificio">Edificio:</label>
                <select id="edificio" name="edificio" required>
                    <option value="">Seleziona un edificio</option>
                </select>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Tabella delle aule -->
            <h2>Lista delle Aule</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Capacità</th>
                        <th>Tipologia</th>
                        <th>Edificio</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($aule) && count($aule) > 0): ?>
                        <?php foreach ($aule as $aula): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($aula['ID_Aula']); ?></td>
                                <td><?php echo htmlspecialchars($aula['Nome']); ?></td>
                                <td><?php echo htmlspecialchars($aula['Capacita']); ?></td>
                                <td><?php echo htmlspecialchars($aula['Tipologia']); ?></td>
                                <td><?php echo htmlspecialchars($aula['Edificio']); ?></td>
                                <td>
                                    <a
                                        href="aula.php?edit=<?php echo htmlspecialchars($aula['ID_Aula']); ?>"><button>Modifica</button></a>
                                    <a href="aula.php?delete=<?php echo htmlspecialchars($aula['ID_Aula']); ?>"
                                        onclick="return confirm('Sei sicuro di voler eliminare questa aula?');"><button
                                            class="delete">Rimuovi</button></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Nessuna aula trovata.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>