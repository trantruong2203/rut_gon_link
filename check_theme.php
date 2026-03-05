<?php
$pdo = new PDO('mysql:host=localhost;dbname=shorten_db', 'root', '');
$stmt = $pdo->query("SELECT name, value FROM options WHERE name = 'theme'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['name'] . ' = ' . $row['value'] . "\n";
}
