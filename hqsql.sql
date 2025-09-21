CREATE DATABASE IF NOT EXISTS `hqsql` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `hqsql`;

-- Tabela de Editoras
CREATE TABLE IF NOT EXISTS `publishers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `avatar_file_name` VARCHAR(255) DEFAULT NULL,
  `avatar_file_size` INT DEFAULT NULL,
  `avatar_mime_type` VARCHAR(50) DEFAULT NULL,
  `avatar_updated_at` TIMESTAMP NULL DEFAULT NULL,
  `bio` TEXT,
  `role` ENUM('user', 'creator', 'moderator', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_email` (`email`),
  INDEX `idx_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Quadrinhos
CREATE TABLE IF NOT EXISTS `comics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author_id` INT NOT NULL,
  `publisher_id` INT DEFAULT NULL,
  `cover` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `is_published` BOOLEAN DEFAULT FALSE,
  `is_premium` BOOLEAN DEFAULT FALSE,
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  `release_date` DATE DEFAULT NULL,
  `page_count` INT DEFAULT 0,
  `views` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`publisher_id`) REFERENCES `publishers`(`id`) ON DELETE SET NULL,
  INDEX `idx_comics_title` (`title`),
  INDEX `idx_comics_author` (`author_id`),
  INDEX `idx_comics_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Relação Comics-Categories
CREATE TABLE IF NOT EXISTS `comic_categories` (
  `comic_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`comic_id`, `category_id`),
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  INDEX `idx_comic_cat_comic` (`comic_id`),
  INDEX `idx_comic_cat_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Páginas dos Quadrinhos
CREATE TABLE IF NOT EXISTS `comic_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `comic_id` INT NOT NULL,
  `page_number` INT NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  INDEX `idx_pages_comic` (`comic_id`),
  INDEX `idx_pages_number` (`page_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Transações/Compras
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `comic_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('credit_card', 'debit_card', 'paypal', 'crypto') NOT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `transaction_id` VARCHAR(255) UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  INDEX `idx_transactions_user` (`user_id`),
  INDEX `idx_transactions_status` (`status`),
  INDEX `idx_transactions_comic` (`comic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Biblioteca do Usuário
CREATE TABLE IF NOT EXISTS `user_library` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `comic_id` INT NOT NULL,
  `purchased_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `transaction_id` INT NOT NULL,
  UNIQUE KEY `user_comic` (`user_id`, `comic_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE,
  INDEX `idx_library_user` (`user_id`),
  INDEX `idx_library_comic` (`comic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Fóruns
CREATE TABLE IF NOT EXISTS `forums` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `category_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_forums_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Tópicos
CREATE TABLE IF NOT EXISTS `topics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `user_id` INT NOT NULL,
  `forum_id` INT NOT NULL,
  `comic_id` INT DEFAULT NULL,
  `is_pinned` BOOLEAN DEFAULT FALSE,
  `is_locked` BOOLEAN DEFAULT FALSE,
  `view_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`forum_id`) REFERENCES `forums`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE SET NULL,
  INDEX `idx_topics_user` (`user_id`),
  INDEX `idx_topics_forum` (`forum_id`),
  INDEX `idx_topics_comic` (`comic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Posts/Respostas
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `content` TEXT NOT NULL,
  `user_id` INT NOT NULL,
  `topic_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`topic_id`) REFERENCES `topics`(`id`) ON DELETE CASCADE,
  INDEX `idx_posts_user` (`user_id`),
  INDEX `idx_posts_topic` (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Reações/Likes
CREATE TABLE IF NOT EXISTS `reactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `post_id` INT NOT NULL,
  `type` ENUM('like', 'love', 'laugh', 'wow', 'sad', 'angry') DEFAULT 'like',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_post_reaction` (`user_id`, `post_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  INDEX `idx_reactions_user` (`user_id`),
  INDEX `idx_reactions_post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Progresso de Leitura
CREATE TABLE IF NOT EXISTS `reading_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `comic_id` INT NOT NULL,
  `current_page` INT NOT NULL DEFAULT 1,
  `is_completed` BOOLEAN DEFAULT FALSE,
  `last_read` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reading_time` INT DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_comic` (`user_id`, `comic_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  INDEX `idx_reading_user` (`user_id`),
  INDEX `idx_reading_comic` (`comic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Favoritos
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `comic_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_comic_fav` (`user_id`, `comic_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  INDEX `idx_favorites_user` (`user_id`),
  INDEX `idx_favorites_comic` (`comic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Avaliações
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `comic_id` INT NOT NULL,
  `rating` TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `title` VARCHAR(255) DEFAULT NULL,
  `content` TEXT,
  `is_verified_purchase` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_comic_review` (`user_id`, `comic_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  INDEX `idx_reviews_comic` (`comic_id`),
  INDEX `idx_reviews_rating` (`rating`),
  INDEX `idx_reviews_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Comentários nas HQs
CREATE TABLE IF NOT EXISTS `comic_comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `comic_id` INT NOT NULL,
  `page_number` INT DEFAULT NULL,
  `content` TEXT NOT NULL,
  `parent_comment_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_comment_id`) REFERENCES `comic_comments`(`id`) ON DELETE CASCADE,
  INDEX `idx_comments_user` (`user_id`),
  INDEX `idx_comments_comic` (`comic_id`),
  INDEX `idx_comments_parent` (`parent_comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Rascunhos
CREATE TABLE IF NOT EXISTS `comic_drafts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `comic_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` JSON NOT NULL,
  `version` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  INDEX `idx_drafts_comic` (`comic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Colaboradores
CREATE TABLE IF NOT EXISTS `comic_collaborators` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `comic_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` ENUM('author', 'illustrator', 'colorist', 'letterer', 'editor') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `comic_user_role` (`comic_id`, `user_id`, `role`),
  FOREIGN KEY (`comic_id`) REFERENCES `comics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_collaborators_comic` (`comic_id`),
  INDEX `idx_collaborators_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Uploads de Arquivos
CREATE TABLE IF NOT EXISTS `user_uploads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `upload_type` ENUM('avatar', 'comic_cover', 'comic_page', 'other') DEFAULT 'other',
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_uploads_user` (`user_id`),
  INDEX `idx_uploads_type` (`upload_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir Dados Iniciais
INSERT INTO `categories` (`name`, `description`) VALUES
('Super-heróis', 'Quadrinhos de super-heróis da Marvel, DC e outras editoras'),
('Mangá', 'Quadrinhos japoneses'),
('Graphic Novels', 'Romances gráficos e histórias completas'),
('Clássicos', 'Quadrinhos clássicos e históricos'),
('Indie', 'Quadrinhos independentes'),
('Horror', 'Quadrinhos de terror e suspense'),
('Ficção Científica', 'Quadrinhos de ficção científica'),
('Fantasia', 'Quadrinhos de fantasia e medieval'),
('Humor', 'Quadrinhos cômicos e de humor'),
('Aventura', 'Quadrinhos de aventura e ação');

INSERT INTO `publishers` (`name`, `description`) VALUES
('DC Comics', 'Detective Comics - Publicadora de Batman, Superman, Mulher-Maravilha'),
('Marvel Comics', 'Publicadora de Homem-Aranha, X-Men, Vingadores'),
('Dark Horse', 'Editora independente de Hellboy, Sin City'),
('Image Comics', 'Editora de quadrinhos independentes'),
('Vertigo', 'Selos de quadrinhos maduros da DC'),
('Panini', 'Editora brasileira de quadrinhos'),
('Devir', 'Editora brasileira de quadrinhos'),
('Manga', 'Editoras de mangás variados'),
('Independente', 'Publicações independentes'),
('Outras', 'Outras editoras');

-- Inserir um usuário admin padrão
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@hqverso.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Criar alguns fóruns iniciais
INSERT INTO `forums` (`name`, `description`, `category_id`) VALUES
('Geral', 'Discussões gerais sobre quadrinhos', NULL),
('Novidades', 'Lançamentos e novidades do mundo dos quadrinhos', NULL),
('Reviews', 'Análises e críticas de quadrinhos', NULL),
('Dúvidas', 'Tire suas dúvidas sobre quadrinhos', NULL),
('Criação', 'Discussões sobre criação de HQs', NULL);

-- Índices Adicionais para Otimização
CREATE INDEX `idx_users_created` ON `users` (`created_at`);
CREATE INDEX `idx_comics_created` ON `comics` (`created_at`);
CREATE INDEX `idx_comics_price` ON `comics` (`price`);
CREATE INDEX `idx_transactions_created` ON `transactions` (`created_at`);
CREATE INDEX `idx_topics_created` ON `topics` (`created_at`);
CREATE INDEX `idx_posts_created` ON `posts` (`created_at`);
