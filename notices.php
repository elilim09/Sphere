<?php
include 'config.php';

$sql = "SELECT * FROM notices ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notices = $stmt->fetchAll();

foreach ($notices as $notice) {
    echo "<h3>{$notice['title']}</h3>";
    echo "<p>{$notice['content']}</p>";
}
?>
