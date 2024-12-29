<?php

// Avvia la sessione
session_start();

// Distruggi tutte le variabili di sessione
session_unset();

// Distruggi la sessione
session_destroy();

// Reindirizza al login
header("Location: login.php?messaggio=Sei stato disconnesso con successo");
exit();
?>
