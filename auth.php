<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        if ($data['action'] === 'login') {
            // Processar login
            $email = $data['email'];
            $password = $data['password'];
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                
                echo json_encode(['success' => true, 'user' => [
                    'id' => $user['id'],
                    'name' => $user['username'],
                    'email' => $user['email']
                ]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
            }
        }
        elseif ($data['action'] === 'signup') {
            // Processar cadastro
            $name = $data['name'];
            $email = $data['email'];
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $password]);
                
                echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário: ' . $e->getMessage()]);
            }
        }
    }
}
?>
