<?php
require_once 'functions.php';

// Avvia la sessione
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (LoginAmministratore($email, $password)) {
        // Login effettuato con successo, redirect alla dashboard
        header('Location: dashboard.php'); // Reindirizza alla pagina della dashboard
        exit;
    } else {
        // Credenziali errate
        $error = "Email o password errati.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Amministratore</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>
<body>
    <form method="post" action="login.php">
        <h2>Login Amministratore</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
