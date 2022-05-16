<?php
require_once './database/database.php';
$authDB = require_once './database/security.php';

// On crée les constantes d'erreurs
const ERROR_REQUIRED = 'Veuillez renseigner ce champ';
const ERROR_PASSWORD_TOO_SHORT = 'Le mot de passe doit faire au moins 6 caractères';
const ERROR_PASSWORD_MISMATCH = 'Le mot de passe n\'est pas valide';
const ERROR_EMAIL_INVALID = 'L\'email n\'est pas valide';
const ERROR_EMAIL_UNKNOW = 'L\'email n\'est pas enregistré';


// On initialise le tableau des erreurs
$errors = [
    'email' => '',
    'password' => ''
];

// Si la méthode POST est envoyée pour créer un article, on peut lancer la requête 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On nettoie le input email
    $input = filter_input_array(INPUT_POST, [
        'email' => FILTER_SANITIZE_EMAIL,

    ]);

    // On déclare les variables
    $email = $input['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Si email n'existe pas ou qu'il n'est pas dans le bon format
    if (!$email) {
        $errors['email'] = ERROR_REQUIRED;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ERROR_EMAIL_INVALID;
    }
    // Si le mot de passe est vide et que son nombre de caractères est < 6
    if (!$password) {
        $errors['password'] = ERROR_REQUIRED;
    } elseif (mb_strlen($password) < 6) {
        $errors['password'] = ERROR_PASSWORD_TOO_SHORT;
    }

    // S'il ny a pas d'erreur
    if (empty(array_filter($errors, fn ($e) => $e !== ''))) {

        $user = $authDB->getUserFromEmail($email);

        // Si l'email n'existe pas = error
        if (!$user) {
            $errors['email'] = ERROR_EMAIL_UNKNOW;
            // Sinon, on vérifie que le password envoyé via le formlaire correspond au password de la base de données
        } else {
            if (!password_verify($password, $user['password'])) {
                $errors['password'] = ERROR_PASSWORD_MISMATCH;
            } else {
                $authDB->login($user['id']);
                header('Location: /profile.php');
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once 'includes/head.php' ?>
    <link rel="stylesheet" href="/public/css/auth-login.css">
    <title>Connexion</title>
</head>

<body>
    <div class="container">
        <?php require_once 'includes/header.php' ?>
        <div class="content">
            <div class="block p-20 form-container">
                <h1>Connexion</h1>
                <form action="/auth-login.php" method="POST">

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
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Connexion</button>
                    </div>
                </form>
            </div>
        </div>
        <?php require_once 'includes/footer.php' ?>
    </div>

</body>

</html>