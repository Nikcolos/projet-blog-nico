<?php

// On crée la classe ArticleDB()
class ArticleDB
{
    // On déclare les statements de la class PDO
    private PDOStatement $statementCreateOne;
    private PDOStatement $statementUpdateOne;
    private PDOStatement $statementDeleteOne;
    private PDOStatement $statementReadOne;
    private PDOStatement $statementReadAll;
    private PDOStatement $statementReadUserAll;

    // Constructeur pour initialiser tous les statements
    function __construct(private PDO $pdo)
    {
        // On prépare la requête pour enregistrer les données en BDD
        $this->statementCreateOne = $pdo->prepare('
        INSERT INTO article (
        title,
        category,
        content,
        image,
        author
    ) VALUES(
        :title,
        :category,
        :content,
        :image,
        :author
        )
    ');

        // On prépare la requête pour update les données en BDD
        $this->statementUpdateOne = $pdo->prepare('
        UPDATE article 
        SET 
            title=:title,
            category=:category,
            content=:content,
            image=:image,
            author=:author 
        WHERE id=:id
    ');

        // On prépare la requête pour supprimer les données en BDD
        $this->statementDeleteOne = $pdo->prepare('DELETE FROM article WHERE id=:id');

        // On prépare la requête pour sélectionner les données en BDD et lire l'article (Single page)
        $this->statementReadOne = $pdo->prepare('SELECT article.*, user.firstname, user.lastname FROM article LEFT JOIN user ON article.author = user.id WHERE article.id=:id');

        // On prépare la requête pour lire tous les articles de la table en  (On met LEFT pour pouvoir obtenir tout nos articles)
        $this->statementReadAll = $pdo->prepare('SELECT article.*, user.firstname, user.lastname FROM article LEFT JOIN user ON article.author = user.id');

        // On prépare la requête pour afficher tous les articles de la table en  (On met LEFT pour pouvoir obtenir tout nos articles) sur la page profile
        $this->statementReadUserAll = $pdo->prepare('SELECT * FROM article WHERE author=:authorId');
    }

    // On implémente nos fonctions
    public function fetchAll(): array
    {
        $this->statementReadAll->execute();
        return $this->statementReadAll->fetchAll();
    }

    // On crée une méthode (fonction) public (Que l'on peut utiliser à l'extérieur) pour afficher l'article
    public function fetchOne(int $id): array
    {
        $this->statementReadOne->bindValue(':id', $id);
        $this->statementReadOne->execute();
        return $this->statementReadOne->fetch();
    }

    // On crée une méthode (fonction) public (Que l'on peut utiliser à l'extérieur) pour supprimer l'article
    public function deleteOne(int $id): string
    {

        $this->statementDeleteOne->bindValue(':id', $id);
        $this->statementDeleteOne->execute();
        return $id;
    }

    // On crée une méthode (fonction) public (Que l'on peut utiliser à l'extérieur) pour créer l'article
    public function createOne($article): array
    {
        $this->statementCreateOne->bindValue(':title', $article['title']);
        $this->statementCreateOne->bindValue(':category', $article['category']);
        $this->statementCreateOne->bindValue(':content', $article['content']);
        $this->statementCreateOne->bindValue(':image', $article['image']);
        $this->statementCreateOne->bindValue(':author', $article['author']);
        $this->statementCreateOne->execute();
        return $this->fetchOne($this->pdo->lastInsertId());
    }

    // On crée une méthode (fonction) public (Que l'on peut utiliser à l'extérieur) pour modifier l'article
    public function updateOne($article): array
    {
        $this->statementUpdateOne->bindValue(':title', $article['title']);
        $this->statementUpdateOne->bindValue(':category', $article['category']);
        $this->statementUpdateOne->bindValue(':content', $article['content']);
        $this->statementUpdateOne->bindValue(':image', $article['image']);
        $this->statementUpdateOne->bindValue(':id', $article['id']);
        $this->statementUpdateOne->bindValue(':author', $article['author']);
        $this->statementUpdateOne->execute();
        return $article;
    }

    public function fetchUserArticle(string $authorId): array
    {
        $this->statementReadUserAll->bindValue(':authorId', $authorId);
        $this->statementReadUserAll->execute();
        return $this->statementReadUserAll->fetchAll();
    }
}

// On instancie la classe ArticleDB()
return new ArticleDB($pdo);
