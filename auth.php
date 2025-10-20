<?php
require_once 'config.php';

// Todas as respostas serão JSON
header('Content-Type: application/json; charset=utf-8');

// Garantir sessão ativa (não produz saída)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit;
}

$action = $data['action'];
if ($action === 'login') {
    // validar inputs
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email ou senha inválidos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];

        echo json_encode(['success' => true, 'user' => [
            'id' => $user['id'],
            'name' => $user['username'],
            'email' => $user['email']
        ]]);
        exit;
    }

    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
    exit;

} elseif ($action === 'signup') {
    $name = isset($data['name']) ? trim($data['name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $passwordRaw = isset($data['password']) ? $data['password'] : '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($passwordRaw) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados de cadastro inválidos (senha mínima 6 caracteres)']);
        exit;
    }

    // Checar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email já cadastrado']);
        exit;
    }

    $passwordHash = password_hash($passwordRaw, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $passwordHash]);

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso']);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário']);
        // não expor $e->getMessage() em produção
        exit;
    }
} elseif ($action === 'logout') {
    // destruir sessão
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout efetuado']);
    exit;

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit;
}

?>
