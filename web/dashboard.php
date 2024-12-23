<?php

// Avvia la sessione
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['nome']) || !isset($_SESSION['cognome'])) {
    // Se non è loggato, reindirizza al login
    header("Location: login.php");
    exit();
}

// Recupera il nome e cognome dell'amministratore dalla sessione
$nome = $_SESSION['nome'];
$cognome = $_SESSION['cognome'];
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <div class="nav-container">
        <h1>Ciao, <?php echo htmlspecialchars($nome) . " " . htmlspecialchars($cognome); ?>!</h1>
        <p>Benvenuto nella tua dashboard, amministratore.</p>
        <a href="logout.php">Logout</a>
    </div>

    <ul>
        <li><a href="docente.php">Gestisci Docenti</a></li>
        <li><a href="aula.php">Gestisci Aule</a></li>
        <li><a href="edificio.php">Gestisci Edifici</a></li>
        <!-- Aggiungi altri link per gestire altre risorse -->
    </ul>
</body>

</html>