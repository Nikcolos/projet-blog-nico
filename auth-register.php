<?php
$pdo = require_once './database/database.php';
$authDB = require_once './database/security.php';

// On crée les constantes d'erreurs
const ERROR_REQUIRED = 'Veuillez renseigner ce champ';
const ERROR_TOO_SHORT = 'Ce champ est trop court';
const ERROR_PASSWORD_TOO_SHORT = 'Le mot de passe doit faire au moins 6 caractères';
const ERROR_EMAIL_INVALID = 'L\'email n\'est pas valide';
const ERROR_PASSWORD_MISMATCH = 'Le mot de passe de confirmation est différent';

// On initialise le tableau des erreurs
$errors = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'password' => '',
    'confirmpassword' => ''
];

// Si la méthode POST est envoyée pour créer un article, on peut lancer la requête 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On nettoie les inputs
    $input = filter_input_array(INPUT_POST, [
        'firstname' => FILTER_SANITIZE_SPECIAL_CHARS,
        'lastname' => FILTER_SANITIZE_SPECIAL_CHARS,
        'email' => FILTER_SANITIZE_EMAIL,

    ]);

    // On déclare les variables
    $firstname = $input['firstname'] ?? '';
    $lastname = $input['lastname'] ?? '';
    $email = $input['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? '';

    // Si firstname n'existe pas
    if (!$firstname) {
        $errors['firstname'] = ERROR_REQUIRED;
    } elseif (mb_strlen($firstname) < 2) {
        $errors['firstname'] = ERROR_TOO_SHORT;
    }

    if (!$lastname) {
        $errors['lastname'] = ERROR_REQUIRED;
    } elseif (mb_strlen($firstname) < 2) {
        $errors['lastname'] = ERROR_TOO_SHORT;
    }

    if (!$email) {
        $errors['email'] = ERROR_REQUIRED;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ERROR_EMAIL_INVALID;
    }

    if (!$password) {
        $errors['password'] = ERROR_REQUIRED;
    } elseif (mb_strlen($password) < 6) {
        $errors['password'] = ERROR_PASSWORD_TOO_SHORT;
    }

    if (!$confirmpassword) {
        $errors['confirmpassword'] = ERROR_REQUIRED;
    } elseif ($confirmpassword !== $password) {
        $errors['confirmpassword'] = ERROR_PASSWORD_MISMATCH;
    }

    // S'il ny a pas d'erreur
    if (empty(array_filter($errors, fn ($e) => $e !== ''))) {
        $authDB->register([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password
        ]);
        header('Location: /profile.php');
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once 'includes/head.php' ?>
    <link rel="stylesheet" href="/public/css/auth-register.css">
    <title>Inscription</title>
</head>

<body>
    <div class="container">
        <?php require_once 'includes/header.php' ?>
        <div class="content">
            <div class="block p-20 form-container">
                <h1>Inscription</h1>
                <form action="/auth-register.php" method="POST">
                    <div class="form-control">
                        <label for="firstname">Prénom</label>
                        <input type="text" name="firstname" id="firstname" value="<?= $firstname ?? '' ?>">
                        <?php if ($errors['firstname']) : ?>
                            <p class="error"><?= $errors['firstname'] ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-control">
                        <label for="lastname">Nom</label>
                        <input type="text" name="lastname" id="lastname" value="<?= $lastname ?? '' ?>">
                        <?php if ($errors['lastname']) : ?>
                            <p class="error"><?= $errors['lastname'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-control">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?= $email ?? '' ?>">
                        <?php if ($errors['email']) : ?>
                            <p class="error"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-control">
                        <label for="password">Mot de passe</label>
                        <input type="password" name="password" id="password">
                        <?php if ($errors['password']) : ?>
                            <p class="error"><?= $errors['password'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-control">
                        <label for="confirmpassword">Confirmation mot de passe</label>
                        <input type="password" name="confirmpassword" id="confirmpassword">
                        <?php if ($errors['confirmpassword']) : ?>
                            <p class="error"><?= $errors['confirmpassword'] ?></p>
                        <?php endif; ?>
                    </div>


                    <div class="form-actions">
                        <a href="/" class="btn btn-secondary" type="button">Annuler</a>
                        <button class="btn btn-primary" type="submit">S'enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
        <?php require_once 'includes/footer.php' ?>
    </div>

</body>

</html>