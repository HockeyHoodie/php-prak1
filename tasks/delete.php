<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (
    !isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('Invalid CSRF token.');
}

$id = (int)($_POST['id'] ?? 0);

$stmt = $db->prepare("
    DELETE FROM tasks
    WHERE id = :id
      AND user_id = :user_id
");

$stmt->execute([
    ':id' => $id,
    ':user_id' => $_SESSION['user_id']
]);

$log = date('Y-m-d H:i:s')
    . " | User: " . $_SESSION['user_id']
    . " | Deleted task ID: " . $id
    . PHP_EOL;

file_put_contents(
    '../logs/actions.log',
    $log,
    FILE_APPEND
);

header('Location: index.php');
exit;