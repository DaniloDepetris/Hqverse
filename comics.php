<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'all') {
            // Buscar todos os quadrinhos
            $stmt = $pdo->query("SELECT * FROM comics");
            $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'comics' => $comics]);
        }
        elseif ($_GET['action'] === 'single' && isset($_GET['id'])) {
            // Buscar um quadrinho específico
            $stmt = $pdo->prepare("SELECT * FROM comics WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $comic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($comic) {
                // Buscar páginas do quadrinho
                $stmtPages = $pdo->prepare("SELECT * FROM comic_pages WHERE comic_id = ? ORDER BY page_number");
                $stmtPages->execute([$_GET['id']]);
                $pages = $stmtPages->fetchAll(PDO::FETCH_ASSOC);
                
                $comic['pages'] = $pages;
                echo json_encode(['success' => true, 'comic' => $comic]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Quadrinho não encontrado']);
            }
        }
        elseif ($_GET['action'] === 'category' && isset($_GET['category'])) {
            // Buscar por categoria
            $category = '%' . $_GET['category'] . '%';
            $stmt = $pdo->prepare("SELECT * FROM comics WHERE categories LIKE ?");
            $stmt->execute([$category]);
            $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'comics' => $comics]);
        }
    }
}
?>
