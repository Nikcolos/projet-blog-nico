<?php

$dns = 'mysql:host=localhost;dbname=blog;';
// Le fait d'employer getenv() permet de protéger les informations des autres développeurs. 
// Dans le terminal, il faut relancer le serveur en mentionnant avant :
// DB-USER = 'root' DB_PWD = 'Nikco16cognac@' php -S localhost:xxxx
$user = 'root';
$pwd = 'Nikco16cognac@';

// Si une erreur survient, on try l'erreur puis on la catche

try {
    // On crée notre premier objet PDO
    $pdo = new PDO($dns, $user, $pwd, [
        // Paramètres spécifique à PDO 
        // Les :: permettent  d'accéder à des constantes déclarées dans la définition d'une classe (Voir les cours)
        // ATTR_ERRMODE permet de définir de quelle façon PDO doit gérer les erreurs
        // ERRMODE_EXCEPTION permet de tout arrêter s'il y a une erreur
        // On met ATTR_DEFAULT_FETCH_MODE en FETCH_ASSOC pour que tous les tableaux associatifs dans fetchAll s'affichent sans les index.
        // Cela permet aussi de ne pas avoir à retaper FETCH_ASSOC a chaque fois que l'on utilise fetchAll().
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    // echo 'Connexion db ok...';
} catch (PDOException $e) {
    throw new Exception($e->getMessage());
}

return $pdo;
