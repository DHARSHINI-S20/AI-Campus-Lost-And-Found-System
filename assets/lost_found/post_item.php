<?php
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $date_reported = $_POST['date_reported'];
    $user_id = $_SESSION['user_id'];
    $image_path = null;

    // Handle optional image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('item_') . '.' . $ext;
        $target = 'uploads/' . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        }
    }

    $stmt = $conn->prepare("INSERT INTO items (user_id, type, category, description, location, date_reported, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $type, $category, $description, $location, $date_reported, $image_path);
    $stmt->execute();
    $new_id = $conn->insert_id;

    // Karma points: reward reporting a FOUND item more, since that's the prosocial action
    $points = ($type === 'found') ? 10 : 2;
    $conn->query("UPDATE users SET karma_points = karma_points + $points WHERE id = $user_id");

    header("Location: item_detail.php?id=" . $new_id);
    exit;
}
require 'includes/header.php';
?>
<h3 class="mb-3">Post an Item</h3>
<form method="POST" enctype="multipart/form-data" class="col-md-6">
    <div class="mb-2">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
            <option value="lost">Lost</option>
            <option value="found">Found</option>
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label">Category</label>
        <select name="category" class="form-select" required>
            <option>Electronics</option>
            <option>Bag/Backpack</option>
            <option>ID Card / Documents</option>
            <option>Keys</option>
            <option>Clothing</option>
            <option>Water Bottle</option>
            <option>Other</option>
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="e.g. black Dell laptop with a cracked corner sticker" required></textarea>
    </div>
    <div class="mb-2">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control" placeholder="e.g. Library 2nd floor" required>
    </div>
    <div class="mb-2">
        <label class="form-label">Date</label>
        <input type="date" name="date_reported" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Photo (optional)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <button class="btn btn-primary">Submit</button>
</form>
<?php require 'includes/footer.php'; ?>
