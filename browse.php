<?php
require 'includes/db.php';

$category = $_GET['category'] ?? '';
$location = $_GET['location'] ?? '';
$type = $_GET['type'] ?? '';

$sql = "SELECT items.*, users.name AS poster_name FROM items JOIN users ON items.user_id = users.id WHERE 1=1";
$params = []; $types = "";

if ($category !== '') { $sql .= " AND category = ?"; $params[] = $category; $types .= "s"; }
if ($location !== '') { $sql .= " AND location LIKE ?"; $params[] = "%$location%"; $types .= "s"; }
if ($type !== '') { $sql .= " AND type = ?"; $params[] = $type; $types .= "s"; }
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$items = $stmt->get_result();

require 'includes/header.php';
?>
<h3 class="mb-3">Browse Items</h3>
<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <select name="type" class="form-select">
            <option value="">All Types</option>
            <option value="lost" <?= $type=='lost'?'selected':'' ?>>Lost</option>
            <option value="found" <?= $type=='found'?'selected':'' ?>>Found</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="category" class="form-control" placeholder="Category" value="<?= htmlspecialchars($category) ?>">
    </div>
    <div class="col-md-3">
        <input type="text" name="location" class="form-control" placeholder="Location" value="<?= htmlspecialchars($location) ?>">
    </div>
    <div class="col-md-3">
        <button class="btn btn-secondary w-100">Filter</button>
    </div>
</form>

<div class="row">
<?php while ($item = $items->fetch_assoc()): ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <?php if ($item['image_path']): ?>
                <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" style="height:180px;object-fit:cover;">
            <?php endif; ?>
            <div class="card-body">
                <span class="badge <?= $item['type']=='lost' ? 'bg-danger' : 'bg-success' ?>"><?= strtoupper($item['type']) ?></span>
                <h5 class="card-title mt-2"><?= htmlspecialchars($item['category']) ?></h5>
                <p class="card-text"><?= htmlspecialchars(substr($item['description'],0,80)) ?>...</p>
                <p class="text-muted small">📍 <?= htmlspecialchars($item['location']) ?> · <?= $item['date_reported'] ?></p>
                <a href="item_detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">View & Matches</a>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php require 'includes/footer.php'; ?>
