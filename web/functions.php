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
                Email VARCHAR(100) NOT NULL UNIQUE,
                Telefono VARCHAR(20),
                Ruolo ENUM('Contratto (50 ore)', 'Ricercatore (80 ore)', 'Associato (100 ore)', 'Ordinario (120 ore)') NOT NULL,
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
                Attrezzature TEXT,
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
                Periodo VARCHAR(50) PRIMARY KEY,
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
                Periodo VARCHAR(50) NOT NULL,
                FOREIGN KEY (Lingua) REFERENCES LINGUE(CodiceLingua),
                FOREIGN KEY (SSD) REFERENCES SETTORE_SCIENTIFICO(SSD),
                FOREIGN KEY (DocenteTitolare) REFERENCES DOCENTE(ID_Docente),
                FOREIGN KEY (CorsoDiStudio) REFERENCES CORSO_DI_STUDIO(Codice),
                FOREIGN KEY (Periodo) REFERENCES PERIODO(Periodo)
            )",

      // Tabella DISPONIBILITA_DOCENTE
      "CREATE TABLE IF NOT EXISTS DISPONIBILITA_DOCENTE (
                ID_Docente INT NOT NULL,
                Giorno ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi') NOT NULL,
                OraInizio TIME NOT NULL CHECK (OraInizio >= '09:00:00' AND OraInizio <= '16:00:00'),
                OraFine TIME NOT NULL CHECK (OraFine >= '11:00:00' AND OraFine <= '18:00:00'),
                PRIMARY KEY (ID_Docente, Giorno, OraInizio),
                FOREIGN KEY (ID_Docente) REFERENCES DOCENTE(ID_Docente)
            )",

      // Tabella DISPONIBILITA_AULA
      "CREATE TABLE IF NOT EXISTS DISPONIBILITA_AULA (
                ID_Aula INT NOT NULL,
                Giorno ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi') NOT NULL,
                OraInizio TIME NOT NULL CHECK (OraInizio >= '09:00:00' AND OraInizio <= '16:00:00'),
                OraFine TIME NOT NULL CHECK (OraFine >= '11:00:00' AND OraFine <= '18:00:00'),
                TipologiaUtilizzo ENUM('lezione', 'laboratorio', 'esame') NOT NULL,
                PRIMARY KEY (ID_Aula, Giorno, OraInizio),
                FOREIGN KEY (ID_Aula) REFERENCES AULA(ID_Aula)
            )",

      // Tabella ORARIO
      "CREATE TABLE IF NOT EXISTS ORARIO (
                ID_Orario INT AUTO_INCREMENT PRIMARY KEY,
                Giorno DATE NOT NULL,
                OraInizio TIME NOT NULL CHECK (OraInizio >= '09:00:00' AND OraInizio <= '16:00:00'),
                OraFine TIME NOT NULL CHECK (OraFine >= '11:00:00' AND OraFine <= '18:00:00'),
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
                Periodo VARCHAR(50) NOT NULL,
                OreTotali INT NOT NULL CHECK (OreTotali >= 0),
                MaxOreConsentite INT NOT NULL CHECK (MaxOreConsentite > 0),
                PRIMARY KEY (ID_Docente, Periodo),
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

    // Esegui le query per creare le tabelle
    foreach ($queries as $query) {
      if ($conn->query($query) === TRUE) {
        echo "Query eseguita correttamente!<br>";
      } else {
        echo "Errore nell'esecuzione della query: " . $conn->error . "<br>";
      }
    }

    // Esegui i trigger separatamente
    $triggers = [
      "CREATE TRIGGER trg_chk_orario_docente
            BEFORE INSERT ON DISPONIBILITA_DOCENTE
            FOR EACH ROW
            BEGIN
                IF NEW.OraFine <= NEW.OraInizio THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'L\'ora finale non può essere minore o uguale all\'ora di inizio.';
                END IF;
            END",

      "CREATE TRIGGER trg_chk_orario_aula
            BEFORE INSERT ON DISPONIBILITA_AULA
            FOR EACH ROW
            BEGIN
                IF NEW.OraFine <= NEW.OraInizio THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'L\'ora finale non può essere minore o uguale all\'ora di inizio.';
                END IF;
            END",

      "CREATE TRIGGER trg_chk_capacita_aula
            BEFORE INSERT ON AULA
            FOR EACH ROW
            BEGIN
                DECLARE capacita_edificio INT;

                -- Ottieni la capacità totale dell'edificio associato
                SELECT CapacitaTotale INTO capacita_edificio
                FROM EDIFICIO
                WHERE ID_Edificio = NEW.Edificio;

                -- Verifica che la Capacita dell'aula non superi la CapacitaTotale dell'edificio
                IF NEW.Capacita > capacita_edificio THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'La capacità dell\'aula non può superare la capacità totale dell\'edificio.';
                END IF;
            END",

      "CREATE TRIGGER trg_chk_date_periodo
            BEFORE INSERT ON PERIODO
            FOR EACH ROW
            BEGIN
                IF NEW.DataInizio >= NEW.DataFine THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'La data di inizio non può essere uguale o successiva alla data di fine.';
                END IF;
            END",

      "CREATE TRIGGER trg_chk_anno_corso
            BEFORE INSERT ON CORSO_DI_STUDIO
            FOR EACH ROW
            BEGIN
                IF NEW.AnnoCorso <= 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'L\'anno del corso deve essere maggiore di zero.';
                END IF;
            END"
    ];

    // Esegui i trigger
    foreach ($triggers as $trigger) {
      if ($conn->query($trigger) === TRUE) {
        echo "Trigger creato correttamente!<br>";
      } else {
        echo "Errore nella creazione del trigger: " . $conn->error . "<br>";
      }
    }

    // Chiudi la connessione al database
    ChiudiConnessione($conn);

  } catch (Exception $e) {
    error_log($e->getMessage());
    die("Errore: " . $e->getMessage());
  }
}

// FUNZIONE PER OTTENERE TUTTE LE UNITÀ ORGANIZZATIVE
function OttieniUnitaOrganizzative($soloCodiceNome = false)
{
  $conn = ApriConnessione();
  $unitaOrganizzative = [];

  try {
    $query = $soloCodiceNome ? "SELECT Codice, Nome FROM UNITA_ORGANIZZATIVA" : "SELECT * FROM UNITA_ORGANIZZATIVA";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
      $unitaOrganizzative = $result->fetch_all(MYSQLI_ASSOC);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    ChiudiConnessione($conn);
  }

  return $unitaOrganizzative;
}

// FUNZIONE PER AGGIUNGERE UN'UNITÀ ORGANIZZATIVA
function AggiungiUnitaOrganizzativa($codice, $nome)
{
  $conn = ApriConnessione();

  // Aggiunge automaticamente il prefisso "Dipartimento di"
  $nomeCompleto = "Dipartimento di " . $nome;

  try {
    $query = "INSERT INTO UNITA_ORGANIZZATIVA (Codice, Nome) VALUES (?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
      throw new Exception("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("ss", $codice, $nomeCompleto);

    if ($stmt->execute()) {
      return "Unità organizzativa aggiunta con successo!";
    } else {
      throw new Exception("Errore nell'esecuzione della query: " . $stmt->error);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    $stmt->close();
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER RIMUOVERE UN'UNITÀ ORGANIZZATIVA
function RimuoviUnitaOrganizzativa($codice)
{
  $conn = ApriConnessione();

  try {
    $query = "DELETE FROM UNITA_ORGANIZZATIVA WHERE Codice = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
      throw new Exception("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("s", $codice);

    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        return "Unità organizzativa rimossa con successo!";
      } else {
        return "Nessuna unità organizzativa trovata con il codice specificato.";
      }
    } else {
      throw new Exception("Errore nell'esecuzione della query: " . $stmt->error);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    $stmt->close();
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER MODIFICARE L'UNITÀ ORGANIZZATIVA
function ModificaUnitaOrganizzativa($vecchioCodice, $nuovoCodice, $nuovoNome)
{
  $conn = ApriConnessione();

  try {
    // Aggiunge automaticamente il prefisso "Dipartimento di"
    $nuovoNomeCompleto = "Dipartimento di " . $nuovoNome;

    // Aggiorna il codice e il nome
    $query = "UPDATE UNITA_ORGANIZZATIVA SET Codice = ?, Nome = ? WHERE Codice = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
      throw new Exception("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("sss", $nuovoCodice, $nuovoNomeCompleto, $vecchioCodice);

    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        return "Unità organizzativa modificata con successo!";
      } else {
        return "Nessuna modifica effettuata. Verifica il codice inserito.";
      }
    } else {
      throw new Exception("Errore nell'esecuzione della query: " . $stmt->error);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    $stmt->close();
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER RECUPERARE UN'UNITÀ ORGANIZZATIVA PER MODIFICA
function OttieniUnitaOrganizzativaPerModifica($codice)
{
  $conn = ApriConnessione();
  $unita = null;

  try {
    $query = "SELECT * FROM UNITA_ORGANIZZATIVA WHERE Codice = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $codice);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $unita = $result->fetch_assoc();
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    ChiudiConnessione($conn);
  }

  return $unita;
}

// FUNZIONE PER INSERIRE UN CORSO DI STUDIO
function InserisciCorsoDiStudio($codice, $nome, $percorso, $annoCorso)
{
  $conn = ApriConnessione();

  try {
    $query = "INSERT INTO CORSO_DI_STUDIO (Codice, Nome, Percorso, AnnoCorso) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
      throw new Exception("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("sssi", $codice, $nome, $percorso, $annoCorso);

    if ($stmt->execute()) {
      return "Corso di studio inserito con successo!";
    } else {
      throw new Exception("Errore nell'inserimento del corso di studio: " . $stmt->error);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    $stmt->close();
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER MODIFICARE I CORSI DI STUDIO
function ModificaCorsoDiStudio($codice, $nome, $percorso, $annoCorso)
{
  $conn = ApriConnessione();

  try {
    $query = "UPDATE CORSO_DI_STUDIO SET Nome = ?, Percorso = ?, AnnoCorso = ? WHERE Codice = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
      throw new Exception("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("ssis", $nome, $percorso, $annoCorso, $codice);

    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        return "Corso di studio modificato con successo!";
      } else {
        return "Nessuna modifica apportata al corso di studio.";
      }
    } else {
      throw new Exception("Errore nella modifica del corso di studio: " . $stmt->error);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    $stmt->close();
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER ELIMINARE UN CORSO DI STUDIO
function RimuoviCorsoDiStudio($codice)
{
  $conn = ApriConnessione();

  try {
    $query = "DELETE FROM CORSO_DI_STUDIO WHERE Codice = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
      throw new Exception("Errore nella preparazione della query: " . $conn->error);
    }

    $stmt->bind_param("s", $codice);

    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        return "Corso di studio rimosso con successo!";
      } else {
        return "Nessun corso di studio trovato con il codice specificato.";
      }
    } else {
      throw new Exception("Errore nell'eliminazione del corso di studio: " . $stmt->error);
    }
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    $stmt->close();
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER OTTENERE TUTTI I CORSI DI STUDIO
function OttieniCorsiDiStudio()
{
  $conn = ApriConnessione();

  try {
    $query = "SELECT * FROM CORSO_DI_STUDIO";
    $result = $conn->query($query);

    if ($result === false) {
      throw new Exception("Errore nella query: " . $conn->error);
    }

    $corsi = [];
    while ($row = $result->fetch_assoc()) {
      $corsi[] = $row;
    }

    return $corsi;
  } catch (Exception $e) {
    return "Errore: " . $e->getMessage();
  } finally {
    ChiudiConnessione($conn);
  }
}

// FUNZIONE PER OTTENERE I SETTORI SCIENTIFICI
function OttieniSettoriScientifici($soloSSD = false)
{
  $conn = ApriConnessione();
  $sql = $soloSSD ? "SELECT SSD FROM SETTORE_SCIENTIFICO" : "SELECT * FROM SETTORE_SCIENTIFICO";
  $result = $conn->query($sql);

  $settori = [];
  while ($row = $result->fetch_assoc()) {
    $settori[] = $row;
  }
  ChiudiConnessione($conn);
  return $settori;
}

// FUNZIONE PER INSERIRE UN SETTORE SCIENTIFICO
function InserisciSettoreScientifico($ssd, $nome)
{
  $conn = ApriConnessione();
  $sql = "INSERT INTO SETTORE_SCIENTIFICO (SSD, NomeSettore) VALUES (?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $ssd, $nome);

  if ($stmt->execute()) {
    ChiudiConnessione($conn);
    return "Settore scientifico inserito con successo!";
  } else {
    ChiudiConnessione($conn);
    return "Errore durante l'inserimento del settore scientifico.";
  }
}

// FUZNZIONE PER RIMUOVERE UN SETTORE SCIENTIFICO
function RimuoviSettoreScientifico($ssd)
{
  $conn = ApriConnessione();
  $sql = "DELETE FROM SETTORE_SCIENTIFICO WHERE SSD = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $ssd);

  if ($stmt->execute()) {
    ChiudiConnessione($conn);
    return "Settore scientifico rimosso con successo!";
  } else {
    ChiudiConnessione($conn);
    return "Errore durante la rimozione del settore scientifico.";
  }
}

// FUNZIONE PER MODIFICARE IL SETTORE SCIENTIFICO
function ModificaSettoreScientifico($ssd, $nuovoNome)
{
  $conn = ApriConnessione();
  $sql = "UPDATE SETTORE_SCIENTIFICO SET NomeSettore = ? WHERE SSD = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $nuovoNome, $ssd);

  if ($stmt->execute()) {
    ChiudiConnessione($conn);
    return "Settore scientifico modificato con successo!";
  } else {
    ChiudiConnessione($conn);
    return "Errore durante la modifica del settore scientifico.";
  }
}

// FUNZIONE PER OTTENERE LA LISTA DI TUTTI GLI EDIFICI
function OttieniEdifici()
{
  $conn = ApriConnessione();

  $sql = "SELECT ID_Edificio, Nome, Indirizzo FROM EDIFICIO ORDER BY Nome ASC";
  $result = $conn->query($sql);

  $edifici = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $edifici[] = $row;
    }
  }

  ChiudiConnessione($conn);
  return $edifici;
}

// FUNZIONE PER INSERIRE UN EDIFICIO
function InserisciEdificio($idEdificio, $nome, $indirizzo, $capacitaTotale)
{
  $conn = ApriConnessione();

  $sql = "INSERT INTO EDIFICIO (ID_Edificio, Nome, Indirizzo, CapacitaTotale)
          VALUES (?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssi", $idEdificio, $nome, $indirizzo, $capacitaTotale);

  if ($stmt->execute()) {
    $message = "Edificio inserito con successo!";
  } else {
    $message = "Errore nell'inserimento dell'edificio: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER MODIFICARE UN EDIFICIO (senza modificare l'ID)
function ModificaEdificio($vecchioCodice, $nome, $indirizzo, $capacitaTotale)
{
  // Controllo se CapacitaTotale è un numero valido
  if (!is_numeric($capacitaTotale)) {
    return "Errore: la capacità totale deve essere un numero valido!";
  }

  $conn = ApriConnessione();

  // Prepara la query di aggiornamento
  $sql = "UPDATE EDIFICIO SET Nome = ?, Indirizzo = ?, CapacitaTotale = ? 
          WHERE ID_Edificio = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssis", $nome, $indirizzo, $capacitaTotale, $vecchioCodice);

  // Esegui la query
  if ($stmt->execute()) {
    $message = "Edificio modificato con successo!";
  } else {
    $message = "Errore nella modifica dell'edificio: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER RIMUOVERE UN EDIFICIO
function RimuoviEdificio($idEdificio)
{
  $conn = ApriConnessione();

  $sql = "DELETE FROM EDIFICIO WHERE ID_Edificio = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $idEdificio);

  if ($stmt->execute()) {
    $message = "Edificio rimosso con successo!";
  } else {
    $message = "Errore nella rimozione dell'edificio: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER GENERARE CODICE EDIFICIO
function GeneraCodiceEdificio($nome, $indirizzo)
{
  $prefisso = '';
  if ($indirizzo === 'Viale Ferdinando Stagno d\'Alcontres 31 (Papardo)') {
    $prefisso = 'PAP';
  } elseif ($indirizzo === 'Piazza Pugliatti, 1 (Centro)') {
    $prefisso = 'CENT';
  } elseif ($indirizzo === 'Viale Giovanni Palatucci, 13 (Annunziata)') {
    $prefisso = 'ANN';
  }

  // Rimuove spazi e crea abbreviazione del nome (primi 3 caratteri)
  $nomeAbbreviato = strtoupper(substr(trim($nome), 0, 3));
  return $prefisso . '-ED-' . $nomeAbbreviato;
}


// FUNZIONE PER OTTENERE RIMUOVERE IL PREFISSO DALL'EDIFICIO
function RimuoviPrefissoEdificio($nomeCompleto)
{
  // Rimuovi "EDIFICIO " dal nome per visualizzarlo o modificarlo
  return str_replace('EDIFICIO ', '', $nomeCompleto);
}

// FUNZIONE PER AGGIUNGERE IL PREFISSO ALL'EDIFICIO
function AggiungiPrefissoEdificio($nome)
{
  // Aggiungi "EDIFICIO " al nome per salvarlo
  return 'EDIFICIO ' . strtoupper(trim($nome));
}

// FUNZIONE PER OTTENERE GLI INDIRIZZI DISPONIBILI
function OttieniIndirizzi()
{
  return [
    'Viale Giovanni Palatucci, 13 (Annunziata)',
    'Piazza Pugliatti, 1 (Centro)',
    'Viale Ferdinando Stagno d\'Alcontres 31 (Papardo)'
  ];
}

// FUNZIONE PER OTTENERE TUTTE LE LINGUE
function OttieniLingue()
{
  $conn = ApriConnessione();
  $query = "SELECT * FROM LINGUE";
  $result = $conn->query($query);

  $lingue = [];
  while ($row = $result->fetch_assoc()) {
    $lingue[] = $row;
  }

  ChiudiConnessione($conn);
  return $lingue;
}

// FUNZIONE PER INSERIRE UNA LINGUA
function InserisciLingua($codiceLingua, $nomeLingua)
{
  $conn = ApriConnessione();
  $query = "INSERT INTO LINGUE (CodiceLingua, NomeLingua) VALUES (?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ss", $codiceLingua, $nomeLingua);

  if ($stmt->execute()) {
    $message = "Lingua aggiunta con successo!";
  } else {
    $message = "Errore nell'inserimento della lingua: " . $conn->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER RIMUOVERE UNA LINGUA
function RimuoviLingua($codiceLingua)
{
  $conn = ApriConnessione();
  $query = "DELETE FROM LINGUE WHERE CodiceLingua = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $codiceLingua);

  if ($stmt->execute()) {
    $message = "Lingua rimossa con successo!";
  } else {
    $message = "Errore nella rimozione della lingua: " . $conn->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER OTTENERE LA LISTA DI TUTTE LE AULE
function OttieniAule()
{
  $conn = ApriConnessione();

  $sql = "SELECT * FROM AULA";
  $result = $conn->query($sql);

  $aule = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $aule[] = $row;
    }
  }

  ChiudiConnessione($conn);
  return $aule;
}

// FUNZIONE PER OTTENERE TUTTE LE TIPOLOGIE DI AULA

function OttieniTipologieAula()
{
  $conn = ApriConnessione();
  $sql = "SHOW COLUMNS FROM AULA LIKE 'Tipologia'";
  $result = $conn->query($sql);
  $tipologie = [];
  if ($result && $row = $result->fetch_assoc()) {
    // es: "enum('teorica','laboratorio')"
    preg_match_all("/'([^']+)'/", $row['Type'], $matches);
    if (isset($matches[1])) {
      $tipologie = $matches[1];
    }
  }
  ChiudiConnessione($conn);
  return $tipologie;
}

// FUNZIONE PER INSERIRE UN'AULA
function InserisciAula($nome, $capacita, $tipologia, $edificio, $attrezzature)
{
  $conn = ApriConnessione();

  $sql = "INSERT INTO AULA (Nome, Capacita, Tipologia, Edificio, Attrezzature)
            VALUES (?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sisss", $nome, $capacita, $tipologia, $edificio, $attrezzature);

  if ($stmt->execute()) {
    $message = "Aula inserita con successo!";
  } else {
    $message = "Errore nell'inserimento dell'aula: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER MODIFICARE UN AULA
function ModificaAula($idAula, $nome, $capacita, $tipologia, $edificio, $attrezzature)
{
  if (!is_numeric($capacita)) {
    return "Errore: la capacità deve essere un numero valido!";
  }

  // Validazione della tipologia
  $tipoConsentiti = OttieniTipologieAula();
  if (!in_array($tipologia, $tipoConsentiti)) {
    return "Errore: tipologia non valida!";
  }

  // Validazione dell'edificio
  $conn = ApriConnessione();
  $stmt = $conn->prepare("SELECT ID_Edificio FROM EDIFICIO WHERE ID_Edificio = ?");
  $stmt->bind_param("s", $edificio);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    $stmt->close();
    ChiudiConnessione($conn);
    return "Errore: l'edificio selezionato non esiste!";
  }
  $stmt->close();

  // Prepara la query di aggiornamento
  $sql = "UPDATE AULA SET Nome = ?, Capacita = ?, Tipologia = ?, Edificio = ?, Attrezzature = ? 
            WHERE ID_Aula = ?";

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    ChiudiConnessione($conn);
    return "Errore nella preparazione della query: " . $conn->error;
  }

  // Corretto il tipo di binding: s=string, i=integer
  $stmt->bind_param("sisssi", $nome, $capacita, $tipologia, $edificio, $attrezzature, $idAula);

  if ($stmt->execute()) {
    $message = "Aula modificata con successo!";
  } else {
    $message = "Errore nella modifica dell'aula: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER RIMUOVERE UN'AULA
function RimuoviAula($idAula)
{
  $conn = ApriConnessione();

  $sql = "DELETE FROM AULA WHERE ID_Aula = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $idAula);

  if ($stmt->execute()) {
    $message = "Aula rimossa con successo!";
  } else {
    $message = "Errore nella rimozione dell'aula: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER OTTENERE LA LISTA DI TUTTI I DOCENTI
function OttieniDocenti()
{
  $conn = ApriConnessione();

  $sql = "SELECT * FROM DOCENTE";
  $result = $conn->query($sql);

  $docenti = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $docenti[] = $row;
    }
  }

  ChiudiConnessione($conn);
  return $docenti;
}

// FUNZIONE PER INSERIRE UN DOCENTE
function InserisciDocente($nome, $cognome, $ssd, $unitaOrganizzativa, $email, $telefono, $ruolo)
{
  $conn = ApriConnessione();

  $sql = "INSERT INTO DOCENTE (Nome, Cognome, SSD, UnitaOrganizzativa, Email, Telefono, Ruolo) VALUES (?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssss", $nome, $cognome, $ssd, $unitaOrganizzativa, $email, $telefono, $ruolo);

  if ($stmt->execute()) {
    $message = "Docente inserito con successo!";
  } else {
    $message = "Errore nell'inserimento del docente: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}


// FUNZIONE PER MODIFICARE UN DOCENTE
function ModificaDocente($idDocente, $nome, $cognome, $ssd, $unitaOrganizzativa, $email, $telefono, $ruolo)
{
  $conn = ApriConnessione();

  $sql = "UPDATE DOCENTE SET Nome = ?, Cognome = ?, SSD = ?, UnitaOrganizzativa = ?, Email = ?, Telefono = ?, Ruolo = ? WHERE ID_Docente = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssssi", $nome, $cognome, $ssd, $unitaOrganizzativa, $email, $telefono, $ruolo, $idDocente);

  if ($stmt->execute()) {
    $message = "Docente modificato con successo!";
  } else {
    $message = "Errore nella modifica del docente: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER RIMUOVERE UN DOCENTE
function RimuoviDocente($idDocente)
{
  $conn = ApriConnessione();

  $sql = "DELETE FROM DOCENTE WHERE ID_Docente = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $idDocente);

  if ($stmt->execute()) {
    $message = "Docente rimosso con successo!";
  } else {
    $message = "Errore nella rimozione del docente: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER INSERIRE UNA DISPONIBILITA' DOCENTE
function InserisciDisponibilitaDocente($idDocente, $giorno, $oraInizio, $oraFine)
{
  $conn = ApriConnessione();

  $sql = "INSERT INTO DISPONIBILITA_DOCENTE (ID_Docente, Giorno, OraInizio, OraFine) VALUES (?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("isss", $idDocente, $giorno, $oraInizio, $oraFine);

  if ($stmt->execute()) {
    $message = "Disponibilità docente inserita con successo!";
  } else {
    $message = "Errore nell'inserimento della disponibilità del docente: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER INSERIRE UN CARICO LAVORO DOCENTE
function InserisciCaricoLavoroDocente($idDocente, $periodo, $oreTotali, $maxOreConsentite)
{
  $conn = ApriConnessione();

  $sql = "INSERT INTO CARICO_LAVORO_DOCENTE (ID_Docente, Periodo, OreTotali, MaxOreConsentite) VALUES (?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("isii", $idDocente, $periodo, $oreTotali, $maxOreConsentite);

  if ($stmt->execute()) {
    $message = "Carico lavoro docente inserito con successo!";
  } else {
    $message = "Errore nell'inserimento del carico lavoro del docente: " . $stmt->error;
  }

  $stmt->close();
  ChiudiConnessione($conn);
  return $message;
}

// FUNZIONE PER OTTENERE TUTTI I VALORI DELLA COLONNA RUOLO NELLA TABELLA DOCENTE
function OttieniValoriRuolo()
{
  // Apri la connessione al database
  $conn = ApriConnessione();

  // Query per ottenere la definizione della colonna Ruolo dalla tabella Docente
  $sql = "SHOW COLUMNS FROM DOCENTE LIKE 'Ruolo'";
  $result = $conn->query($sql);

  $valoriRuolo = [];
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Il tipo della colonna (e.g., "enum('Valore1','Valore2',...)") è nella chiave 'Type'
    $tipo = $row['Type'];

    // Estraggo i valori ENUM dal tipo
    if (preg_match("/^enum\((.+)\)$/", $tipo, $matches)) {
      // Divido i valori, rimuovo gli apici singoli e li aggiungo all'array
      $valoriRuolo = array_map(function ($valore) {
        return trim($valore, "'");
      }, explode(",", $matches[1]));
    }
  }

  // Chiudi la connessione al database
  ChiudiConnessione($conn);

  return $valoriRuolo;
}

// FUNZIONE PER OTTENERE LE ORE MASSIME CONSENTITE PER OGNI RUOLO
function OttieniOreMassimePerRuolo($ruolo)
{
  $oreMassime = [
    'Contratto (50 ore)' => 50,
    'Ricercatore (80 ore)' => 80,
    'Associato (100 ore)' => 100,
    'Ordinario (120 ore)' => 120
  ];

  return isset($oreMassime[$ruolo]) ? $oreMassime[$ruolo] : 0;
}
