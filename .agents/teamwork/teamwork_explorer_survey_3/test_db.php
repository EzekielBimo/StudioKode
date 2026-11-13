<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS: Connected to MySQL " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test GRANTS
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    echo "Grants:\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "  " . $row[0] . "\n";
    }
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
