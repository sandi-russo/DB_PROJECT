<?php
include('functions.php');
require_once 'auth.php';

// Aggiungi una aula
if (isset($_POST['AggiungiAula'])) {
    $nome = $_POST['nome'];
    $capacita = $_POST['capacita'];
    $tipologia = $_POST['tipologia'];
    $edificio = $_POST['edificio'];
    
    $risultato = AggiungiAula($nome, $capacita, $tipologia, $edificio);
    echo $risultato;
}

// Modifica una aula
if (isset($_POST['modificaAula'])) {
    $idAula = $_POST['idAula'];
    $nome = $_POST['nome'];
    $capacita = $_POST['capacita'];
    $tipologia = $_POST['tipologia'];
    
    $risultato = ModificaAula($idAula, $nome, $capacita, $tipologia);
    echo $risultato;
}

// Rimuovi una aula
if (isset($_POST['rimuoviAula'])) {
    $idAula = $_POST['idAula'];
    $risultato = RimuoviAula($idAula);
    echo $risultato;
}
?>

<!-- Form per aggiungere una aula -->
<form method="POST" action="">
    <input type="text" name="nome" placeholder="Nome Aula" required>
    <input type="number" name="capacita" placeholder="Capacità" required>
    <select name="tipologia">
        <option value="teorica">Teorica</option>
        <option value="laboratorio">Laboratorio</option>
    </select>
    <input type="text" name="edificio" placeholder="ID Edificio" required>
    <button type="submit" name="aggiungiAula">Aggiungi Aula</button>
</form>

<!-- Form per modificare una aula -->
<form method="POST" action="">
    <input type="number" name="idAula" placeholder="ID Aula" required>
    <input type="text" name="nome" placeholder="Nome Aula" required>
    <input type="number" name="capacita" placeholder="Capacità" required>
    <select name="tipologia">
        <option value="teorica">Teorica</option>
        <option value="laboratorio">Laboratorio</option>
    </select>
    <button type="submit" name="modificaAula">Modifica Aula</button>
</form>

<!-- Form per rimuovere una aula -->
<form method="POST" action="">
    <input type="number" name="idAula" placeholder="ID Aula" required>
    <button type="submit" name="rimuoviAula">Rimuovi Aula</button>
</form>
