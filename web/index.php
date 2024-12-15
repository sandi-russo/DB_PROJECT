<?php
try {
    $servername = "db";
    $username = "utente";
    $password = "password";
    $dbname = "nome_database";

    // Crea connessione con gestione degli errori
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Controlla la connessione
    if ($conn->connect_error) {
        throw new Exception("Connessione fallita: " . $conn->connect_error);
    }

    echo "Connessione al database avvenuta con successo!";
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Errore di connessione: " . $e->getMessage());
}
?>