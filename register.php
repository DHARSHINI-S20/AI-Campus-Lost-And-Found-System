<?php
require 'includes/db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hash);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        $error = "That email is already registered.";
    }
}
require 'includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <h3 class="mb-3">Create an account</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <input class="form-control mb-2" type="text" name="name" placeholder="Full Name" required>
        <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
        <button class="btn btn-primary w-100">Register</button>
    </form>
    <p class="mt-3">Already have an account? <a href="login.php">Login</a></p>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
