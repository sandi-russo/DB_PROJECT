<?php
session_start();

// Disconnessione automatica dopo un certo periodo di inattività (es. 15 minuti)
$tempoInattivita = 15 * 60; // 15 minuti in secondi
if (isset($_SESSION['ultimo_accesso']) && (time() - $_SESSION['ultimo_accesso'] > $tempoInattivita)) {
    // Distruggi la sessione e reindirizza al login
    session_unset();
    session_destroy();
    header("Location: login.php?messaggio=Sessione scaduta per inattività");
    exit();
}
// Aggiorna il timestamp dell'ultimo accesso
$_SESSION['ultimo_accesso'] = time();

// Verifica se l'utente è loggato
if (!isset($_SESSION['nome']) || !isset($_SESSION['cognome'])) {
    header("Location: login.php");
    exit();
}
?>
