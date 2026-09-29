<?php

session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$stmt = $db->prepare("
    SELECT *
    FROM tasks
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$tasks = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Tasks</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<div class="container">

    <div class="header">
        <h1>My Tasks</h1>

        <div>
            Logged in as
            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>

            |
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <a class="button" href="create.php">+ Create Task</a>

    <?php if (!$tasks): ?>

        <p>No tasks yet.</p>

    <?php endif; ?>

    <?php foreach ($tasks as $task): ?>

        <div class="task">

            <h2><?= htmlspecialchars($task['title']) ?></h2>

            <p>
                <?= nl2br(htmlspecialchars($task['description'])) ?>
            </p>

            <p>
                Status:
                <strong><?= htmlspecialchars($task['status']) ?></strong>
            </p>

            <a href="edit.php?id=<?= $task['id'] ?>">
                Edit
            </a>

            <form
                method="POST"
                action="delete.php"
                style="display:inline"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $task['id'] ?>"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $_SESSION['csrf_token'] ?>"
                >

                <button type="submit">
                    Delete
                </button>

            </form>

        </div>

    <?php endforeach; ?>

</div>

</body>
</html>