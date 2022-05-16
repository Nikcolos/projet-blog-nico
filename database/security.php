<?php
// Création d'une classe qi comporte tous les statements d'identification

class AuthDB
{
    private PDOStatement $statementRegister;
    private PDOStatement $statementReadSession;
    private PDOStatement $statementReadUser;
    private PDOStatement $statementReadUserFromEmail;
    private PDOStatement $statementCreateSession;
    private PDOStatement $statementDeleteSession;

    function __construct(private PDO $pdo)
    {
        $this->statementRegister = $pdo->prepare('INSERT INTO user VALUES (
            DEFAULT,
            :firstname,
            :lastname,
            :email,
            :password
            )');

        // Initialisation des statements
        $this->statementReadSession = $pdo->prepare('SELECT * FROM session WHERE id=:id');
        $this->statementReadUser = $pdo->prepare('SELECT * FROM user WHERE id=:id');
        $this->statementReadUserFromEmail = $pdo->prepare('SELECT * FROM user WHERE email=:email');
        $this->statementCreateSession = $pdo->prepare('INSERT INTO session VALUES(
            :sessionid,
            :userid
        )');
        $this->statementDeleteSession = $pdo->prepare('DELETE FROM session WHERE id=:id');
    }

    function login(string $userId): void
    {
        $sessionId = bin2hex(random_bytes(32));
        $this->statementCreateSession->bindvalue(':userid', $userId);
        $this->statementCreateSession->bindvalue(':sessionid', $sessionId);
        $this->statementCreateSession->execute();
        // On crée une signature pour protéger l'id de session
        $signature = hash_hmac('sha256', $sessionId, 'cinq petits chats');
        // On crée le cookie de 14 jours, path vide, domaine vide, secur à false, httpOnly à true
        setcookie('session', $sessionId, time() + 60 * 60 * 24 * 14, '', '', false, true);
        setcookie('signature', $signature, time() + 60 * 60 * 24 * 14, '', '', false, true);
        // l'utilisateur est loggé
        return;
    }

    // Gère les statements de la partie inscription de l'utilisteur
    function register(array $user)
    {

        // On hash le mot de passe
        $hashedPassword = password_hash($user['password'], PASSWORD_ARGON2ID);

        // On bindvalue pour éviter les injections sql et faire correspondre les données envoyées
        $this->statementRegister->bindvalue(':firstname', $user['firstname']);
        $this->statementRegister->bindvalue(':lastname', $user['lastname']);
        $this->statementRegister->bindvalue(':email', $user['email']);
        $this->statementRegister->bindvalue(':password', $hashedPassword);
        $this->statementRegister->execute();
        return;
    }

    function isLoggedin(): array | false
    {
        // On extrait la session
        $sessionId = $_COOKIE['session'] ?? '';
        $signature = $_COOKIE['signature'] ?? '';

        // Si on a une seesionId on va chercher l'utilisateur
        if ($sessionId && $signature) {
            $hash = hash_hmac('sha256', $sessionId, 'cinq petits chats');
            if (hash_equals($hash, $signature)) {
                // On prépare le statement pour récupérer l'id de session
                $this->statementReadSession->bindvalue(':id', $sessionId);
                $this->statementReadSession->execute();
                $session = $this->statementReadSession->fetch();

                // Si on a une session
                if ($session) {
                    $this->statementReadUser->bindvalue(':id', $session['userid']);
                    $this->statementReadUser->execute();
                    $user = $this->statementReadUser->fetch();
                }
            }
        }
        return $user ?? false;
    }

    function logout(string $sessionId): void
    {
        $this->statementDeleteSession->bindvalue(':id', $sessionId);
        $this->statementDeleteSession->execute();
        setcookie('session', '', time() - 1);
        setcookie('signature', '', time() - 1);
        return;
    }

    function getUserFromEmail(string $email): array
    {
        $this->statementReadUserFromEmail->bindvalue(':email', $email);
        $this->statementReadUserFromEmail->execute();
        return $this->statementReadUserFromEmail->fetch();
    }
}

return new AuthDB($pdo);
