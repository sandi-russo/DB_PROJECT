<?php
// dashboard.php

require_once 'auth.php'; // Assicurati che l'utente sia autenticato
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

    <ul>
        <li><a href="docente.php">Gestisci Docenti</a></li>
        <li><a href="aula.php">Gestisci Aule</a></li>
        <li><a href="edificio.php">Gestisci Edifici</a></li>
        <li><a href="crea_amministratore.php">Crea Nuovo Amministratore</a></li>
    </ul>
</body>
</html>
