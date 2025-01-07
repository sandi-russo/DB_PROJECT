<?php
require_once 'functions.php';

if (isset($_GET['indirizzo'])) {
    $indirizzo = $_GET['indirizzo'];
    $conn = ApriConnessione();

    $stmt = $conn->prepare("SELECT ID_Edificio, Nome FROM EDIFICIO WHERE Indirizzo = ?");
    $stmt->bind_param("s", $indirizzo);
    $stmt->execute();
    $result = $stmt->get_result();

    $edifici = [];
    while ($row = $result->fetch_assoc()) {
        $edifici[] = $row;
    }

    $stmt->close();
    ChiudiConnessione($conn);

    header('Content-Type: application/json');
    echo json_encode($edifici);
}
?>