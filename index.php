<?php
//Config BDD WAMP
$mysqlDsn = 'mysql:dbname=cinema;host:localhost';
$username = 'root';
$password = 'root';

//Connexion BDD
try {
    $obj_pdp = new PDO($mysqlDsn,$username,$password);
    echo 'DB connected<br>';
}catch (PDOException $e) {
    echo 'Erreur de connexion : ' . $e->getMessage();
}

//Mise en place des tables de la BDD
try {
    $obj_pdp = new PDO($mysqlDsn,$username,$password);
    $obj_pdp->query('CREATE TABLE users
(
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(254) NOT NULL UNIQUE,
  password VARCHAR(60) NOT NULL,
  first_name VARCHAR(254) NOT NULL,
  last_name VARCHAR(254) NOT NULL,
  birthday DATETIME NOT NULL,
  role VARCHAR (254) NOT NULL
);

CREATE TABLE films
(
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(254) NOT NULL,
  release_date DATE NOT NULL,
  score FLOAT NOT NULL
);

CREATE TABLE shows
(
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  cinema_seats TINYINT NULL,
  time_slot DATETIME NOT NULL,
  room_number TINYINT NOT NULL,
  select_film INT(11) NOT NULL,
  select_user INT(11) NOT NULL,
  FOREIGN KEY (select_film) REFERENCES films(id),
  FOREIGN KEY (select_user) REFERENCES users(id)
);
');
    echo 'Toutes les tables sont créées <br>';
}catch (PDOException $e) {
    echo 'Impossible de créer les tables : ' . $e->getMessage();
}

//Insertion d'un user dans la BDD
try{$obj_pdo = new PDO($mysqlDsn,$username,$password);
    $statement = $obj_pdo->prepare('INSERT INTO users (email, password, first_name, last_name, role ,birthday) VALUES (:email, :password, :first_name, :last_name, :role ,:birthday)');
    $statement->bindValue(':email', 'john.doe@example.com');

    // Hash du mot de passe en utilisant BCRYPT
    $statement->bindValue(':password', password_hash('test_p4ssword', PASSWORD_BCRYPT));

    $statement->bindValue(':first_name', 'john');
    $statement->bindValue(':last_name', 'doe');
    $statement->bindValue(':role', 'ROLE_USER');
    $statement->bindValue(':birthday', '1996-04-18');
    if ($statement->execute()) {
        echo 'Utilisateur créé <br>';
    } else {
        $errorInfo = $statement->errorInfo();
        echo 'SQLSTATE : '.$errorInfo[0].'<br>';
        echo 'Erreur du driver : '.$errorInfo[1].'<br>';
        echo 'Message : '.$errorInfo[2]. '<br>';
    }
} catch (PDOException $e){
    echo 'Impossible de créer l\'utilisateur :' . $e->getMessage();
}

//Insertion d'un film dans la bdd
try{
    $obj_pdp = new PDO($mysqlDsn,$username,$password);
    $obj_pdp->query('INSERT INTO films (name, release_date, score)
    VALUES ("Star Wars : L\'Empire contre attaque", "1980-08-20", "4.8")');
    echo 'Film crée <br>';
}catch (PDOException $e){
    echo 'Impossible de créer le film' . $e->getMessage();
}
//Connexion à l'utilisateur et vérification des droits
try {
    $obj_pdo = new PDO($mysqlDsn, $username, $password);
    $statement = $obj_pdo->prepare('SELECT * FROM users WHERE email = :email');
    $statement->bindValue(':email', 'john.doe@example.com');
    if ($statement->execute()) {
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        if ($user === false) {
            echo 'Identifiants invalides';
        } else {
            if (password_verify('test_p4ssword', $user['password'])) {
                if ($user['role'] === 'ROLE_ADMIN'){
                    echo 'Bienvenue Admin ' . $user['first_name'] .   '<br>';
                } else{
                    echo  'Bienvenue ' . $user['first_name'] . '<br>';
                }
            } else {
                echo 'Identifiants invalides';
            }
        }
    } else {
        echo 'Impossible de récupérer l\'utilisateur';
    }
}catch (PDOException $e){
    echo 'Impossible de se connecter à l\'utilisateur';
}

//Insertion d'une réservation dans la BDD
try {
    $obj_pdo = new PDO($mysqlDsn, $username, $password);
    $statement = $obj_pdo->prepare("INSERT INTO shows
	(cinema_seats, time_slot, room_number, select_film, select_user)
    VALUES
	('3','2019-08-20 19:00:00','4', '1', '1');");
    if ($statement->execute()) {
        echo 'Réservation créé <br>';
    } else {
        $errorInfo = $statement->errorInfo();
        echo 'SQLSTATE : ' . $errorInfo[0] . '<br>';
        echo 'Erreur du driver : ' . $errorInfo[1] . '<br>';
        echo 'Message : ' . $errorInfo[2] . '<br>';
    }
}
    catch (PDOException $e){
    echo 'Impossible de créer la réservation' . $e->getMessage();
}

//Selection et affichage d'une réservation
try {
    $obj_pdo = new PDO($mysqlDsn, $username, $password);
    $statement = $obj_pdo->prepare("SELECT cinema_seats as seats, time_slot, room_number, name as film_name, first_name, last_name, email FROM shows
	INNER JOIN films ON shows.select_film = films.id
    INNER JOIN users ON shows.select_user = users.id
    WHERE shows.id like :id;");
    $statement->bindValue(':id', '1');
    if ($statement->execute()) {
        while ($shows = $statement->fetch(PDO::FETCH_OBJ)) {
            echo 'La reservation de ' . $shows->first_name . ' ' .$shows->last_name .'(' .$shows->email . ')' .  ' : ' . $shows->film_name . '<br>' . $shows->time_slot . '<br>Nombre de place : ' . $shows->seats . '<br>Salle n°' . $shows->room_number . '<br>';
        }
    } else {
        $errorInfo = $statement->errorInfo();
        echo 'SQLSTATE : ' . $errorInfo[0] . '<br>';
        echo 'Erreur du driver : ' . $errorInfo[1] . '<br>';
        echo 'Message : ' . $errorInfo[2] . '<br>';
    }
}catch (PDOException $e){
    echo 'Impossible de sélectionner la reservation';
}

//Modification de la réservation
try {
    $obj_pdo = new PDO($mysqlDsn, $username, $password);
    $statement = $obj_pdo->prepare("UPDATE shows
    SET
        cinema_seats = '6'
    WHERE
        id = '1';");
    if ($statement->execute()) {
        echo 'Reservation modifiée<br>';
    } else {
        $errorInfo = $statement->errorInfo();
        echo 'SQLSTATE : ' . $errorInfo[0] . '<br>';
        echo 'Erreur du driver : ' . $errorInfo[1] . '<br>';
        echo 'Message : ' . $errorInfo[2] . '<br>';
    }
}catch (PDOException $e){
    echo 'Impossible de modifier la reservation';
}

//Suppression de la réservation
try {
    $obj_pdo = new PDO($mysqlDsn, $username, $password);
    $statement = $obj_pdo->prepare("DELETE FROM shows
    WHERE
        id = '1';");
    if ($statement->execute()) {
        echo 'Reservation supprimée<br>';
    } else {
        $errorInfo = $statement->errorInfo();
        echo 'SQLSTATE : ' . $errorInfo[0] . '<br>';
        echo 'Erreur du driver : ' . $errorInfo[1] . '<br>';
        echo 'Message : ' . $errorInfo[2] . '<br>';
    }
}catch (PDOException $e){
    echo 'Impossible de supprimer la reservation';
}
