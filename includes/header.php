<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="browse.php">🔎 Lost & Found</a>
    <div class="ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="post_item.php" class="btn btn-sm btn-outline-light me-2">+ Post Item</a>
            <a href="leaderboard.php" class="btn btn-sm btn-outline-warning me-2">🏆 Leaderboard</a>
            <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-sm btn-outline-light me-2">Login</a>
            <a href="register.php" class="btn btn-sm btn-outline-light">Register</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container mt-4">
