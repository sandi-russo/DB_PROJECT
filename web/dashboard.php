<?php
// dashboard.php

require_once 'auth.php'; // Assicurati che l'utente sia autenticato

// Funzione per generare un nome leggibile da un nome file
function formattaNomeFile($filename) {
    $filename = basename($filename, '.php'); // Rimuove l'estensione .php
    $filename = str_replace('_', ' ', $filename); // Sostituisce underscore con spazi
    return ucwords($filename); // Trasforma in maiuscolo le parole
}

// Ottieni tutti i file .php nella directory corrente
$files = glob("*.php");

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>
<body>
    <div class="nav-container">
        <h1>Ciao, <?php echo htmlspecialchars($_SESSION['nome']) . " " . htmlspecialchars($_SESSION['cognome']); ?>!</h1>
        <p>Benvenuto nella tua dashboard, amministratore.</p>
        <a href="logout.php">Logout</a>
    </div>

    <h2>Gestione Risorse</h2>
    <ul>
        <?php foreach ($files as $file): ?>
            <?php if ($file !== 'dashboard.php' && $file !== 'auth.php' && $file !== 'logout.php'): ?>
                <li><a href="<?php echo htmlspecialchars($file); ?>">
                    <?php echo htmlspecialchars(formattaNomeFile($file)); ?>
                </a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</body>
</html>
