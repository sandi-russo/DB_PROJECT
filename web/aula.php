<?php
require_once 'functions.php';
require_once 'auth.php';

// Recupera aule, edifici e tipologie
$aule = OttieniAule();
$edifici = OttieniEdifici();
$tipologie = OttieniTipologieAula();

// Costruisce elenco indirizzi unici
$indirizzi = array_unique(array_map(function ($ed) {
    return $ed['Indirizzo'];
}, $edifici));

// Mappa indirizzo -> elenco di (ID_Edificio, Nome)
$edificiMap = [];
foreach ($edifici as $ed) {
    $edificiMap[$ed['Indirizzo']][] = [
        'id' => $ed['ID_Edificio'],
        'nome' => $ed['Nome']
    ];
}

// Se edit
if (isset($_GET['edit'])) {
    $idAula = $_GET['edit'];
    foreach ($aule as $aula) {
        if ($aula['ID_Aula'] === $idAula) {
            $aulaInModifica = $aula;
            break;
        }
    }
}

// Modifica
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modifica'])) {
    $idAula = $_POST['id_aula'];
    $nome = trim($_POST['nome']);
    $capacita = intval($_POST['capacita']);
    $tipologia = trim($_POST['tipologia']);
    $edificio = $_POST['edificio'];
    $attrezzature = trim($_POST['attrezzature']);

    $message = ModificaAula($idAula, $nome, $capacita, $tipologia, $edificio, $attrezzature);
    header("Location: aula.php?message=" . urlencode($message));
    exit;
}

// Creazione
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_creazione'])) {
    $nome = trim($_POST['nome']);
    $capacita = intval($_POST['capacita']);
    $tipologia = trim($_POST['tipologia']);
    $edificio = $_POST['edificio'];
    $attrezzature = trim($_POST['attrezzature']);

    $message = InserisciAula($nome, $capacita, $tipologia, $edificio, $attrezzature);
    header("Location: aula.php?message=" . urlencode($message));
    exit;
}

// Eliminazione
if (isset($_GET['delete'])) {
    $idAula = $_GET['delete'];
    $message = RimuoviAula($idAula);
    header("Location: aula.php?message=" . urlencode($message));
    exit;
}

// Messaggi di esito
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
</head>

<body>
    <div class="container">
        <h1>Gestione Aule</h1>

        <?php if (isset($aulaInModifica)): ?>
            <h2>Modifica Aula</h2>
            <form method="POST">
                <input type="hidden" name="id_aula" value="<?php echo htmlspecialchars($aulaInModifica['ID_Aula']); ?>">

                <label for="nome">Nome Aula:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($aulaInModifica['Nome']); ?>"
                    required>

                <label for="capacita">Capacità:</label>
                <input type="number" id="capacita" name="capacita"
                    value="<?php echo htmlspecialchars($aulaInModifica['Capacita']); ?>" required min="0" step="1">

                <label for="tipologia">Tipologia:</label>
                <select name="tipologia" required>
                    <option value="">Seleziona Tipologia</option>
                    <?php foreach ($tipologie as $val): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>" <?php if ($aulaInModifica['Tipologia'] === $val)
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($val); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Indirizzo -->
                <label for="indirizzo">Indirizzo:</label>
                <select id="indirizzo">
                    <option value="">Seleziona Indirizzo</option>
                    <?php foreach ($indirizzi as $ind): ?>
                        <option value="<?php echo htmlspecialchars($ind); ?>" <?php
                           // Se l'indirizzo dell'edificio corrisponde a quello di $aulaInModifica, selezioniamo
                           foreach ($edifici as $ed) {
                               if ($ed['ID_Edificio'] == $aulaInModifica['Edificio'] && $ed['Indirizzo'] == $ind) {
                                   echo 'selected';
                                   break;
                               }
                           }
                           ?>>
                            <?php echo htmlspecialchars($ind); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Edificio -->
                <label for="edificio">Edificio:</label>
                <select id="edificio" name="edificio" required>
                    <option value="">Seleziona Edificio</option>
                    <?php
                    // Pre-popola solo gli edifici che corrispondono all'indirizzo selezionato
                    foreach ($edifici as $ed) {
                        // se l'edificio coincide con quello in modifica
                        $selected = ($aulaInModifica['Edificio'] == $ed['ID_Edificio']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($ed['ID_Edificio']) . '" ' . $selected . '>'
                            . htmlspecialchars($ed['Nome']) . '</option>';
                    }
                    ?>
                </select>

                <label for="attrezzature">Attrezzature:</label>
                <input type="text" id="attrezzature" name="attrezzature"
                    value="<?php echo htmlspecialchars($aulaInModifica['Attrezzature']); ?>" required>

                <button type="submit" name="submit_modifica">Salva modifiche</button>
            </form>
        <?php else: ?>
            <h2>Aggiungi Nuova Aula</h2>
            <form method="POST">
                <label for="nome">Nome Aula:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="capacita">Capacità:</label>
                <input type="number" id="capacita" name="capacita" required min="0" step="1">

                <label for="tipologia">Tipologia:</label>
                <select name="tipologia" required>
                    <option value="">Seleziona Tipologia</option>
                    <?php foreach ($tipologie as $val): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>">
                            <?php echo htmlspecialchars($val); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Indirizzo -->
                <label for="indirizzo">Indirizzo:</label>
                <select id="indirizzo">
                    <option value="">Seleziona Indirizzo</option>
                    <?php foreach ($indirizzi as $ind): ?>
                        <option value="<?php echo htmlspecialchars($ind); ?>">
                            <?php echo htmlspecialchars($ind); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Edificio -->
                <label for="edificio">Edificio:</label>
                <select id="edificio" name="edificio" required>
                    <option value="">Seleziona Edificio</option>
                </select>

                <label for="attrezzature">Attrezzature:</label>
                <input type="text" id="attrezzature" name="attrezzature" required>

                <button type="submit" name="submit_creazione">Aggiungi</button>
            </form>
        <?php endif; ?>

        <h2>Lista delle Aule</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Capacità</th>
                    <th>Tipologia</th>
                    <th>Edificio</th>
                    <th>Attrezzature</th>
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
                            <td>
                                <?php
                                foreach ($edifici as $edOption) {
                                    if ($edOption['ID_Edificio'] == $aula['Edificio']) {
                                        echo htmlspecialchars($edOption['Nome']) . " - " . htmlspecialchars($edOption['Indirizzo']);
                                        break;
                                    }
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($aula['Attrezzature']); ?></td>
                            <td>
                                <a href="aula.php?edit=<?php echo htmlspecialchars($aula['ID_Aula']); ?>">
                                    <button>Modifica</button></a>
                                <a href="aula.php?delete=<?php echo htmlspecialchars($aula['ID_Aula']); ?>"
                                    onclick="return confirm('Sei sicuro di voler eliminare questa aula?');">
                                    <button class="delete">Rimuovi</button></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nessuna aula trovata.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Mappa indirizzo -> array di edifici
        window.edificiData = <?php echo json_encode($edificiMap); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const indirizzoSelect = document.getElementById('indirizzo');
            const edificioSelect = document.getElementById('edificio');
            if (!indirizzoSelect || !edificioSelect || !window.edificiData) return;

            indirizzoSelect.addEventListener('change', function () {
                edificioSelect.innerHTML = '<option value="">Seleziona Edificio</option>';
                const addr = this.value;
                if (addr && window.edificiData[addr]) {
                    window.edificiData[addr].forEach(ed => {
                        const opt = document.createElement('option');
                        opt.value = ed.id;
                        opt.textContent = ed.nome;
                        edificioSelect.appendChild(opt);
                    });
                }
            });
        });
    </script>
</body>

</html>