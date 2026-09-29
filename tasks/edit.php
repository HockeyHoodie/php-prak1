<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare("
    SELECT *
    FROM tasks
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    ':id' => $id,
    ':user_id' => $_SESSION['user_id']
]);

$task = $stmt->fetch();

if (!$task) {
    die('Task not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Invalid CSRF token.');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'pending',
        'in_progress',
        'completed'
    ];

    if ($title === '') {
        $error = 'Title is required.';
    } elseif (!in_array($status, $allowedStatuses, true)) {
        $error = 'Invalid status.';
    } else {

        $stmt = $db->prepare("
            UPDATE tasks
            SET title = :title,
                description = :description,
                status = :status
            WHERE id = :id
              AND user_id = :user_id
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':status' => $status,
            ':id' => $id,
            ':user_id' => $_SESSION['user_id']
        ]);

        $log = date('Y-m-d H:i:s')
            . " | User: " . $_SESSION['user_id']
            . " | Updated task ID: " . $id
            . PHP_EOL;

        file_put_contents(
            '../logs/actions.log',
            $log,
            FILE_APPEND
        );

        header('Location: index.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<div class="container">

    <h1>Edit Task</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

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

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars($task['title']) ?>"
            required
        >

        <textarea name="description"><?= htmlspecialchars($task['description']) ?></textarea>

        <select name="status">

            <option
                value="pending"
                <?= $task['status'] === 'pending' ? 'selected' : '' ?>
            >
                Pending
            </option>

            <option
                value="in_progress"
                <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>
            >
                In progress
            </option>

            <option
                value="completed"
                <?= $task['status'] === 'completed' ? 'selected' : '' ?>
            >
                Completed
            </option>

        </select>

        <button type="submit">
            Save changes
        </button>

    </form>

    <a href="index.php">Back</a>

</div>

</body>
</html>