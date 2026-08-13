<?php
require 'includes/db.php';
$result = $conn->query("SELECT name, karma_points FROM users ORDER BY karma_points DESC LIMIT 10");
require 'includes/header.php';
?>
<h3 class="mb-3">🏆 Karma Leaderboard</h3>
<p class="text-muted">Points are earned by reporting found items (+10), returning items to their owner (+15), and reporting lost items (+2). This rewards the behavior that actually matters: closing the loop, not just posting.</p>
<table class="table table-striped">
    <thead><tr><th>#</th><th>Name</th><th>Karma Points</th></tr></thead>
    <tbody>
    <?php $rank = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $rank++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><span class="badge bg-warning text-dark"><?= $row['karma_points'] ?> pts</span></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php require 'includes/footer.php'; ?>
