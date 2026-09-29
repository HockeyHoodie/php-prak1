<?php

session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../tasks/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 2) {
        $error = 'Username must contain at least 2 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6 && (bool) !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $error = 'Password must contain at least 6 characters and 1 special character.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must contain at least 6 characters.';
    } elseif ((bool) !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $error = 'Password must contain at least 1 special character.';
    } else {
        $stmt = $db->prepare("
            SELECT id FROM users
            WHERE username = :username OR email = :email
        ");

        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $db->prepare("
                INSERT INTO users (username, email, password)
                VALUES (:username, :email, :password)
            ");

            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $passwordHash
            ]);

            header('Location: login.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <div class="container">

        <h1>Register</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">

            <input
                type="text"
                name="username"
                placeholder="Username"
                required>

            <input
                type="email"
                name="email"
                placeholder="Email"
                required>

            <input
                type="password"
                name="password"
                placeholder="Password"
                required>

            <button type="submit">Register</button>

        </form>

        <p>
            <a href="login.php">Login</a>
        </p>

    </div>

</body>

</html>