<?php
require_once 'functions.php';

// Avvia la sessione o richiama altre risorse se necessario
session_start();

// Inizializza variabili per il feedback
$message = '';
$success = false;

// Controlla se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati dal form
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Chiama la funzione per creare l'amministratore
    $result = CreaAmministratore($nome, $cognome, $email, $password);

    // Aggiorna il messaggio e il flag di successo
    $message = $result['message'];
    $success = $result['success'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Amministratore</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
    <!-- Include Boxicons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <form method="post" action="crea_amministratore.php">
        <h2>Crea Amministratore</h2>

        <?php if (!empty($message)): ?>
            <p style="color: <?php echo $success ? 'green' : 'red'; ?>;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>Cognome:</label>
        <input type="text" name="cognome" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <div class="password-container">
            <input type="password" name="password" id="password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility()">
                <i class="bx bx-show" id="eye-icon"></i>
            </span>
        </div>
        <p class="password-hint">
            La password deve contenere almeno 10 caratteri, una lettera maiuscola, una minuscola, un numero e un carattere speciale.
        </p>

        <button type="submit">Crea Amministratore</button>
    </form>
</body>
</html>
