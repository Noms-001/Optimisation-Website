<?php
try {
    $pdo = new PDO(
        "mysql:host=mysql;dbname=cms;charset=utf8",
        "root",
        "root"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>
