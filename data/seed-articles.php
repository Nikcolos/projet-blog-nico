<?php
/////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////
// Ce script permet de renvoyer des données json vers une base de données mysql /////
/////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////


// On récupère le fichier json et on le décode pour renvoyer un tableau associatif
$articles = json_decode(file_get_contents('./articles.json'), true);

// On crée des variables pour l'objet PDO
$dns = 'mysql:host=localhost;dbname=blog;';
$user = 'root';
$pwd = 'Nikco16cognac@';


// On crée notre premier objet PDO
$pdo = new PDO($dns, $user, $pwd);

// On crée notre premier statement
$statement = $pdo->prepare('
INSERT INTO article (
  title,
  category,
  content,
  image
) VALUES(
  :title,
  :category,
  :content,
  :image
)');

foreach ($articles as $article) {
    $statement->bindValue(':title', $article['title']);
    $statement->bindValue(':category', $article['category']);
    $statement->bindValue(':content', $article['content']);
    $statement->bindValue(':image', $article['image']);
    $statement->execute();
}
