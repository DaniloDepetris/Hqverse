<?php
// install.php - Executar apenas uma vez
header('Content-Type: text/plain');

try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar banco de dados
    $conn->exec("CREATE DATABASE IF NOT EXISTS `hqverso` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados criado com sucesso.\n";
    
    // Usar o banco
    $conn->exec("USE `hqverso`");
    
    // Executar comandos do arquivo SQL
    $sql = file_get_contents('setup.sql');
    $conn->exec($sql);
    
    echo "Instalação concluída com sucesso!";
} catch(PDOException $e) {
    die("ERRO: " . $e->getMessage());
}
