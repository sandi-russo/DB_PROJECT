<?php
require_once 'functions.php';
require_once 'auth.php';

// Recupera tutti i docenti
$docenti = OttieniDocenti();
$settori = OttieniSettoriScientifici(true); // Solo SSD
$unitaOrganizzative = OttieniUnitaOrganizzative(true); // Solo Codice e Nome
$ruoli = OttieniValoriRuolo(); // Recupera i valori della colonna Ruolo

// Se è stato inviato un codice per la modifica
if (isset($_GET['edit'])) {
    $idDocente = $_GET['edit'];
    foreach ($docenti as $docente) {
        if ($docente['ID_Docente'] === $idDocente) {
            $docenteInModifica = $docente;
            break;
        }
    }
}

// Gestione del modulo di modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    $idDocente = $_POST['id_docente'];
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $ssd = trim($_POST['ssd']);
    $unitaOrganizzativa = trim($_POST['unita_organizzativa']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $ruolo = trim($_POST['ruolo']);
    $oreTotali = OttieniOreMassimePerRuolo($ruolo);
    $maxOreConsentite = $oreTotali;

    $message = ModificaDocente($idDocente, $nome, $cognome, $ssd, $unitaOrganizzativa, $email, $telefono, $ruolo);
    // Aggiungi la gestione per la disponibilità e il carico di lavoro
    header("Location: docente.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $ssd = trim($_POST['ssd']);
    $unitaOrganizzativa = trim($_POST['unita_organizzativa']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $ruolo = trim($_POST['ruolo']);
    $oreTotali = OttieniOreMassimePerRuolo($ruolo);
    $maxOreConsentite = $oreTotali;

    $message = InserisciDocente($nome, $cognome, $ssd, $unitaOrganizzativa, $email, $telefono, $ruolo);
    // Aggiungi la gestione per la disponibilità e il carico di lavoro
    header("Location: docente.php?message=" . urlencode($message));
    exit;
}

// Gestione del modulo di eliminazione
if (isset($_GET['delete'])) {
    $idDocente = $_GET['delete'];

    $message = RimuoviDocente($idDocente);
    header("Location: docente.php?message=" . urlencode($message));
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
    <title>Gestione Docenti</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <script>
        function toggleDisponibilita() {
            var checkbox = document.getElementById('disponibilita_checkbox');
            var disponibilitaFields = document.getElementById('disponibilita_fields');
            if (checkbox.checked) {
                disponibilitaFields.style.display = 'none';
            } else {
                disponibilitaFields.style.display = 'block';
            }
        }

        window.onload = function () {
            toggleDisponibilita();
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Gestione Docenti</h1>

        <?php if (isset($docenteInModifica)): ?>
            <!-- Modulo di modifica docente -->
            <h2>Modifica Docente</h2>
            <form method="POST">
                <input type="hidden" name="id_docente"
                    value="<?php echo htmlspecialchars($docenteInModifica['ID_Docente']); ?>">

                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($docenteInModifica['Nome']); ?>"
                    required>

                <label for="cognome">Cognome:</label>
                <input type="text" id="cognome" name="cognome"
                    value="<?php echo htmlspecialchars($docenteInModifica['Cognome']); ?>" required>

                <label for="ssd">Settore Scientifico Disciplinare (SSD):</label>
                <select id="ssd" name="ssd" required>
                    <option value="">Seleziona SSD</option>
                    <?php foreach ($settori as $settore): ?>
                        <option value="<?php echo htmlspecialchars($settore['SSD']); ?>" <?php if ($docenteInModifica['SSD'] === $settore['SSD'])
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($settore['SSD']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="unita_organizzativa">Unità Organizzativa:</label>
                <select id="unita_organizzativa" name="unita_organizzativa" required>
                    <option value="">Seleziona Unità Organizzativa</option>
                    <?php foreach ($unitaOrganizzative as $unita): ?>
                        <option value="<?php echo htmlspecialchars($unita['Codice']); ?>" <?php if ($docenteInModifica['UnitaOrganizzativa'] === $unita['Codice'])
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($unita['Nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email"
                    value="<?php echo htmlspecialchars($docenteInModifica['Email']); ?>" required>

                <label for="telefono">Telefono:</label>
                <input type="text" id="telefono" name="telefono"
                    value="<?php echo htmlspecialchars($docenteInModifica['Telefono']); ?>">

                <label for="ruolo">Ruolo:</label>
                <select id="ruolo" name="ruolo" required>
                    <option value="">Seleziona Ruolo</option>
                    <?php foreach ($ruoli as $ruolo): ?>
                        <option value="<?php echo htmlspecialchars($ruolo); ?>" <?php if ($docenteInModifica['Ruolo'] === $ruolo)
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($ruolo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <h3>Disponibilità</h3>
                <label for="disponibilita_checkbox">
                    <input type="checkbox" id="disponibilita_checkbox" name="disponibilita_checkbox"
                        onclick="toggleDisponibilita()" checked>
                    Disponibile dal lunedì al venerdì dalle 09:00 alle 18:00
                </label>

                <div id="disponibilita_fields" style="display: none;">
                    <?php
                    $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi'];
                    foreach ($giorni as $giorno): ?>
                        <div style="display: flex; align-items: center;">
                            <label for="ora_inizio_<?php echo $giorno; ?>"><?php echo ucfirst($giorno); ?>:</label>
                            <input type="time" id="ora_inizio_<?php echo $giorno; ?>" name="ora_inizio_<?php echo $giorno; ?>"
                                min="09:00" max="16:00">
                            <label for="ora_fine_<?php echo $giorno; ?>">Ora Fine:</label>
                            <input type="time" id="ora_fine_<?php echo $giorno; ?>" name="ora_fine_<?php echo $giorno; ?>"
                                min="11:00" max="18:00">
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <!-- Modulo di creazione docente -->
            <h2>Aggiungi Nuovo Docente</h2>
            <form method="POST">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="cognome">Cognome:</label>
                <input type="text" id="cognome" name="cognome" required>

                <label for="ssd">Settore Scientifico Disciplinare (SSD):</label>
                <select id="ssd" name="ssd" required>
                    <option value="">Seleziona SSD</option>
                    <?php foreach ($settori as $settore): ?>
                        <option value="<?php echo htmlspecialchars($settore['SSD']); ?>">
                            <?php echo htmlspecialchars($settore['SSD']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="unita_organizzativa">Unità Organizzativa:</label>
                <select id="unita_organizzativa" name="unita_organizzativa" required>
                    <option value="">Seleziona Unità Organizzativa</option>
                    <?php foreach ($unitaOrganizzative as $unita): ?>
                        <option value="<?php echo htmlspecialchars($unita['Codice']); ?>">
                            <?php echo htmlspecialchars($unita['Nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="telefono">Telefono:</label>
                <input type="text" id="telefono" name="telefono">

                <label for="ruolo">Ruolo:</label>
                <select id="ruolo" name="ruolo" required>
                    <option value="">Seleziona Ruolo</option>
                    <?php foreach ($ruoli as $ruolo): ?>
                        <option value="<?php echo htmlspecialchars($ruolo); ?>">
                            <?php echo htmlspecialchars($ruolo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <h3>Disponibilità</h3>
                <label for="disponibilita_checkbox">
                    <input type="checkbox" id="disponibilita_checkbox" name="disponibilita_checkbox"
                        onclick="toggleDisponibilita()" checked>
                    Disponibile dal lunedì al venerdì dalle 09:00 alle 18:00
                </label>

                <div id="disponibilita_fields" style="display: none;">
                    <?php
                    $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi'];
                    foreach ($giorni as $giorno): ?>
                        <div style="display: flex; align-items: center;">
                            <label for="ora_inizio_<?php echo $giorno; ?>"><?php echo ucfirst($giorno); ?>:</label>
                            <input type="time" id="ora_inizio_<?php echo $giorno; ?>" name="ora_inizio_<?php echo $giorno; ?>"
                                min="09:00" max="16:00">
                            <label for="ora_fine_<?php echo $giorno; ?>">Ora Fine:</label>
                            <input type="time" id="ora_fine_<?php echo $giorno; ?>" name="ora_fine_<?php echo $giorno; ?>"
                                min="11:00" max="18:00">
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" name="submit_creazione">Aggiungi</button>
            </form>
        <?php endif; ?>

        <!-- Lista dei docenti -->
        <h2>Lista dei Docenti</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>SSD</th>
                    <th>Unità Organizzativa</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Ruolo</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($docenti) && count($docenti) > 0): ?>
                    <?php foreach ($docenti as $docente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($docente['ID_Docente']); ?></td>
                            <td><?php echo htmlspecialchars($docente['Nome']); ?></td>
                            <td><?php echo htmlspecialchars($docente['Cognome']); ?></td>
                            <td><?php echo htmlspecialchars($docente['SSD']); ?></td>
                            <td><?php echo htmlspecialchars($docente['UnitaOrganizzativa']); ?></td>
                            <td><?php echo htmlspecialchars($docente['Email']); ?></td>
                            <td><?php echo htmlspecialchars($docente['Telefono']); ?></td>
                            <td><?php echo htmlspecialchars($docente['Ruolo']); ?></td>
                            <td>
                                <a
                                    href="docente.php?edit=<?php echo htmlspecialchars($docente['ID_Docente']); ?>"><button>Modifica</button></a>
                                <a href="docente.php?delete=<?php echo htmlspecialchars($docente['ID_Docente']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questo docente?');"><button
                                        class="delete">Rimuovi</button></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Nessun docente trovato.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>