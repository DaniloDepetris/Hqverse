CREATE DATABASE IF NOT EXISTS `hqsql` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `hqsql`;

-- Tabela de quadrinhos
CREATE TABLE IF NOT EXISTS `comics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255),
  `cover` VARCHAR(255),
  `description` TEXT,
  `category` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de páginas
CREATE TABLE IF NOT EXISTS `comic_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comic_id` int(11) NOT NULL,
  `page_number` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comic_id` (`comic_id`),
  CONSTRAINT `comic_pages_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de usuários (opcional)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de progresso de leitura (opcional)
CREATE TABLE IF NOT EXISTS `reading_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `current_page` int(11) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_comic` (`user_id`,`comic_id`),
  KEY `comic_id` (`comic_id`),
  CONSTRAINT `reading_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reading_progress_ibfk_2` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dados iniciais (exemplos)
INSERT INTO `comics` (`title`, `author`, `cover`, `category`, `description`) VALUES
('Batman: O Cavaleiro das Trevas', 'Frank Miller', 'batman-cover.jpg', 'super-heroi,dc-comics,classico', 'A revolucionária graphic novel que redefiniu o Batman.'),
('Watchmen', 'Alan Moore', 'watchmen-cover.jpg', 'super-heroi,dc-comics,graphic-novel', 'A obra-prima de Alan Moore que reinventou os super-heróis.');

INSERT INTO `comic_pages` (`comic_id`, `page_number`, `image_url`) VALUES
(1, 1, 'batman/page1.jpg'),
(1, 2, 'batman/page2.jpg'),
(2, 1, 'watchmen/page1.jpg');
