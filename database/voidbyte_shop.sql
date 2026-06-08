-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/06/2026 às 00:18
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `voidbyte_shop`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) NOT NULL DEFAULT './assets/imgs/default-hardware.png',
  `categoria` varchar(100) NOT NULL,
  `desconto` decimal(5,2) DEFAULT 0.00,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `imagem`, `categoria`, `desconto`, `data_criacao`) VALUES
(1, 'Teclado Mecânico Void Minimalist', 'Switches silenciosos, chassi de alumínio escovado e retroiluminação roxa.', 599.90, './assets/imgs/default-hardware.png', 'Teclados Mecânicos', 15.00, '2026-06-08 17:37:58'),
(2, 'Mousepad Neural Cyber', 'Superfície de microfibra escura com bordas costuradas em fio óptico.', 129.90, './assets/imgs/default-hardware.png', 'Mouses Cyber', 0.00, '2026-06-08 17:37:58'),
(3, 'Monitor Dark Mode 144Hz', 'Painel IPS de 27 polegadas com calibração profunda para níveis de preto perfeitos.', 1499.90, './assets/imgs/default-hardware.png', 'Monitores', 0.00, '2026-06-08 17:37:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','cliente') DEFAULT 'cliente',
  `foto` varchar(255) DEFAULT 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `foto`, `data_criacao`) VALUES
(1, 'Admin Supremo', 'admin@voidbyte.com', '$2y$10$gNYkngzFsh20vrc8hnMnNOp8Iyr/60r2wQxBC9Yz6TF3QllWCVKJu', 'admin', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-06-08 17:41:31'),
(2, 'Ian Gabriel', 'ianbielbia223@gmail.com', '$2y$10$htvoCKHwF3wn0IoxTo5ZfuJ.Wj6ki1UAvJtT1v5.AEJPR3KyiZ/Fy', 'cliente', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-06-08 18:27:03');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
