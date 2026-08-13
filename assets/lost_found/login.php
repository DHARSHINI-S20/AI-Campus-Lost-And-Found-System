<?php
require 'includes/db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            header("Location: browse.php");
            exit;
        }
    }
    $error = "Invalid email or password.";
}
require 'includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <h3 class="mb-3">Login</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
        <button class="btn btn-primary w-100">Login</button>
    </form>
    <p class="mt-3">No account? <a href="register.php">Register</a></p>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
