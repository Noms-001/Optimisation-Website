<?php
function getDatabaseConnection()
{
    try {
        $pdo = new PDO(
            "mysql:host=db;dbname=cms;charset=utf8",
            "root",
            "root"
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
        
    } catch (Exception $e) {
        die("Erreur DB : " . $e->getMessage());
    }
}
