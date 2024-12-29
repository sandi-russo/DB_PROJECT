<?php

// APERTURA CONNESSIONE DATABASE
function ApriConnessione()
{
  $servername = "db";
  $username = "root";
  $password = "password";
  $dbname = "progetto_db";

  // Creazione della connessione
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Controllo errori
  if ($conn->connect_error) {
    throw new Exception("Connessione fallita: " . $conn->connect_error);
  }

  return $conn;
}

// CHIUSURA CONNESSIONE DATABASE
function ChiudiConnessione($conn)
{
  if ($conn) {
    $conn->close();
  }
}

// FUNZIONE DI LOGIN PER GLI AMMINISTRATORI
function LoginAmministratore($email, $password)
{
  try {
    // Apri connessione
    $conn = ApriConnessione();

    // Prepara la query per recuperare nome, cognome e password
    $stmt = $conn->prepare("SELECT Nome, Cognome, Password FROM AMMINISTRATORE WHERE Email = ?");
    if (!$stmt) {
      // Gestisci l'errore se la query non è preparata correttamente
      ChiudiConnessione($conn);
      return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Verifica se l'email esiste
    if ($stmt->num_rows === 0) {
      $stmt->close();
      ChiudiConnessione($conn);
      return false; // Email non trovata
    }

    // Recupera il nome, cognome e la password hashata
    $stmt->bind_result($nome, $cognome, $hashedPassword);
    $stmt->fetch();

    // Hasha la password inserita dall'utente
    $hashedInputPassword = hash("sha256", $password);

    // Verifica la corrispondenza delle password
    if ($hashedInputPassword === $hashedPassword) {
      // Salva il nome e cognome dell'amministratore nella sessione
      $_SESSION['nome'] = $nome;
      $_SESSION['cognome'] = $cognome;

      // Chiudi risorse e connessione
      $stmt->close();
      ChiudiConnessione($conn);

      return true; // Login riuscito
    }

    // Password errata
    $stmt->close();
    ChiudiConnessione($conn);
    return false;

  } catch (Exception $e) {
    return false; // In caso di errore, il login fallisce
  }
}

// Funzione per validare la password
function PasswordValida($password)
{
  // La password deve contenere almeno 10 caratteri, una lettera maiuscola, una minuscola, un numero e un carattere speciale
  return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $password);
}

// Funzione per creare un nuovo amministratore
function CreaAmministratore($nome, $cognome, $email, $password)
{
  try {
    // Verifica se l'email è già presente nel database
    $conn = ApriConnessione();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM AMMINISTRATORE WHERE Email = ?");
    if (!$stmt) {
      ChiudiConnessione($conn);
      return ['success' => false, 'message' => "Errore nella preparazione della query di controllo email."];
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
      ChiudiConnessione($conn);
      return ['success' => false, 'message' => "L'email inserita è già in uso."];
    }

    // Verifica che la password sia valida
    if (!PasswordValida($password)) {
      ChiudiConnessione($conn);
      return ['success' => false, 'message' => "La password non soddisfa i requisiti. Deve contenere almeno 10 caratteri, una lettera maiuscola, una minuscola, un numero e un carattere speciale."];
    }

    // Uppercase e trim del nome per l'ID (opzionale, lasciato come esempio)
    $nomeUpper = strtoupper(trim($nome));

    // Hasha la password con SHA256
    $hashedPassword = hash("sha256", $password);

    // Prepara la query di inserimento
    $stmt = $conn->prepare("INSERT INTO AMMINISTRATORE (Nome, Cognome, Email, Password) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
      ChiudiConnessione($conn);
      return ['success' => false, 'message' => "Errore nella preparazione della query di inserimento."];
    }

    $stmt->bind_param("ssss", $nome, $cognome, $email, $hashedPassword);

    // Esegui la query
    if ($stmt->execute()) {
      $stmt->close();
      ChiudiConnessione($conn);
      return ['success' => true, 'message' => "Amministratore aggiunto con successo."];
    } else {
      $stmt->close();
      ChiudiConnessione($conn);
      return ['success' => false, 'message' => "Errore nell'inserimento dell'amministratore."];
    }

  } catch (Exception $e) {
    return ['success' => false, 'message' => "Errore: " . $e->getMessage()];
  }
}



// FUNZIONE PER LA CREAZIONE DELLE TABELLE NEL DATABASE
function CreaTabelle($conn)
{
  try {
    // Apri la connessione al database
    $conn = ApriConnessione();

    echo "Connessione al database avvenuta con successo!<br>";

    $queries = [
      // Tabella UNITA_ORGANIZZATIVA
      "CREATE TABLE IF NOT EXISTS UNITA_ORGANIZZATIVA (
            Codice VARCHAR(20) PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL
        )",

      // Tabella SETTORE_SCIENTIFICO
      "CREATE TABLE IF NOT EXISTS SETTORE_SCIENTIFICO (
            SSD VARCHAR(20) PRIMARY KEY,
            NomeSettore VARCHAR(100) NOT NULL
        )",

      // Tabella DOCENTE
      "CREATE TABLE IF NOT EXISTS DOCENTE (
            ID_Docente INT AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(50) NOT NULL,
            Cognome VARCHAR(50) NOT NULL,
            SSD VARCHAR(10) NOT NULL,
            UnitaOrganizzativa VARCHAR(255) NOT NULL,
            FOREIGN KEY (SSD) REFERENCES SETTORE_SCIENTIFICO(SSD),
            FOREIGN KEY (UnitaOrganizzativa) REFERENCES UNITA_ORGANIZZATIVA(Codice)
        )",

      // Tabella EDIFICIO
      "CREATE TABLE IF NOT EXISTS EDIFICIO (
            ID_Edificio VARCHAR(40) PRIMARY KEY,
            Nome VARCHAR(100) NOT NULL,
            Indirizzo VARCHAR(200) NOT NULL,
            CapacitaTotale INT NOT NULL CHECK (CapacitaTotale > 0)
        )",

      // Tabella AULA
      "CREATE TABLE IF NOT EXISTS AULA (
            ID_Aula INT AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(50) NOT NULL,
            Capacita INT NOT NULL CHECK (Capacita > 0),
            Tipologia ENUM('teorica', 'laboratorio') NOT NULL,
            Edificio VARCHAR(40),
            FOREIGN KEY (Edificio) REFERENCES EDIFICIO(ID_Edificio)
        )",

      // Tabella CORSO_DI_STUDIO
      "CREATE TABLE IF NOT EXISTS CORSO_DI_STUDIO (
            Codice VARCHAR(25) PRIMARY KEY,
            Nome VARCHAR(100) NOT NULL,
            Percorso VARCHAR(100),
            AnnoCorso INT NOT NULL
        )",

      // Tabella LINGUE
      "CREATE TABLE IF NOT EXISTS LINGUE (
            CodiceLingua VARCHAR(5) PRIMARY KEY,
            NomeLingua VARCHAR(50) NOT NULL
        )",

      // Tabella PERIODO
      "CREATE TABLE IF NOT EXISTS PERIODO (
            Periodo VARCHAR(50) PRIMARY KEY DEFAULT 'Ciclo annuale',
            DataInizio DATE NOT NULL,
            DataFine DATE NOT NULL
        )",

      // Tabella INSEGNAMENTO
      "CREATE TABLE IF NOT EXISTS INSEGNAMENTO (
            Codice VARCHAR(10) PRIMARY KEY,
            Nome VARCHAR(100) NOT NULL,
            AnnoOfferta INT NOT NULL,
            CFU INT NOT NULL CHECK (CFU > 0),
            Lingua VARCHAR(5) NOT NULL,
            SSD VARCHAR(10) NOT NULL,
            Descrizione TEXT,
            MetodoEsame TEXT,
            DocenteTitolare INT,
            CorsoDiStudio VARCHAR(10),
            FOREIGN KEY (Lingua) REFERENCES LINGUE(CodiceLingua),
            FOREIGN KEY (SSD) REFERENCES SETTORE_SCIENTIFICO(SSD),
            FOREIGN KEY (DocenteTitolare) REFERENCES DOCENTE(ID_Docente),
            FOREIGN KEY (CorsoDiStudio) REFERENCES CORSO_DI_STUDIO(Codice)
        )",

      // Tabella DISPONIBILITA_DOCENTE
      "CREATE TABLE IF NOT EXISTS DISPONIBILITA_DOCENTE (
            ID_Docente INT NOT NULL,
            Giorno ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi') NOT NULL,
            OraInizio TIME NOT NULL,
            OraFine TIME NOT NULL,
            FOREIGN KEY (ID_Docente) REFERENCES DOCENTE(ID_Docente)
        )",

      // Tabella DISPONIBILITA_AULA
      "CREATE TABLE IF NOT EXISTS DISPONIBILITA_AULA (
            ID_Aula INT NOT NULL,
            Giorno ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi') NOT NULL,
            OraInizio TIME NOT NULL,
            OraFine TIME NOT NULL,
            TipologiaUtilizzo ENUM('lezione', 'laboratorio', 'esame') NOT NULL,
            FOREIGN KEY (ID_Aula) REFERENCES AULA(ID_Aula)
        )",

      // Tabella ORARIO
      "CREATE TABLE IF NOT EXISTS ORARIO (
            ID_Orario INT AUTO_INCREMENT PRIMARY KEY,
            Giorno DATE NOT NULL,
            OraInizio TIME NOT NULL,
            OraFine TIME NOT NULL,
            Aula INT NOT NULL,
            Insegnamento VARCHAR(10) NOT NULL,
            Docente INT NOT NULL,
            FOREIGN KEY (Aula) REFERENCES AULA(ID_Aula),
            FOREIGN KEY (Insegnamento) REFERENCES INSEGNAMENTO(Codice),
            FOREIGN KEY (Docente) REFERENCES DOCENTE(ID_Docente)
        )",

      // Tabella GRUPPO_STUDENTI
      "CREATE TABLE IF NOT EXISTS GRUPPO_STUDENTI (
            ID_Gruppo INT AUTO_INCREMENT PRIMARY KEY,
            CodiceCorsoDiStudio VARCHAR(10) NOT NULL,
            AnnoCorso INT NOT NULL,
            NumeroStudenti INT NOT NULL CHECK (NumeroStudenti > 0),
            FOREIGN KEY (CodiceCorsoDiStudio) REFERENCES CORSO_DI_STUDIO(Codice)
        )",

      // Tabella CARICO_LAVORO_DOCENTE
      "CREATE TABLE IF NOT EXISTS CARICO_LAVORO_DOCENTE (
            ID_Docente INT NOT NULL,
            Periodo VARCHAR(10) NOT NULL,
            OreTotali INT NOT NULL CHECK (OreTotali >= 0),
            MaxOreConsentite INT NOT NULL CHECK (MaxOreConsentite > 0),
            FOREIGN KEY (ID_Docente) REFERENCES DOCENTE(ID_Docente),
            FOREIGN KEY (Periodo) REFERENCES PERIODO(Periodo)
        )",

      // Tabella ORARIO_STORICO
      "CREATE TABLE IF NOT EXISTS ORARIO_STORICO (
            ID_Storico INT AUTO_INCREMENT PRIMARY KEY,
            ID_Orario INT NOT NULL,
            Giorno DATE NOT NULL,
            OraInizio TIME NOT NULL,
            OraFine TIME NOT NULL,
            Aula INT NOT NULL,
            Insegnamento VARCHAR(10) NOT NULL,
            Docente INT NOT NULL,
            DataModifica DATETIME NOT NULL,
            ModificatoDa INT NOT NULL,
            FOREIGN KEY (ID_Orario) REFERENCES ORARIO(ID_Orario),
            FOREIGN KEY (ModificatoDa) REFERENCES AMMINISTRATORE(ID_Amministratore)
        )",

      // Tabella AMMINISTRATORE
      "CREATE TABLE IF NOT EXISTS AMMINISTRATORE (
            ID_Amministratore INT AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(50) NOT NULL,
            Cognome VARCHAR(50) NOT NULL,
            Email VARCHAR(100) NOT NULL UNIQUE,
            Password VARCHAR(255) NOT NULL
        )",

      // Tabella MODIFICA
      "CREATE TABLE IF NOT EXISTS MODIFICA (
            ID_Modifica INT AUTO_INCREMENT PRIMARY KEY,
            AmministratoreID INT NOT NULL,
            Oggetto VARCHAR(200),
            DataOra DATETIME NOT NULL,
            Dettaglio TEXT,
            FOREIGN KEY (AmministratoreID) REFERENCES AMMINISTRATORE(ID_Amministratore)
        )"
    ];

    foreach ($queries as $query) {
      if ($conn->query($query) === TRUE) {
        echo "Tabella creata correttamente!<br>";
      } else {
        echo "Errore nella creazione della tabella: " . $conn->error . "<br>";
      }
    }

    // Chiudi la connessione al database
    ChiudiConnessione($conn);

  } catch (Exception $e) {
    error_log($e->getMessage());
    die("Errore: " . $e->getMessage());
  }
}

// FUNZIONE PER LA CREAZIONE DEI DOCENTI
function CreaDocente($nome, $cognome, $ssd, $unitaOrganizzativa, $disponibilita, $maxOreConsentite)
{
  try {
    // Apri connessione
    $conn = ApriConnessione();

    // Inizio della transazione
    $conn->begin_transaction();

    // Inserimento del docente nella tabella DOCENTE
    $stmt = $conn->prepare("INSERT INTO DOCENTE (Nome, Cognome, SSD, UnitaOrganizzativa) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $cognome, $ssd, $unitaOrganizzativa);
    if (!$stmt->execute()) {
      throw new Exception("Errore nell'inserimento del docente: " . $stmt->error);
    }
    $docenteId = $conn->insert_id; // Ottieni l'ID del docente appena inserito

    // Inserimento del massimo numero di ore consentite nella tabella CARICO_LAVORO_DOCENTE
    $stmt = $conn->prepare("INSERT INTO CARICO_LAVORO_DOCENTE (ID_Docente, OreTotali, MaxOreConsentite) VALUES (?, 0, ?)");
    $stmt->bind_param("ii", $docenteId, $maxOreConsentite);
    if (!$stmt->execute()) {
      throw new Exception("Errore nell'inserimento delle ore di lavoro del docente: " . $stmt->error);
    }

    // Inserimento delle disponibilità orarie nella tabella DISPONIBILITA_DOCENTE
    foreach ($disponibilita as $giorno => $orari) {
      foreach ($orari as $orario) {
        $stmt = $conn->prepare("INSERT INTO DISPONIBILITA_DOCENTE (ID_Docente, Giorno, OraInizio, OraFine) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $docenteId, $giorno, $orario['oraInizio'], $orario['oraFine']);
        if (!$stmt->execute()) {
          throw new Exception("Errore nell'inserimento della disponibilità oraria: " . $stmt->error);
        }
      }
    }

    // Commit della transazione
    $conn->commit();

    // Chiudi risorse
    $stmt->close();
    ChiudiConnessione($conn);

    return "Docente inserito correttamente!";
  } catch (Exception $e) {
    // Rollback della transazione in caso di errore
    $conn->rollback();
    ChiudiConnessione($conn);
    return "Errore: " . $e->getMessage();
  }
}

// FUNZIONE PER OTTENERE GLI SSD
function OttieniSSD()
{
  $conn = ApriConnessione();
  $query = "SELECT SSD, NomeSettore FROM SETTORE_SCIENTIFICO";
  $result = $conn->query($query);
  $ssds = [];
  while ($row = $result->fetch_assoc()) {
    $ssds[] = $row;
  }
  ChiudiConnessione($conn);
  return $ssds;
}

// FUNZIONE PER OTTENERE LE UNITÀ ORGANIZZATIVE
function OttieniUnitaOrganizzativa()
{
  $conn = ApriConnessione();
  $query = "SELECT Codice, Nome FROM UNITA_ORGANIZZATIVA";
  $result = $conn->query($query);
  $unita = [];
  while ($row = $result->fetch_assoc()) {
    $unita[] = $row;
  }
  ChiudiConnessione($conn);
  return $unita;
}

// FUNZIONE PER VISUALIZZARE L'ELENCO DEI DOCENTI
function VisualizzaElencoDocenti()
{
  try {
    // Apri la connessione al database
    $conn = ApriConnessione();

    // Query per ottenere l'elenco dei docenti
    $sql = "SELECT 
                    d.ID_Docente,
                    d.Nome,
                    d.Cognome,
                    d.SSD,
                    u.Nome AS UnitaOrganizzativa
                FROM 
                    DOCENTE d
                LEFT JOIN 
                    UNITA_ORGANIZZATIVA u 
                ON 
                    d.UnitaOrganizzativa = u.Codice";

    // Esegui la query
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      // Stampa l'elenco dei docenti con uno stile migliorato
      echo "<table class='styled-table'>";
      echo "<thead>
                    <tr>
                        <th>ID Docente</th>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>SSD</th>
                        <th>Unità Organizzativa</th>
                    </tr>
                  </thead>
                  <tbody>";
      while ($row = $result->fetch_assoc()) {
        echo "<tr>
                        <td>{$row['ID_Docente']}</td>
                        <td>{$row['Nome']}</td>
                        <td>{$row['Cognome']}</td>
                        <td>{$row['SSD']}</td>
                        <td>{$row['UnitaOrganizzativa']}</td>
                      </tr>";
      }
      echo "</tbody></table>";
    } else {
      echo "<p>Nessun docente trovato.</p>";
    }

    // Chiudi la connessione
    ChiudiConnessione($conn);
  } catch (Exception $e) {
    error_log($e->getMessage());
    echo "Errore: " . $e->getMessage();
  }
}

// FUNZIONE PER AGGIUNGERE UN EDIFICIO
function aggiungiEdificio($id, $nome, $indirizzo, $capacitaTotale)
{
  // Apri la connessione al database
  $conn = ApriConnessione();

  // Prepara la query di inserimento
  $query = "INSERT INTO EDIFICIO (ID_Edificio, Nome, Indirizzo, CapacitaTotale) 
              VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($query);

  // Verifica se la preparazione della query è riuscita
  if (!$stmt) {
    // Gestisci l'errore se la query non è preparata correttamente
    echo "Errore nella preparazione della query: " . $conn->error;
    ChiudiConnessione($conn);
    return false;
  }

  // Associa i parametri alla query
  $stmt->bind_param("sssi", $id, $nome, $indirizzo, $capacitaTotale);

  // Esegui la query
  $result = $stmt->execute();

  // Verifica se l'esecuzione della query è riuscita
  if ($result) {
    // Se la query ha avuto successo
    ChiudiConnessione($conn);
    return true;  // Ritorna true per indicare il successo
  } else {
    // Se c'è stato un errore nell'esecuzione
    echo "Errore nell'esecuzione della query: " . $stmt->error;
    ChiudiConnessione($conn);
    return false;  // Ritorna false per indicare il fallimento
  }
}


// FUNZIONE PER OTTENERE LA CAPACITÀ TOTALE DI UN EDIFICIO
function ottieniCapacitaTotaleEdificio($idEdificio)
{
  $conn = ApriConnessione();
  $query = "SELECT CapacitaTotale FROM EDIFICIO WHERE ID_Edificio = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $idEdificio);
  $stmt->execute();
  $stmt->bind_result($capacitaTotale);
  $stmt->fetch();
  ChiudiConnessione($conn);

  return $capacitaTotale ? $capacitaTotale : 0;
}

// Funzione per controllare se l'edificio esiste già
function edificioEsiste($nomeEdificio)
{
  $conn = ApriConnessione();
  $query = "SELECT COUNT(*) AS count FROM EDIFICIO WHERE Nome = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $nomeEdificio);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  ChiudiConnessione($conn);
  return $result['count'] > 0;
}

// FUNZIONE PER AGGIUNGERE UN'AULA
function aggiungiAula($nome, $capacita, $tipologia, $edificio)
{
  $conn = ApriConnessione();

  // Verifica che l'edificio esista e che la capacità totale non venga superata
  $capacitaUsata = OttieniCapacitaUtilizzataEdificio($edificio);
  $capacitaTotale = OttieniCapacitaTotaleEdificio($edificio);

  if ($capacitaUsata + $capacita > $capacitaTotale) {
    ChiudiConnessione($conn);
    return "Impossibile aggiungere l'aula: la capacità dell'edificio è stata superata.";
  }

  // Aggiungi l'aula
  $query = "INSERT INTO AULA (Nome, Capacita, Tipologia, Edificio) 
              VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("siss", $nome, $capacita, $tipologia, $edificio);
  $stmt->execute();
  ChiudiConnessione($conn);

  return "Aula aggiunta con successo!";
}

// FUNZIONE PER OTTENERE LA CAPACITÀ GIÀ UTILIZZATA IN UN EDIFICIO
function OttieniCapacitaUtilizzataEdificio($edificio)
{
  $conn = ApriConnessione();
  $query = "SELECT SUM(Capacita) as totalUsed FROM AULA WHERE Edificio = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $edificio);
  $stmt->execute();
  $stmt->bind_result($capacitaUtilizzata);
  $stmt->fetch();
  ChiudiConnessione($conn);

  return $capacitaUtilizzata ? $capacitaUtilizzata : 0;
}

// FUNZIONE PER MODIFICARE L'AULA (CAPACITÀ, NOME...)
function ModificaAula($idAula, $nome, $capacita, $tipologia)
{
  $conn = ApriConnessione();

  // Verifica che la nuova capacità non superi la capacità totale
  $edificio = OttieniEdificioDellaAula($idAula);
  $capacitaUsata = OttieniCapacitaUtilizzataEdificio($edificio) - OttieniCapacitaAula($idAula);
  $capacitaTotale = OttieniCapacitaTotaleEdificio($edificio);

  if ($capacitaUsata + $capacita > $capacitaTotale) {
    ChiudiConnessione($conn);
    return "Impossibile modificare l'aula: la capacità dell'edificio è stata superata.";
  }

  // Modifica l'aula
  $query = "UPDATE AULA SET Nome = ?, Capacita = ?, Tipologia = ? WHERE ID_Aula = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("sisi", $nome, $capacita, $tipologia, $idAula);
  $stmt->execute();
  ChiudiConnessione($conn);

  return "Aula modificata con successo!";
}

// FUNZIONE PER MODIFICARE UN EDIFICIO
function ModificaEdificio($idEdificio, $nome, $indirizzo, $capacitaTotale)
{
  try {
    $conn = ApriConnessione();

    // Prepara la query per aggiornare l'edificio
    $sql = "UPDATE EDIFICIO 
            SET Nome = ?, Indirizzo = ?, CapacitaTotale = ? 
            WHERE ID_Edificio = ?";

    // Prepara la query
    $stmt = $conn->prepare($sql);

    // Bind dei parametri
    $stmt->bind_param("ssis", $nome, $indirizzo, $capacitaTotale, $idEdificio);

    // Esegui la query
    if ($stmt->execute()) {
      $stmt->close();
      ChiudiConnessione($conn);
      return "Edificio modificato con successo.";
    } else {
      $stmt->close();
      ChiudiConnessione($conn);
      return "Errore nella modifica dell'edificio.";
    }
  } catch (Exception $e) {
    ChiudiConnessione($conn);
    error_log($e->getMessage());
    return "Errore: " . $e->getMessage();
  }
}


// FUNZIONE PER OTTENERE I DETTAGLI DI UN EDIFICIO
function OttieniDettagliEdificio($idEdificio)
{
  $conn = ApriConnessione();
  $query = "SELECT ID_Edificio, Nome, Indirizzo, CapacitaTotale FROM EDIFICIO WHERE ID_Edificio = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $idEdificio);
  $stmt->execute();
  $stmt->bind_result($id, $nome, $indirizzo, $capacitaTotale);
  $stmt->fetch();
  ChiudiConnessione($conn);

  return ['ID_Edificio' => $id, 'Nome' => $nome, 'Indirizzo' => $indirizzo, 'CapacitaTotale' => $capacitaTotale];
}


// FUNZIONE PER OTTENERE LA CAPACITÀ DI UN'AULA
function OttieniCapacitaAula($idAula)
{
  $conn = ApriConnessione();
  $query = "SELECT Capacita FROM AULA WHERE ID_Aula = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $idAula);
  $stmt->execute();
  $stmt->bind_result($capacita);
  $stmt->fetch();
  ChiudiConnessione($conn);

  return $capacita ? $capacita : 0;
}

// FUNZIONE PER OTTENERE L'EDIFICIO DI UN'AULA
function OttieniEdificioDellaAula($idAula)
{
  $conn = ApriConnessione();
  $query = "SELECT Edificio FROM AULA WHERE ID_Aula = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $idAula);
  $stmt->execute();
  $stmt->bind_result($edificio);
  $stmt->fetch();
  ChiudiConnessione($conn);

  return $edificio ? $edificio : null;
}

// FUNZIONE PER RIMUOVERE UN'AULA
function RimuoviAula($idAula)
{
  $conn = ApriConnessione();
  $query = "DELETE FROM AULA WHERE ID_Aula = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $idAula);
  $stmt->execute();
  ChiudiConnessione($conn);

  return "Aula rimossa con successo!";
}

// FUNZIONE PER OTTENERE GLI EDIFICI PER ZONA
function OttieniEdificiPerZona($zona)
{
  try {
    $conn = ApriConnessione();
    $zonaRicerca = "%$zona%";
    $sql = "SELECT ID_Edificio, Nome FROM EDIFICIO WHERE Indirizzo LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $zonaRicerca);
    $stmt->execute();
    $result = $stmt->get_result();

    $edifici = [];
    while ($row = $result->fetch_assoc()) {
      $edifici[] = $row;
    }

    $stmt->close();
    ChiudiConnessione($conn);
    return $edifici;

  } catch (Exception $e) {
    ChiudiConnessione($conn);
    error_log($e->getMessage());
    return "Errore: " . $e->getMessage();
  }
}

// FUNZIONE PER OTTENERE LE AULE IN UN EDIFICIO
function OttieniAulePerEdificio($edificio)
{
  $conn = ApriConnessione();
  $query = "SELECT ID_Aula, Nome FROM AULA WHERE Edificio = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $edificio);
  $stmt->execute();
  $result = $stmt->get_result();
  $aule = [];
  while ($row = $result->fetch_assoc()) {
    $aule[] = $row;
  }
  ChiudiConnessione($conn);
  return $aule;
}
