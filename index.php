<?php
// Inicia a sessão para verificar se o usuário está logado
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'hqverso';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Se não conseguir conectar, mostra mensagem de erro
    $db_error = "Erro na conexão com o banco de dados: " . $e->getMessage();
}

// Verifica se o usuário está logado
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';

// Processa logout se solicitado
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Processa login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $is_logged_in = true;
            $user_name = $user['username'];
        } else {
            $login_error = 'Credenciais inválidas';
        }
    } catch(PDOException $e) {
        $login_error = 'Erro no login: ' . $e->getMessage();
    }
}

// Processa cadastro
$signup_error = '';
$signup_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $signup_error = 'As senhas não coincidem';
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password]);
            $signup_success = 'Conta criada com sucesso! Faça login.';
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                $signup_error = 'Este email já está em uso';
            } else {
                $signup_error = 'Erro ao criar conta: ' . $e->getMessage();
            }
        }
    }
}

// Busca quadrinhos do banco de dados
$comics = [];
$featured_comics = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM comics LIMIT 10");
        $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Pega alguns quadrinhos para destaque
        $stmt = $pdo->query("SELECT * FROM comics LIMIT 3");
        $featured_comics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $db_error = "Erro ao carregar quadrinhos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HQ Verso - Sua plataforma de quadrinhos online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e94560;
            --secondary: #0f3460;
            --dark: #16213e;
            --light: #f6f6f6;
            --gray: #b2bec3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--light);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            color: var(--primary);
            font-size: 2.5rem;
            font-weight: bold;
            text-decoration: none;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--light);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #d13354;
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--light);
            border: 1px solid var(--light);
        }
        
        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .hero {
            text-align: center;
            padding: 100px 0;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: var(--gray);
        }
        
        /* Página de Quadrinhos */
        .comics-page {
            display: none;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
        }
        
        .comics-section {
            margin: 40px 0;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        
        .comics-carousel {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) var(--dark);
        }
        
        .comics-carousel::-webkit-scrollbar {
            height: 8px;
        }
        
        .comics-carousel::-webkit-scrollbar-track {
            background: var(--dark);
        }
        
        .comics-carousel::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 20px;
        }
        
        .comic-card {
            min-width: 200px;
            background-color: #222;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .comic-card:hover {
            transform: scale(1.05);
        }
        
        .comic-cover {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        
        .comic-info {
            padding: 15px;
        }
        
        .comic-title {
            font-weight: bold;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .comic-meta {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .featured-comic {
            width: 100%;
            height: 500px;
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            margin-bottom: 40px;
            position: relative;
            cursor: pointer;
        }
        
        .featured-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        }
        
        .featured-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .featured-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .search-bar {
            display: flex;
            margin: 20px 0;
        }
        
        .search-bar input {
            flex: 1;
            padding: 12px;
            background-color: #333;
            border: none;
            border-radius: 4px 0 0 4px;
            color: var(--light);
            outline: none;
        }
        
        .search-bar button {
            padding: 12px 20px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .categories {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .category {
            padding: 8px 15px;
            background-color: #333;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category:hover, .category.active {
            background-color: var(--primary);
        }

        /* Modal de Login/Cadastro */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: var(--dark);
            padding: 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            position: relative;
        }
        
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s;
        }
        
        .close-btn:hover {
            color: var(--light);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            background-color: #333;
            border: none;
            border-radius: 4px;
            color: var(--light);
            outline: none;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #333;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom: 3px solid var(--primary);
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }

        /* Modal Netflix */
        .netflix-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            overflow-y: auto;
        }

        .netflix-modal-content {
            position: relative;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .netflix-hero {
            position: relative;
            width: 100%;
            height: 70vh;
            min-height: 400px;
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 0 0 60px 60px;
            margin-bottom: 40px;
        }

        .netflix-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30%;
            background: linear-gradient(to top, var(--dark), transparent);
        }

        .netflix-hero-info {
            position: relative;
            z-index: 2;
            max-width: 50%;
        }

        .netflix-hero-title {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .netflix-hero-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .netflix-hero-description {
            font-size: 1.2rem;
            line-height: 1.5;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .netflix-metadata {
            display: flex;
            gap: 15px;
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .netflix-match {
            color: #46d369;
            font-weight: bold;
        }

        .netflix-age-rating {
            padding: 2px 5px;
            border: 1px solid var(--gray);
            border-radius: 2px;
        }

        .netflix-section {
            margin-bottom: 40px;
        }

        .netflix-section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .netflix-more-like-this {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .netflix-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            color: white;
            cursor: pointer;
            z-index: 10;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: background 0.3s;
        }

        .netflix-close-btn:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .netflix-tabs {
            display: flex;
            border-bottom: 1px solid #404040;
            margin-bottom: 30px;
        }

        .netflix-tab {
            padding: 10px 20px;
            cursor: pointer;
            color: var(--gray);
            font-weight: bold;
            transition: all 0.3s;
        }

        .netflix-tab.active {
            color: white;
            border-bottom: 3px solid var(--primary);
        }

        .netflix-tab-content {
            display: none;
        }

        .netflix-tab-content.active {
            display: block;
        }

        .netflix-cast {
            display: flex;
            gap: 30px;
            overflow-x: auto;
            padding-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) var(--dark);
        }

        .netflix-cast::-webkit-scrollbar {
            height: 8px;
        }
        
        .netflix-cast::-webkit-scrollbar-track {
            background: var(--dark);
        }
        
        .netflix-cast::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 20px;
        }

        .netflix-cast-item {
            min-width: 120px;
            text-align: center;
            flex-shrink: 0;
        }

        .netflix-cast-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            background-color: #333;
        }

        .netflix-cast-name {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .netflix-cast-character {
            color: var(--gray);
            font-size: 0.8rem;
        }

        .netflix-episodes {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .netflix-episode {
            display: flex;
            gap: 20px;
            background-color: #222;
            border-radius: 8px;
            overflow: hidden;
        }

        .netflix-episode-thumbnail {
            width: 300px;
            height: 150px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .netflix-episode-info {
            padding: 20px;
            flex: 1;
        }

        .netflix-episode-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .netflix-episode-description {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .hero {
                padding: 60px 0;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1.2rem;
            }
            
            .featured-comic {
                height: 400px;
            }
            
            .featured-title {
                font-size: 1.8rem;
            }
            
            .netflix-hero {
                height: 50vh;
                padding: 0 20px 40px 20px;
            }
            
            .netflix-hero-info {
                max-width: 100%;
            }
            
            .netflix-hero-title {
                font-size: 2rem;
            }
            
            .netflix-hero-description {
                font-size: 1rem;
            }
            
            .netflix-modal-content {
                padding: 60px 20px;
            }
            
            .netflix-episode {
                flex-direction: column;
            }
            
            .netflix-episode-thumbnail {
                width: 100%;
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            header {
                flex-direction: column;
                gap: 15px;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .user-menu {
                width: 100%;
                flex-direction: column;
            }
            
            .search-bar {
                width: 100%;
            }
            
            .featured-comic {
                height: 300px;
            }
            
            .featured-overlay {
                padding: 15px;
            }
            
            .featured-title {
                font-size: 1.5rem;
            }
            
            .featured-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .netflix-hero {
                height: 40vh;
                min-height: 300px;
            }
        }

        .error-message {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 3px solid #ff6b6b;
        }
        
        .success-message {
            color: #51cf66;
            background: rgba(81, 207, 102, 0.1);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 3px solid #51cf66;
        }
        
        .db-error {
            background: rgba(255, 107, 107, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ff6b6b;
            text-align: center;
        }
    </style>
</head>
<body>
  <!-- Página de Quadrinhos -->
    <div class="container comics-page" id="comicsPage">
        <header>
            <a href="#" class="logo">HQ VERSO</a>
            <div class="user-menu">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar quadrinhos...">
                    <button><i class="fas fa-search"></i></button>
                </div>
                <?php if ($is_logged_in): ?>
                    <div class="user-info">
                        <span>Olá, <?php echo htmlspecialchars($user_name); ?></span>
                        <a href="?action=logout" class="btn btn-outline" style="margin-left: 10px;">Sair</a>
                    </div>
                    <div class="user-avatar" id="userAvatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <?php else: ?>
                    <a href="#" class="btn btn-outline" id="loginBtn">Entrar</a>
                <?php endif; ?>
            </div>
        </header>
        
        <?php if (!$is_logged_in): ?>
        <div class="welcome-message">
            <h2>Bem-vindo ao HQ Verso</h2>
            <p>Explore nosso universo de quadrinhos incríveis. Faça login ou crie uma conta para ter acesso completo à nossa biblioteca.</p>
            <a href="#" class="btn btn-primary" id="welcomeLoginBtn">Entrar ou Cadastrar</a>
        </div>
        <?php endif; ?>
        
        <div class="categories">
            <div class="category active">Todos</div>
            <div class="category">Super-heróis</div>
            <div class="category">Mangá</div>
            <div class="category">Graphic Novels</div>
            <div class="category">Clássicos</div>
            <div class="category">Indie</div>
            <?php if ($is_logged_in): ?>
            <div class="category">Favoritos</div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($db_error)): ?>
            <div class="db-error">
                <h3>Erro de conexão com o banco de dados</h3>
                <p><?php echo $db_error; ?></p>
                <p>Verifique se o banco de dados foi criado executando <a href="install.php">install.php</a></p>
            </div>
        <?php else: ?>
            <!-- Destaque -->
            <?php if (!empty($featured_comics)): ?>
            <div class="featured-comic" style="background-image: url('<?php echo $featured_comics[0]['cover'] ?: 'https://via.placeholder.com/1200x500'; ?>')">
                <div class="featured-overlay">
                    <h2 class="featured-title"><?php echo htmlspecialchars($featured_comics[0]['title']); ?></h2>
                    <p><?php echo htmlspecialchars($featured_comics[0]['description']); ?></p>
                    <div class="featured-actions">
                        <?php if ($is_logged_in): ?>
                            <button class="btn btn-primary" aria-label="Ler agora" onclick="window.location.href='leitor.html?id=<?php echo $featured_comics[0]['id']; ?>'">
                                <i class="fas fa-play"></i> Ler agora
                            </button>
                            <button class="btn btn-outline"><i class="fas fa-plus"></i> Minha lista</button>
                        <?php else: ?>
                            <button class="btn btn-primary" aria-label="Fazer login" id="featuredLoginBtn">
                                <i class="fas fa-play"></i> Fazer login para ler
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

             <!-- Lista de quadrinhos -->
            <?php if ($is_logged_in && !empty($comics)): ?>
            <div class="comics-section">
                <h3 class="section-title">Nossa coleção</h3>
                <div class="comics-carousel">
                    <?php foreach ($comics as $comic): ?>
                    <div class="comic-card" onclick="openComicModal('<?php echo $comic['id']; ?>')">
                        <img src="<?php echo $comic['cover'] ?: 'https://via.placeholder.com/200x300'; ?>" alt="Capa de <?php echo htmlspecialchars($comic['title']); ?>" class="comic-cover">
                        <div class="comic-info">
                            <div class="comic-title"><?php echo htmlspecialchars($comic['title']); ?></div>
                            <div class="comic-meta"><?php echo htmlspecialchars($comic['author']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ($is_logged_in): ?>
                <div class="welcome-message">
                    <h3>Nenhum quadrinho encontrado</h3>
                    <p>Execute o script de instalação para adicionar quadrinhos à biblioteca.</p>
                    <a href="install.php" class="btn btn-primary">Executar Instalação</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Modal de Login/Cadastro -->
    <div class="modal" id="authModal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            
            <div class="tabs">
                <div class="tab active" data-tab="login">Entrar</div>
                <div class="tab" data-tab="signup">Cadastrar</div>
            </div>
            
            <div class="tab-content active" id="login">
                <h2>Entrar na HQ Verso</h2>
                <?php if ($login_error): ?>
                    <div class="error-message"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form id="loginForm" method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <input type="email" id="loginEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Senha</label>
                        <input type="password" id="loginPassword" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
            
            <div class="tab-content" id="signup">
                <h2>Criar conta na HQ Verso</h2>
                <?php if ($signup_error): ?>
                    <div class="error-message"><?php echo $signup_error; ?></div>
                <?php endif; ?>
                <?php if ($signup_success): ?>
                    <div class="success-message"><?php echo $signup_success; ?></div>
                <?php endif; ?>
                <form id="signupForm" method="POST">
                    <input type="hidden" name="signup" value="1">
                    <div class="form-group">
                        <label for="signupName">Nome</label>
                        <input type="text" id="signupName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="signupEmail">Email</label>
                        <input type="email" id="signupEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="signupPassword">Senha</label>
                        <input type="password" id="signupPassword" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="signupConfirmPassword">Confirmar Senha</label>
                        <input type="password" id="signupConfirmPassword" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal no estilo Netflix -->
    <div class="netflix-modal" id="netflixModal">
        <button class="netflix-close-btn" id="netflixCloseBtn">&times;</button>
        <div class="netflix-modal-content">
            <div class="netflix-hero" id="netflixHero">
                <div class="netflix-hero-info">
                    <h1 class="netflix-hero-title" id="netflixTitle">Batman: O Cavaleiro das Trevas</h1>
                    <div class="netflix-hero-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-play"></i> Ler Agora
                        </button>
                        <button class="btn btn-outline">
                            <i class="fas fa-plus"></i> Minha Lista
                        </button>
                    </div>
                    <div class="netflix-metadata">
                        <span class="netflix-match">98% relevante</span>
                        <span class="netflix-age-rating">16+</span>
                        <span>2023</span>
                        <span>4 volumes</span>
                    </div>
                    <p class="netflix-hero-description" id="netflixDescription">
                        A obra-prima de Frank Miller que redefiniu o Batman para sempre. Gotham está em perigo e só um Batman mais velho e sombrio pode salvar a cidade.
                    </p>
                </div>
            </div>

            <div class="netflix-tabs">
                <div class="netflix-tab active" data-tab="about">Sobre</div>
                <div class="netflix-tab" data-tab="volumes">Volumes</div>
                <div class="netflix-tab" data-tab="details">Detalhes</div>
            </div>

            <div class="netflix-tab-content active" id="about">
                <div class="netflix-section">
                    <h3 class="netflix-section-title">Elenco</h3>
                    <div class="netflix-cast">
                        <div class="netflix-cast-item">
                            <img src="https://via.placeholder.com/100x100" alt="Frank Miller" class="netflix-cast-photo">
                            <div class="netflix-cast-name">Frank Miller</div>
                            <div class="netflix-cast-character">Escritor</div>
                        </div>
                        <div class="netflix-cast-item">
                            <img src="https://via.placeholder.com/100x100" alt="Klaus Janson" class="netflix-cast-photo">
                            <div class="netflix-cast-name">Klaus Janson</div>
                            <div class="netflix-cast-character">Artista</div>
                        </div>
                        <div class="netflix-cast-item">
                            <img src="https://via.placeholder.com/100x100" alt="Lynn Varley" class="netflix-cast-photo">
                            <div class="netflix-cast-name">Lynn Varley</div>
                            <div class="netflix-cast-character">Colorista</div>
                        </div>
                    </div>
                </div>

                <div class="netflix-section">
                    <h3 class="netflix-section-title">Mais como este</h3>
                    <div class="comics-carousel">
                        <div class="comic-card">
                            <img src="https://via.placeholder.com/200x300" alt="Batman: Ano Um" class="comic-cover">
                            <div class="comic-info">
                                <div class="comic-title">Batman: Ano Um</div>
                                <div class="comic-meta">Frank Miller</div>
                            </div>
                        </div>
                        <div class="comic-card">
                            <img src="https://via.placeholder.com/200x300" alt="Batman: O Longo Dia das Bruxas" class="comic-cover">
                            <div class="comic-info">
                                <div class="comic-title">Batman: O Longo Dia das Bruxas</div>
                                <div class="comic-meta">Jeph Loeb</div>
                            </div>
                        </div>
                        <div class="comic-card">
                            <img src="https://via.placeholder.com/200x300" alt="Batman: A Piada Mortal" class="comic-cover">
                            <div class="comic-info">
                                <div class="comic-title">Batman: A Piada Mortal</div>
                                <div class="comic-meta">Alan Moore</div>
                            </div>
                        </div>
                        <div class="comic-card">
                            <img src="https://via.placeholder.com/200x300" alt="Batman: Silêncio" class="comic-cover">
                            <div class="comic-info">
                                <div class="comic-title">Batman: Silêncio</div>
                                <div class="comic-meta">Jeph Loeb</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="netflix-tab-content" id="volumes">
                <div class="netflix-section">
                    <h3 class="netflix-section-title">Volumes</h3>
                    <div class="netflix-episodes">
                        <div class="netflix-episode">
                            <img src="https://via.placeholder.com/300x150" alt="Volume 1" class="netflix-episode-thumbnail">
                            <div class="netflix-episode-info">
                                <h4 class="netflix-episode-title">Volume 1: O Cavaleiro das Trevas</h4>
                                <p class="netflix-episode-description">
                                    Batman retorna após 10 anos de aposentadoria para enfrentar uma gangue de mutantes que aterroriza Gotham City.
                                </p>
                            </div>
                        </div>
                        <div class="netflix-episode">
                            <img src="https://via.placeholder.com/300x150" alt="Volume 2" class="netflix-episode-thumbnail">
                            <div class="netflix-episode-info">
                                <h4 class="netflix-episode-title">Volume 2: A Queda do Morcego</h4>
                                <p class="netflix-episode-description">
                                    Batman enfrenta o Coringa em um confronto final que mudará para sempre sua relação com o arqui-inimigo.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="netflix-tab-content" id="details">
                <div class="netflix-section">
                    <h3 class="netflix-section-title">Detalhes</h3>
                    <div class="netflix-more-like-this">
                        <div>
                            <strong>Editora:</strong> DC Comics
                        </div>
                        <div>
                            <strong>Data de publicação:</strong> 1986
                        </div>
                        <div>
                            <strong>Páginas:</strong> 224
                        </div>
                        <div>
                            <strong>Formato:</strong> Graphic Novel
                        </div>
                        <div>
                            <strong>Gêneros:</strong> Super-herói, Ação, Drama
                        </div>
                        <div>
                            <strong>Prêmios:</strong> Eisner Award, Harvey Award
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal de Login/Cadastro
        const loginBtn = document.getElementById('loginBtn');
        const authModal = document.getElementById('authModal');
        const closeBtn = document.querySelector('.close-btn');
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        const plansPage = document.getElementById('plansPage');
        const comicsPage = document.getElementById('comicsPage');
        const userAvatar = document.getElementById('userAvatar');
        
        // Função genérica para abrir/fechar modal
        function toggleModal(modalId, show = true) {
            const modal = document.getElementById(modalId);
            modal.style.display = show ? 'flex' : 'none';
            document.body.style.overflow = show ? 'hidden' : 'auto';
        }

        // Abrir modal
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleModal('authModal', true);
        });
        
        // Fechar modal
        closeBtn.addEventListener('click', () => {
            toggleModal('authModal', false);
        });
        
        // Fechar modal ao clicar fora
        window.addEventListener('click', (e) => {
            if (e.target === authModal) toggleModal('authModal', false);
        });
        
        // Trocar entre abas
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Remover classe active de todas as tabs e contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Adicionar classe active na tab e content selecionados
                tab.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Formulário de login
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Validação simples
            if (!email || !password) {
                alert('Por favor, preencha todos os campos');
                return;
            }
            
            // Simular login bem-sucedido
            simulateLogin(email);
        });
        
        // Formulário de cadastro
        document.getElementById('signupForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('signupName').value;
            const email = document.getElementById('signupEmail').value;
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('signupConfirmPassword').value;
            
            // Validações
            if (!name || !email || !password || !confirmPassword) {
                alert('Por favor, preencha todos os campos');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('As senhas não coincidem!');
                return;
            }
            
            if (password.length < 6) {
                alert('A senha deve ter pelo menos 6 caracteres');
                return;
            }
            
            // Simular cadastro bem-sucedido e login automático
            simulateLogin(email, name);
        });
        
        // Simular login
        function simulateLogin(email, name = 'Usuário') {
            plansPage.style.display = 'none';
            comicsPage.style.display = 'block';
            
            // Atualizar avatar do usuário com a primeira letra do nome
            userAvatar.textContent = name.charAt(0).toUpperCase();
            
            // Fechar modal
            authModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Mudar para a aba de login após cadastro
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            tabs[0].classList.add('active');
            tabContents[0].classList.add('active');
            
            // Limpar formulários
            document.getElementById('loginForm').reset();
            document.getElementById('signupForm').reset();
            
            // Mostrar mensagem de boas-vindas
            alert(`Bem-vindo(a) ${name}!`);
        }
        
        // Dados de exemplo para quadrinhos
        const comicsData = {
            "Batman: O Cavaleiro das Trevas": {
                cover: "https://via.placeholder.com/1200x500",
                description: "A obra-prima de Frank Miller que redefiniu o Batman para sempre. Gotham está em perigo e só um Batman mais velho e sombrio pode salvar a cidade.",
                features: [
                    "Autor: Frank Miller",
                    "Ano: 1986",
                    "Gênero: Super-herói, Ação",
                    "Páginas: 224"
                ]
            },
            "Homem-Aranha: A Última Caçada": {
                cover: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTg9az9jtGfQoj20u_4hUEeTQXvX9i0W2KPKw&s",
                description: "Kraven, o Caçador, finalmente derrota o Homem-Aranha em uma das histórias mais sombrias do herói.",
                features: [
                    "Autor: J.M. DeMatteis",
                    "Ano: 1987",
                    "Gênero: Super-herói, Drama",
                    "Páginas: 160"
                ]
            },
            "Watchmen": {
                cover: "https://via.placeholder.com/200x300",
                description: "Uma revolucionária graphic novel que explora a vida de super-heróis aposentados em um mundo à beira da guerra nuclear.",
                features: [
                    "Autor: Alan Moore",
                    "Ano: 1986",
                    "Gênero: Super-herói, Drama",
                    "Páginas: 416"
                ]
            }
        };
        
        // Função para abrir o modal Netflix
        function openComicModal(title) {
            const data = comicsData[title];
            if (!data) {
                alert('Quadrinho não encontrado');
                return;
            }
            
            document.getElementById('netflixTitle').textContent = title;
            document.getElementById('netflixDescription').textContent = data.description;
            document.getElementById('netflixHero').style.backgroundImage = `url(${data.cover})`;
            document.getElementById('netflixModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Resetar abas para a primeira
            document.querySelectorAll('.netflix-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.netflix-tab-content').forEach(c => c.classList.remove('active'));
            document.querySelector('.netflix-tab').classList.add('active');
            document.querySelector('.netflix-tab-content').classList.add('active');
        }

        // Fechar modal Netflix
        document.getElementById('netflixCloseBtn').onclick = function() {
            document.getElementById('netflixModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        };

        // Trocar entre abas no modal Netflix
        document.querySelectorAll('.netflix-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                document.querySelectorAll('.netflix-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.netflix-tab-content').forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Fechar modal ao clicar fora
        window.addEventListener('click', function(e) {
            const netflixModal = document.getElementById('netflixModal');
            if (e.target === netflixModal) {
                netflixModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Clique nos cards para abrir o modal Netflix
        document.querySelectorAll('.comic-card').forEach(card => {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                const title = card.querySelector('.comic-title');
                if (title) openComicModal(title.textContent.trim());
            });
        });

        // Clique no destaque para abrir o modal Netflix
        document.querySelector('.featured-comic').addEventListener('click', function(e) {
            e.preventDefault();
            const title = document.querySelector('.featured-title');
            if (title) openComicModal(title.textContent.trim());
        });

        // Botão "Ler agora" no destaque
        document.querySelector('.featured-actions .btn-primary').addEventListener('click', function(e) {
            e.stopPropagation();
            alert('Redirecionando para o leitor...');
            // window.location.href = 'leitor.html?comic=batman-cavaleiro-das-trevas';
        });

        // Botão "Minha lista" no destaque
        document.querySelector('.featured-actions .btn-outline').addEventListener('click', function(e) {
            e.stopPropagation();
            alert('Adicionado à sua lista!');
        });

        // Categorias - filtro
        document.querySelectorAll('.category').forEach(category => {
            category.addEventListener('click', function() {
                document.querySelectorAll('.category').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                // Aqui iria a lógica para filtrar os quadrinhos
            });
        });

        // Barra de pesquisa
        document.querySelector('.search-bar button').addEventListener('click', function() {
            const searchTerm = document.querySelector('.search-bar input').value;
            if (searchTerm) {
                alert(`Buscando por: ${searchTerm}`);
                // Aqui iria a lógica de busca
            }
        });

        // Permitir busca ao pressionar Enter
        document.querySelector('.search-bar input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('.search-bar button').click();
            }
        });

        // Função para fazer login
async function loginUser(email, password) {
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro no login:', error);
        return { success: false, message: 'Erro de conexão' };
    }
}

// Função para cadastrar usuário
async function registerUser(name, email, password) {
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'signup',
                name: name,
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro no cadastro:', error);
        return { success: false, message: 'Erro de conexão' };
    }
}

// Função para carregar quadrinhos
async function loadComics(category = 'all') {
    try {
        const url = category === 'all' 
            ? 'api/comics.php?action=all' 
            : `api/comics.php?action=category&category=${category}`;
            
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            return data.comics;
        } else {
            console.error('Erro ao carregar quadrinhos:', data.message);
            return [];
        }
    } catch (error) {
        console.error('Erro ao carregar quadrinhos:', error);
        return [];
    }
}

// Ao carregar a página
document.addEventListener('DOMContentLoaded', async () => {
    const comics = await loadComics();
    renderComics(comics); // Você precisa implementar esta função
});

// Quando o usuário seleciona uma categoria
document.querySelectorAll('.category').forEach(category => {
    category.addEventListener('click', async function() {
        const categoryName = this.textContent.toLowerCase();
        const comics = await loadComics(categoryName);
        renderComics(comics);
    });
});
    </script>
</body>
</html>
