<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Only admin can access
require_role('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $cashier_number = trim($_POST['cashier_number']);

    if (!$username || !$password || !$cashier_number) {
        $error = "All fields are required!";
    } else {
        // Check if username or cashier_number already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=? OR cashier_number=?");
        $stmt->execute([$username, $cashier_number]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username or Cashier Number already exists!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, cashier_number) VALUES (?,?, 'cashier', ?)");
            $stmt->execute([$username, $hash, $cashier_number]);
            $success = "Cashier added successfully!";
        }
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Cashier</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input { padding: 5px; width: 200px; }
        .btn { margin-top: 10px; padding: 6px 12px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Add New Cashier</h2>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Cashier Number</label>
        <input type="text" name="cashier_number" required>

        <button type="submit" class="btn">Add Cashier</button>
    </form>
</body>
</html>
