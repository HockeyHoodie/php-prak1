<?php

session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../tasks/index.php');
    exit;
}

$error = '';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_SESSION['login_attempts'] >= 5) {
        $error = 'Too many login attempts.';
    } else {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $db->prepare(
            "
            SELECT *
            FROM users
            WHERE email = :email
            ");

        $stmt->execute([
            ':email' => $email
        ]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_attempts'] = 0;

            header('Location: ../tasks/index.php');
            exit;

        } else {

            $_SESSION['login_attempts']++;
            $error = 'Invalid email or password.';
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="container">

    <h1>Login</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit">Login</button>

    </form>

        <a href="register.php">Register</a>

</div>

</body>
</html>