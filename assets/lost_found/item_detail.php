<?php
require 'includes/db.php';
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT items.*, users.name AS poster_name FROM items JOIN users ON items.user_id = users.id WHERE items.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) { die("Item not found."); }

// ---------- SMART MATCHING ENGINE ----------
// Compares this item against opposite-type items and scores similarity.
function scoreMatch($a, $b) {
    $score = 0;

    // 1. Category exact match — strongest signal
    if (strtolower($a['category']) === strtolower($b['category'])) {
        $score += 50;
    }

    // 2. Location overlap (loose match, e.g. "Library" appears in both)
    if (stripos($a['location'], $b['location']) !== false || stripos($b['location'], $a['location']) !== false) {
        $score += 20;
    }

    // 3. Keyword overlap in description (ignore common stopwords)
    $stopwords = ['the','a','an','with','near','and','of','in','on','at','my','is','it','was'];
    $wordsA = array_diff(array_unique(str_word_count(strtolower($a['description']), 1)), $stopwords);
    $wordsB = array_diff(array_unique(str_word_count(strtolower($b['description']), 1)), $stopwords);
    $common = array_intersect($wordsA, $wordsB);
    $score += min(count($common) * 8, 32); // cap keyword contribution

    // 4. Date proximity
    $daysApart = abs((strtotime($a['date_reported']) - strtotime($b['date_reported'])) / 86400);
    if ($daysApart <= 1) $score += 15;
    elseif ($daysApart <= 3) $score += 10;
    elseif ($daysApart <= 7) $score += 5;

    return $score;
}

$oppositeType = $item['type'] === 'lost' ? 'found' : 'lost';
$stmt2 = $conn->prepare("SELECT * FROM items WHERE type = ? AND status = 'open' AND id != ?");
$stmt2->bind_param("si", $oppositeType, $id);
$stmt2->execute();
$candidates = $stmt2->get_result();

$matches = [];
while ($c = $candidates->fetch_assoc()) {
    $s = scoreMatch($item, $c);
    if ($s > 0) {
        $c['score'] = $s;
        $matches[] = $c;
    }
}
usort($matches, fn($x, $y) => $y['score'] - $x['score']);
$matches = array_slice($matches, 0, 5); // top 5

// Handle claim button
if (isset($_POST['claim']) && isset($_SESSION['user_id'])) {
    $conn->query("UPDATE items SET status = 'claimed' WHERE id = $id");
    $conn->query("UPDATE users SET karma_points = karma_points + 15 WHERE id = " . $_SESSION['user_id']);
    header("Location: item_detail.php?id=$id&claimed=1");
    exit;
}

require 'includes/header.php';
?>

<?php if (isset($_GET['claimed'])): ?>
    <div class="alert alert-success">Marked as claimed — thanks for closing the loop! You earned 15 karma points.</div>
<?php endif; ?>

<div class="row">
  <div class="col-md-7">
    <span class="badge <?= $item['type']=='lost' ? 'bg-danger' : 'bg-success' ?>"><?= strtoupper($item['type']) ?></span>
    <span class="badge bg-secondary"><?= $item['status'] ?></span>
    <h3 class="mt-2"><?= htmlspecialchars($item['category']) ?></h3>
    <?php if ($item['image_path']): ?>
        <img src="<?= htmlspecialchars($item['image_path']) ?>" style="max-width:100%;max-height:300px;" class="mb-3 rounded">
    <?php endif; ?>
    <p><?= htmlspecialchars($item['description']) ?></p>
    <p class="text-muted">📍 <?= htmlspecialchars($item['location']) ?> · 🗓 <?= $item['date_reported'] ?> · Posted by <?= htmlspecialchars($item['poster_name']) ?></p>

    <?php if ($item['type'] === 'found'): ?>
    <!-- Innovative touch: auto-generated QR code — print & stick on the physical item.
         Anyone who scans it lands straight on this claim page. -->
    <div class="card p-3 mt-3" style="max-width:220px;">
        <p class="small mb-2">📎 Print this & attach it to the item:</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode("http://localhost/lost_found/item_detail.php?id=$id") ?>" alt="QR code">
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id']) && $item['status'] === 'open'): ?>
    <form method="POST" class="mt-3">
        <button name="claim" class="btn btn-success">✅ This is mine / I found the owner</button>
    </form>
    <?php endif; ?>
  </div>

  <div class="col-md-5">
    <h5>🔗 Possible Matches</h5>
    <?php if (empty($matches)): ?>
        <p class="text-muted">No strong matches yet — check back as more items get posted.</p>
    <?php else: ?>
        <?php foreach ($matches as $m): ?>
            <a href="item_detail.php?id=<?= $m['id'] ?>" class="text-decoration-none">
            <div class="card mb-2 p-2">
                <div class="d-flex justify-content-between">
                    <strong><?= htmlspecialchars($m['category']) ?></strong>
                    <span class="badge bg-info text-dark">Match: <?= $m['score'] ?>%</span>
                </div>
                <small class="text-muted"><?= htmlspecialchars(substr($m['description'],0,60)) ?>...</small><br>
                <small>📍 <?= htmlspecialchars($m['location']) ?></small>
            </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
