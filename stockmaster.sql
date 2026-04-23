-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 03 avr. 2026 à 17:54
-- Version du serveur : 10.4.27-MariaDB
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `stockmaster`
--

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `date_commande` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

CREATE TABLE `fournisseurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fournisseurs`
--

INSERT INTO `fournisseurs` (`id`, `nom`, `telephone`, `email`) VALUES
(2, 'steve', '683262340', 'jeff@gmail.com'),
(5, 'Nkeimi steve', '687443647', 'jefft068@gmail.com'),
(7, 'steve nk', '683262340', 'jefftchatchoua068@gmail.com');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `order_date`, `total_amount`, `status`) VALUES
(12, 'CMD 008', 'Nkeimi', '2026-03-15 18:07:48', '26258.00', 'pending'),
(13, 'CMD 000', 'Nkeimi', '2026-03-15 18:11:36', '12145.00', 'pending'),
(14, 'CMD 003', 'nana', '2026-03-23 03:49:26', '12500.00', 'completed'),
(15, 'CMD 007', 'nana', '2026-03-24 06:23:20', '12500.00', 'pending'),
(17, 'CMD-1775230618', 'nana', '2026-04-03 16:37:15', '250000.00', 'completed');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `quantite` int(11) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp(),
  `categorie` varchar(100) DEFAULT NULL,
  `entrepot` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `quantite`, `prix`, `date_ajout`, `categorie`, `entrepot`) VALUES
(30, 'routeurAA', 4, '15000.00', '2026-03-14 01:04:20', 'lenovo', 'mag1'),
(34, 'Souris BT', 1, '2000.00', '2026-03-14 11:17:59', 'hp', 'mag1'),
(38, 'PC Portable', 18, '50000.00', '2026-03-15 15:02:42', 'lenovo', 'mag2'),
(49, 'clavier Dell', 14, '2500.00', '2026-03-16 20:54:27', 'dell', 'mag3'),
(52, 'imprimante NETZERO', 12, '72500.00', '2026-03-23 04:34:44', 'lenovo', 'mag3');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secteur` varchar(50) DEFAULT NULL,
  `profil_image` varchar(255) DEFAULT 'default_user.png',
  `role` enum('admin','manager','staff') DEFAULT 'staff',
  `avatar_url` varchar(255) DEFAULT 'default.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Nom_user` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `secteur`, `profil_image`, `role`, `avatar_url`, `last_login`, `created_at`, `Nom_user`) VALUES
(43, 'jeansdupond068@gmail.com', '$2y$10$uegitNiL5LKe5azTc5t56ud84GGBnzpZOoLNXMB8mNVKoBQ8p1Fgu', 'employe', '1774205024_69c03860551d3.png', '', 'default.png', NULL, '2026-03-22 18:43:44', 'jean dupond'),
(63, 'marielouis068@gmail.com', '$2y$10$B4x54L.wJwEVkpKiXfQwHuFcjWa4BwI7aJ6mNDWGaOV8WdwhNZKG.', 'employe', 'img_1774239421.jpg', '', 'default.png', NULL, '2026-03-22 19:42:18', 'Marie-Louise'),
(66, 'jefftchatchoua068@gmail.com', '$2y$10$OJCysGgEzN7L1b8anBdoseEfSpHySkwn/cCWUHqd2N2mdLRzERyhe', 'chefss', 'img_1774411311.jpg', 'staff', 'default.png', NULL, '2026-03-23 04:20:28', 'NKEIMI'),
(69, 'Steve@gmail.com', '$2y$10$VpcOJzmSqvvfYRiqDHHUFOPfOUo3Q9xDs4XQbawRI9fU.BC8WaanO', 'chefss', 'img_1774411976.jpg', 'staff', 'default.png', NULL, '2026-03-25 04:12:24', 'Steve');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD UNIQUE KEY `order_number_2` (`order_number`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
