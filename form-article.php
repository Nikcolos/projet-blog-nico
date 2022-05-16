<?php
/////////////////////////////////////////////////////////////////
// Code qui permet de protéger une page /////////////////////////
/////////////////////////////////////////////////////////////////
require_once __DIR__ . '/database/database.php';

$authDB = require __DIR__ . '/database/security.php';

$currentUser = $authDB->isLoggedin();

$currentUser = $authDB->isLoggedin();
if (!$currentUser) {
    header('Location: /');
}
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////

// On se connecte au système de gestion des articles
$articleDB = require_once __DIR__ . './database/models/ArticleDB.php';

// On crée les constantes d'erreurs
const ERROR_REQUIRED = 'Veuillez renseigner ce champ';
const ERROR_TITLE_TOO_SHORT = 'Le titre est trop court';
const ERROR_CONTENT_TOO_SHORT = 'L\'article est trop court';
const ERROR_IMAGE_URL = 'L\'image doit être une url valide';

$errors = [
    'title' => '',
    'image' => '',
    'category' => '',
    'content' => '',
];

$category = '';


// On nettoie les inputs
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Le id envoyé par la méthode GET
$id = $_GET['id'] ?? '';

// Si un id est envoyé via GET (Pour modification), on lance la requête
if ($id) {

    $article = $articleDB->fetchOne($id);
    if ($article['author'] !== $currentUser['id']) {
        header('Location: /');
    }
    $title = $article['title'];
    $image = $article['image'];
    $category = $article['category'];
    $content = $article['content'];
}

// Si la méthode POST est envoyée pour créer un article, on peut lancer la requête 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST = filter_input_array(INPUT_POST, [
        'title' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'image' => FILTER_SANITIZE_URL,
        'category' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'content' => [
            'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'flags' => FILTER_FLAG_NO_ENCODE_QUOTES
        ]
    ]);

    // On déclare les données du formulaire
    // On renvoie une chaine vide pour éviter d'afficher les erreurs inutiles
    $title = $_POST['title'] ?? '';
    $image = $_POST['image'] ?? '';
    $category = $_POST['category'] ?? '';
    $content = $_POST['content'] ?? '';

    // Si la donnée envoyée est différente de title ou est vide ou si le titre est inférieur à 5 caractères
    if (!$title) {
        $errors['title'] = ERROR_REQUIRED;
    } elseif (mb_strlen($title) < 5) {
        $errors['title'] = ERROR_TITLE_TOO_SHORT;
    }

    // Si la donnée envoyée est différente de image ou est vide ou si ce n'est pas une url
    if (!$image) {
        $errors['image'] = ERROR_REQUIRED;
    } elseif (!filter_var($image, FILTER_VALIDATE_URL)) {
        $errors['image'] = ERROR_IMAGE_URL;
    }

    // Si la donnée envoyée est différente de category ou est vide
    if (!$category) {
        $errors['category'] = ERROR_REQUIRED;
    }

    // Si la données envoyée est différente de content ou est vide ou si le content est inférieur à 50 caractères
    if (!$content) {
        $errors['content'] = ERROR_REQUIRED;
    } elseif (mb_strlen($content) < 50) {
        $errors['content'] = ERROR_CONTENT_TOO_SHORT;
    }

    // S'il ny a pas d'erreur
    if (empty(array_filter($errors, fn ($e) => $e !== ''))) {
        // Si le id est envoyé via GET['id'] on applique la requete 
        if ($id) {
            $article['title'] = $title;
            $article['image'] = $image;
            $article['category'] = $category;
            $article['content'] = $content;
            $article['author'] = $currentUser['id'];
            $articleDB->updateOne($article);
        } else {
            $articleDB->CreateOne([
                'title' => $title,
                'category' => $category,
                'content' => $content,
                'image' => $image,
                'author' => $currentUser['id']
            ]);
        }
        header('Location: /');
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once 'includes/head.php' ?>
    <link rel="stylesheet" href="/public/css/form-article.css">

    <title><?= $id ? 'Modifier' : 'Créer' ?> un article</title>
</head>

<body>
    <div class="container">
        <?php require_once 'includes/header.php' ?>
        <div class="content">
            <div class="block p-20 form-container">
                <h1><?= $id ? 'Modifier' : 'Écrire' ?> un article</h1>
                <form action="/form-article.php<?= $id ? "?id=$id" : '' ?>" , method="post">
                    <div class="form-control">
                        <label for="title">Titre</label>
                        <input type="text" name="title" id="title" value="<?= $title ?? '' ?>">
                        <?php if ($errors['title']) : ?>
                            <p class="error"><?= $errors['title'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="image">Image</label>
                        <input type="text" name="image" id="image" value="<?= $image ?? '' ?>">
                        <?php if ($errors['image']) : ?>
                            <p class="error"><?= $errors['image'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="category">Catégorie</label>
                        <select name="category" id="category">
                            <option <?= !$category || $category === 'technologie' ? 'selected' : '' ?> value="technologie">Technologie</option>
                            <option <?= $category === 'nature' ? 'selected' : '' ?> value="nature">Nature</option>
                            <option <?= $category === 'politique' ? 'selected' : '' ?> value="politique">Politique</option>
                        </select>
                        <?php if ($errors['category']) : ?>
                            <p class="error"><?= $errors['category'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="content">Contenu</label>
                        <textarea name="content" id="content"><?= $content ?? '' ?></textarea>
                        <?php if ($errors['content']) : ?>
                            <p class="error"><?= $errors['content'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <a href="/" class="btn btn-secondary" type="button">Annuler</a>
                        <button class="btn btn-primary" type="submit"><?= $id ? 'Modifier' : 'Sauvegarder' ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php require_once 'includes/footer.php' ?>
    </div>

</body>

</html>