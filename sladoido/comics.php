<?php
require_once 'includes_auth.php';

if(!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_data = $auth->getUserData($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HQ Verso - Sua plataforma de quadrinhos online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
        
        /* Modo Claro */
        body.light-mode {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #e94560;
            text-decoration: none;
            letter-spacing: 1px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .search-bar {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 8px 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        body.light-mode .search-bar {
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        
        .search-bar input {
            background: transparent;
            border: none;
            color: white;
            padding: 5px 10px;
            width: 250px;
            outline: none;
        }
        
        body.light-mode .search-bar input {
            color: #333;
        }
        
        .search-bar input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        body.light-mode .search-bar input::placeholder {
            color: #718096;
        }
        
        .search-bar button {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            padding: 5px;
        }
        
        body.light-mode .search-bar button {
            color: #718096;
        }
        
        .user-avatar {
            position: relative;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #e94560;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(26, 26, 46, 0.95);
            border-radius: 8px;
            padding: 10px 0;
            min-width: 200px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        body.light-mode .user-dropdown {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .user-dropdown a {
            display: block;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        body.light-mode .user-dropdown a {
            color: #333;
        }
        
        .user-dropdown a:hover {
            background: rgba(233, 69, 96, 0.2);
        }
        
        .user-avatar:hover .user-dropdown {
            display: block;
        }
        
        .categories {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .category {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        body.light-mode .category {
            background: rgba(0, 0, 0, 0.05);
            color: #4a5568;
        }
        
        .category.active {
            background: #e94560;
            color: white;
        }
        
        .category:hover {
            background: rgba(233, 69, 96, 0.7);
        }
        
        .comic-counter {
            text-align: center;
            margin: 10px 0 20px 0;
            font-size: 14px;
            opacity: 0.8;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        body.light-mode .comic-counter {
            color: #4a5568;
        }
        
        .featured-comic {
            position: relative;
            height: 500px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #0f3460 0%, #1a1a2e 100%);
            background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_1OXqNiK7qA0H461zQkADUkI6EwQIiExA3w&s');
            background-size: cover;
            background-position: center;
        }
        
        .featured-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
            padding: 40px;
            color: white;
        }
        
        .featured-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .featured-description {
            font-size: 1.1rem;
            margin-bottom: 20px;
            opacity: 0.9;
            max-width: 600px;
        }
        
        .featured-actions {
            display: flex;
            gap: 15px;
        }
        
        .comics-section {
            margin-bottom: 50px;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        body.light-mode .section-title {
            color: #2d3748;
        }
        
        .comics-carousel {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            transition: opacity 0.3s ease;
        }
        
        .comic-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        
        body.light-mode .comic-card {
            background: rgba(255, 255, 255, 0.8);
            color: #333;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .comic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        body.light-mode .comic-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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
        
        body.light-mode .comic-info {
            color: #333;
        }
        
        .comic-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }
        
        .comic-meta {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        body.light-mode .comic-meta {
            color: #718096;
        }
        
        .card-actions {
            padding: 0 15px 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            border: none;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .btn-primary {
            background: #e94560;
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #d8345f;
        }
        
        .btn-play {
            background: #e94560;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #e94560;
            color: #e94560;
        }
        
        body.light-mode .btn-outline {
            color: #e94560;
        }
        
        .btn-outline:hover {
            background: rgba(233, 69, 96, 0.1);
        }
        
        .progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
        }
        
        body.light-mode .progress {
            background: rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            height: 100%;
            background: #e94560;
            transition: width 0.3s ease;
        }
        
        .continue-row .comics-carousel {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding-bottom: 10px;
        }
        
        .continue-row .comic-card {
            min-width: 200px;
            flex-shrink: 0;
        }
        
        .browser-info-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(15, 52, 96, 0.8);
            color: white;
            padding: 12px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        body.light-mode .browser-info-link {
            background: rgba(233, 69, 96, 0.8);
            color: white;
        }
        
        .browser-info-link:hover {
            background: rgba(15, 52, 96, 1);
            transform: translateY(-2px);
        }
        
        body.light-mode .browser-info-link:hover {
            background: rgba(233, 69, 96, 1);
        }
        
        .theme-toggle {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: rgba(15, 52, 96, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
        }
        
        body.light-mode .theme-toggle {
            background: rgba(233, 69, 96, 0.8);
            color: white;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Scrollbar personalizado */
        .continue-row .comics-carousel::-webkit-scrollbar {
            height: 8px;
        }
        
        .continue-row .comics-carousel::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        body.light-mode .continue-row .comics-carousel::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        
        .continue-row .comics-carousel::-webkit-scrollbar-thumb {
            background: #e94560;
            border-radius: 4px;
        }
        
        .no-comics {
            text-align: center;
            padding: 40px;
            opacity: 0.7;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
            }
            
            .header-right {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-bar input {
                width: 150px;
            }
            
            .featured-comic {
                height: 300px;
            }
            
            .featured-title {
                font-size: 1.8rem;
            }
            
            .featured-description {
                font-size: 1rem;
            }
            
            .comics-carousel {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .comic-cover {
                height: 220px;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="container comics-page" id="comicsPage">
        <header>
            <div class="header-left">
                <a href="#" class="logo">HQ VERSO</a>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar quadrinhos..." aria-label="Buscar quadrinhos">
                    <button type="button" aria-label="Buscar"><i class="fas fa-search"></i></button>
                </div>
                <div class="user-avatar" id="userAvatar">
                    <span class="avatar-letter"><?php echo strtoupper(substr($user_data['username'], 0, 1)); ?></span>
                   <div class="user-dropdown">
    <a href="perfil.php">Meu Perfil</a>
    <?php if($auth->isAdmin()): ?>
        <a href="admin.php">Painel Admin</a>
    <?php endif; ?>
    <a href="detection.html">Informações do Navegador</a>
    <a href="logout.php" id="logoutLink">Sair</a>
</div>
                </div>
            </div>
        </header>
        
        <div class="categories">
            <div class="category active" data-category="all">Todos</div> 
            <div class="category" data-category="super-herois">Super-heróis</div>
            <div class="category" data-category="manga">Mangá</div>
            <div class="category" data-category="graphic-novels">Graphic Novels</div>
            <div class="category" data-category="classicos">Clássicos</div>
            <div class="category" data-category="indie">Indie</div>
            <div class="category" data-category="favoritos">Favoritos</div>
        </div>
        
        <div class="comic-counter" id="comicCounter">Carregando quadrinhos...</div>
        
        <div class="featured-comic">
            <div class="featured-overlay">
                <h2 class="featured-title">Batman: O Cavaleiro das Trevas</h2>
                <p class="featured-description">A obra-prima de Frank Miller que redefiniu o Batman para sempre.</p>
                <div class="featured-actions">
                    <button class="btn btn-play" onclick="window.location.href='leitor.html?comic=batman&title=Batman: O Cavaleiro das Trevas'">
                        <i class="fas fa-play"></i> Ler agora
                    </button>
                    <button class="btn btn-outline">
                        <i class="fas fa-plus"></i> Minha lista
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Seção Continuar Lendo -->
        <div class="comics-section continue-row">
            <h3 class="section-title">Continuar Lendo</h3>
            <div class="comics-carousel" id="continueReading">
                <div class="no-comics">Nenhum quadrinho em progresso</div>
            </div>
        </div>
        
        <!-- Seção Recomendados -->
        <div class="comics-section">
            <h3 class="section-title">Recomendados para você</h3>
            <div class="comics-carousel" id="recommendedComics">
                <div class="no-comics">Carregando recomendações...</div>
            </div>
        </div>
        
        <!-- Seção Clássicos da DC -->
        <div class="comics-section">
            <h3 class="section-title">Clássicos da DC</h3>
            <div class="comics-carousel" id="dcClassics">
                <div class="no-comics">Carregando clássicos...</div>
            </div>
        </div>
        
        <!-- Seção Mangás Populares -->
        <div class="comics-section">
            <h3 class="section-title">Mangás Populares</h3>
            <div class="comics-carousel" id="popularManga">
                <div class="no-comics">Carregando mangás...</div>
            </div>
        </div>
        
        <!-- Seção Graphic Novels -->
        <div class="comics-section">
            <h3 class="section-title">Graphic Novels</h3>
            <div class="comics-carousel" id="graphicNovels">
                <div class="no-comics">Carregando graphic novels...</div>
            </div>
        </div>
    </div>

    <a href="detection.html" class="browser-info-link">
        <i class="fas fa-info-circle"></i> Info Navegador
    </a>

    <script>
        // Dados completos dos quadrinhos
        const comicsData = {
            "all": [
                {
                    id: 1,
                    title: "Homem-Aranha: A Última Caçada",
                    cover: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTg9az9jtGfQoj20u_4hUEeTQXvXvX9i0W2KPKw&s",
                    meta: "Capítulo 3",
                    progress: 20,
                    categories: ["super-herois", "classicos"],
                    description: "A clássica história onde o Homem-Aranha enfrenta seu maior desafio."
                },
                {
                    id: 2,
                    title: "Watchmen",
                    cover: "https://upload.wikimedia.org/wikipedia/pt/d/d0/Watchmen.jpg",
                    meta: "Página 45",
                    progress: 45,
                    categories: ["super-herois", "graphic-novels", "classicos"],
                    description: "A revolucionária graphic novel que questiona a natureza dos super-heróis."
                },
                {
                    id: 3,
                    title: "Sandman: Prelúdios e Noturnos",
                    cover: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcReNzUJRFsrqKb-Y4UIxg-jUSPYg-4ermAT3w&s",
                    meta: "Volume 1",
                    progress: 60,
                    categories: ["graphic-novels", "classicos"],
                    description: "A primeira coleção da aclamada série de Neil Gaiman."
                },
                {
                    id: 4,
                    title: "V de Vingança",
                    cover: "https://m.media-amazon.com/images/I/711dLCQ6kuL._UF1000,1000_QL80_.jpg",
                    meta: "50% lido",
                    progress: 50,
                    categories: ["graphic-novels", "classicos"],
                    description: "A distópica graphic novel sobre anarquia e liberdade."
                },
                {
                    id: 5,
                    title: "Maus",
                    cover: "https://m.media-amazon.com/images/I/916IgqQ-54L.jpg",
                    meta: "Art Spiegelman",
                    categories: ["graphic-novels", "classicos"],
                    description: "A premiada graphic novel sobre o Holocausto."
                },
                {
                    id: 6,
                    title: "Persépolis",
                    cover: "https://m.media-amazon.com/images/I/814zhAWOKBL._UF1000,1000_QL80_.jpg",
                    meta: "Marjane Satrapi",
                    categories: ["graphic-novels", "classicos"],
                    description: "A autobiografia em quadrinhos sobre o Irã revolucionário."
                },
                {
                    id: 7,
                    title: "Hellboy: Caçada Selvagem",
                    cover: "https://images.tcdn.com.br/img/img_prod/1119494/hellboy_omnibus_vol_3_1709745_1_a6e86cfa8f53f219f4d0d0b0c4f79558.jpg",
                    meta: "Mike Mignola",
                    categories: ["super-herois", "indie"],
                    description: "As primeiras aventuras do demônio herói."
                },
                {
                    id: 8,
                    title: "Saga",
                    cover: "https://m.media-amazon.com/images/I/81s49EEptML.jpg",
                    meta: "Brian K. Vaughan",
                    categories: ["graphic-novels", "indie"],
                    description: "A épica space opera de ficção científica."
                },
                {
                    id: 9,
                    title: "Superman: Terra Um",
                    cover: "https://m.media-amazon.com/images/I/91wpPruCKrL._UF1000,1000_QL80_.jpg",
                    meta: "2010",
                    categories: ["super-herois", "classicos"],
                    description: "Uma reinterpretação moderna do Homem de Aço."
                },
                {
                    id: 10,
                    title: "Liga da Justiça: A Torre de Babel",
                    cover: "https://super.abril.com.br/wp-content/uploads/2018/07/torredebabel.jpg",
                    meta: "2000",
                    categories: ["super-herois", "classicos"],
                    description: "Quando Batman se torna a maior ameaça da Liga."
                },
                {
                    id: 11,
                    title: "Akira",
                    cover: "https://m.media-amazon.com/images/I/81K1+Z+Yf+L.jpg",
                    meta: "Katsuhiro Otomo",
                    categories: ["manga", "classicos"],
                    description: "A épica cyberpunk que revolucionou os mangás."
                },
                {
                    id: 12,
                    title: "Death Note",
                    cover: "https://m.media-amazon.com/images/I/81MZ6eFQsfL.jpg",
                    meta: "Tsugumi Ohba",
                    categories: ["manga"],
                    description: "Um estudante genius encontra um caderno que pode matar pessoas."
                },
                {
                    id: 13,
                    title: "Attack on Titan",
                    cover: "https://m.media-amazon.com/images/I/81d6e+kN5+L.jpg",
                    meta: "Hajime Isayama",
                    categories: ["manga"],
                    description: "Humanidade luta pela sobrevivência contra titãs gigantes."
                },
                {
                    id: 14,
                    title: "One-Punch Man",
                    cover: "https://m.media-amazon.com/images/I/81I1+-+0R0L.jpg",
                    meta: "ONE",
                    categories: ["manga", "super-herois"],
                    description: "Um herói tão forte que derrota qualquer inimigo com um só soco."
                },
                {
                    id: 15,
                    title: "Scott Pilgrim",
                    cover: "https://m.media-amazon.com/images/I/81K1+Z+Yf+L.jpg",
                    meta: "Bryan Lee O'Malley",
                    categories: ["indie", "graphic-novels"],
                    description: "Um baixista deve derrotar os 7 ex-namorados malvados de sua amada."
                },
                {
                    id: 16,
                    title: "Batman: Ano Um",
                    cover: "https://m.media-amazon.com/images/I/81zK5OjR5aL.jpg",
                    meta: "Frank Miller",
                    categories: ["super-herois", "classicos"],
                    description: "A origem definitiva do Cavaleiro das Trevas."
                },
                {
                    id: 17,
                    title: "X-Men: Fênix Negra",
                    cover: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSmC8oS-dR6n1EW5YxEnvL0mPaz13taAKsbvQ&s",
                    meta: "Chris Claremont",
                    categories: ["super-herois", "classicos"],
                    description: "A épica saga onde Jean Grey se torna a Fênix Negra."
                },
                {
                    id: 18,
                    title: "Monstress",
                    cover: "https://m.media-amazon.com/images/I/81bGs636lzL.jpg",
                    meta: "Marjorie Liu",
                    categories: ["graphic-novels", "indie"],
                    description: "Fantasia sombria em um mundo de guerra e monstros."
                }
            ],
            "super-herois": [1, 2, 7, 9, 10, 14, 16, 17],
            "manga": [11, 12, 13, 14],
            "graphic-novels": [2, 3, 4, 5, 6, 8, 15, 18],
            "classicos": [1, 2, 3, 4, 5, 6, 9, 10, 11, 16, 17],
            "indie": [7, 8, 15, 18],
            "favoritos": [1, 3, 5, 8, 11, 14, 16]
        };

        // Função para criar card de quadrinho
        function createComicCard(comic, showProgress = false) {
            const placeholder = `https://via.placeholder.com/200x300/1a1a2e/e94560?text=${encodeURIComponent(comic.title.substring(0, 15))}`;
            
            return `
                <div class="comic-card" data-id="${comic.id}" data-categories="${comic.categories.join(',')}">
                    <img src="${comic.cover}" 
                         alt="Capa do quadrinho ${comic.title}" 
                         class="comic-cover"
                         onerror="this.src='${placeholder}'">
                    <div class="comic-info">
                        <div class="comic-title">${comic.title}</div>
                        <div class="comic-meta">${comic.meta}</div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary read-now" 
                                onclick="window.location.href='leitor.html?comic=${comic.id}&title=${encodeURIComponent(comic.title)}&progress=${comic.progress || 0}'">
                            Ler agora
                        </button>
                    </div>
                    ${showProgress && comic.progress ? `
                    <div class="progress"><div class="progress-bar" style="width:${comic.progress}%"></div></div>
                    ` : ''}
                </div>
            `;
        }

        // Função para filtrar quadrinhos por categoria
        function filterComicsByCategory(category) {
            const allComics = comicsData.all;
            let filteredComics = [];
            
            if (category === 'all') {
                filteredComics = allComics;
            } else if (category === 'favoritos') {
                const favoriteIds = comicsData.favoritos;
                filteredComics = allComics.filter(comic => favoriteIds.includes(comic.id));
            } else {
                const categoryIds = comicsData[category] || [];
                filteredComics = allComics.filter(comic => categoryIds.includes(comic.id));
            }
            
            return filteredComics;
        }

        // Função para renderizar quadrinhos em uma seção
        function renderComicsInSection(sectionId, comics, showProgress = false) {
            const section = document.getElementById(sectionId);
            if (!section) return;
            
            if (comics.length === 0) {
                section.innerHTML = '<div class="no-comics">Nenhum quadrinho encontrado</div>';
                return;
            }
            
            section.innerHTML = '';
            
            comics.forEach(comic => {
                section.innerHTML += createComicCard(comic, showProgress);
            });
            
            applyCardHoverEffects();
        }

        // Função para atualizar todas as seções baseado na categoria
        function updateAllSections(category) {
            const filteredComics = filterComicsByCategory(category);
            
            // Seção Continuar Lendo - quadrinhos com progresso
            const continueReadingComics = filteredComics.filter(comic => comic.progress && comic.progress > 0);
            renderComicsInSection('continueReading', continueReadingComics.slice(0, 4), true);
            
            // Seção Recomendados - seleção variada
            const recommendedComics = filteredComics.slice(0, 4);
            renderComicsInSection('recommendedComics', recommendedComics);
            
            // Seção Clássicos DC - super-heróis da DC
            const dcComics = filteredComics.filter(comic => 
                comic.categories.includes('super-herois') && 
                (comic.title.includes('Batman') || comic.title.includes('Superman') || comic.title.includes('Liga'))
            );
            renderComicsInSection('dcClassics', dcComics.slice(0, 3));
            
            // Seção Mangás Populares
            const mangaComics = filteredComics.filter(comic => 
                comic.categories.includes('manga')
            );
            renderComicsInSection('popularManga', mangaComics.slice(0, 4));
            
            // Seção Graphic Novels
            const graphicNovels = filteredComics.filter(comic => 
                comic.categories.includes('graphic-novels')
            );
            renderComicsInSection('graphicNovels', graphicNovels.slice(0, 4));
            
            // Atualizar contador
            updateComicCount(filteredComics.length);
        }

        // Função para atualizar contador de quadrinhos
        function updateComicCount(count) {
            const counter = document.getElementById('comicCounter');
            if (counter) {
                counter.textContent = `${count} quadrinhos encontrados`;
            }
        }

        // Função para aplicar efeitos hover nos cards
        function applyCardHoverEffects() {
            document.querySelectorAll('.comic-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        }

        // Sistema de filtro por categoria
        function setupCategoryFilter() {
            document.querySelectorAll('.category').forEach(category => {
                category.addEventListener('click', function() {
                    // Atualizar categoria ativa
                    document.querySelectorAll('.category').forEach(cat => cat.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Obter categoria
                    const categoryType = this.getAttribute('data-category');
                    
                    // Animação de transição
                    document.querySelectorAll('.comics-carousel').forEach(carousel => {
                        carousel.style.opacity = '0.7';
                        setTimeout(() => {
                            carousel.style.opacity = '1';
                        }, 300);
                    });
                    
                    // Atualizar quadrinhos
                    updateAllSections(categoryType);
                });
            });
        }

        // Sistema de busca
        function setupSearch() {
            const searchInput = document.querySelector('.search-bar input');
            const searchButton = document.querySelector('.search-bar button');
            
            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
            
            function performSearch() {
                const searchTerm = searchInput.value.trim().toLowerCase();
                if (searchTerm) {
                    const allComics = comicsData.all;
                    const filteredComics = allComics.filter(comic => 
                        comic.title.toLowerCase().includes(searchTerm) ||
                        comic.description.toLowerCase().includes(searchTerm) ||
                        comic.meta.toLowerCase().includes(searchTerm)
                    );
                    
                    // Atualizar todas as seções com os resultados da busca
                    const continueReadingComics = filteredComics.filter(comic => comic.progress && comic.progress > 0);
                    renderComicsInSection('continueReading', continueReadingComics.slice(0, 4), true);
                    renderComicsInSection('recommendedComics', filteredComics.slice(0, 4));
                    renderComicsInSection('dcClassics', filteredComics.slice(0, 3));
                    renderComicsInSection('popularManga', filteredComics.slice(0, 4));
                    renderComicsInSection('graphicNovels', filteredComics.slice(0, 4));
                    
                    updateComicCount(filteredComics.length);
                    
                    // Mostrar mensagem de busca
                    document.getElementById('comicCounter').textContent = `${filteredComics.length} resultados para "${searchTerm}"`;
                }
            }
        }

        // Sistema de Tema
        function setupTheme() {
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            
            const savedTheme = localStorage.getItem('hq-verso-theme');
            if (savedTheme === 'light') {
                body.classList.add('light-mode');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
            
            themeToggle.addEventListener('click', () => {
                body.classList.toggle('light-mode');
                
                if (body.classList.contains('light-mode')) {
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                    localStorage.setItem('hq-verso-theme', 'light');
                } else {
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                    localStorage.setItem('hq-verso-theme', 'dark');
                }
            });
        }

        // Inicializar a página
        function initializePage() {
            setupTheme();
            setupCategoryFilter();
            setupSearch();
            updateAllSections('all');
            applyCardHoverEffects();
            
            console.log('Sistema de quadrinhos inicializado!');
        }

        // Inicializar quando a página carregar
        document.addEventListener('DOMContentLoaded', initializePage);
    </script>
</body>
</html>