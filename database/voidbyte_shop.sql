-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21/09/2026 às 21:39
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
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome_destinatario` varchar(255) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `forma_pagamento` varchar(30) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `frete` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'confirmado',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_itens`
--

CREATE TABLE `pedido_itens` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `nome_produto` varchar(255) NOT NULL,
  `imagem_produto` varchar(255) DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `quantidade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `especificacoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`especificacoes`)),
  `desconto` decimal(5,2) DEFAULT 0.00,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `imagem`, `categoria`, `especificacoes`, `desconto`, `data_criacao`) VALUES
(1, 'Teclado Mecânico Void Minimalist', 'Switches silenciosos, chassi de alumínio escovado e retroiluminação roxa.', 599.90, './assets/imgs/default-hardware.png', 'Teclados Mecânicos', NULL, 15.00, '2026-06-08 17:37:58'),
(2, 'Mousepad Neural Cyber', 'Superfície de microfibra escura com bordas costuradas em fio óptico.', 129.90, './assets/imgs/default-hardware.png', 'Mouses Cyber', NULL, 0.00, '2026-06-08 17:37:58'),
(3, 'Monitor Dark Mode 144Hz', 'Painel IPS de 27 polegadas com calibração profunda para níveis de preto perfeitos.', 1499.90, './assets/imgs/default-hardware.png', 'Monitores', NULL, 0.00, '2026-06-08 17:37:58'),
(5, 'Mouse Do Void', 'teste', 300.00, './assets/imgs/default-hardware.png', 'Mouses Cyber', '{\"Sensor\": \"Óptico 16K\", \"Peso\": \"65g\", \"Botões\": \"6\", \"Conexão\": \"Wireless\"}', 0.00, '2026-06-09 19:53:38'),
(6, 'Mouse Cyberpunk', 'teste 2', 250.00, './assets/imgs/default-hardware.png', 'Mouses Cyber', '{\"Sensor\": \"Laser 25K\", \"Peso\": \"80g\", \"Botões\": \"8\", \"Conexão\": \"Cabo USB-C\"}', 0.00, '2026-06-09 19:54:45');

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
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `foto`, `data_criacao`, `reset_token`, `reset_expiracao`) VALUES
(1, 'Admin Supremo', 'admin@voidbyte.com', '$2y$10$ScaQHj1CSqoZW7392TpAlukwVZzKok.nlxmaIv9VzXpL5.F3BoRmC', 'admin', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-06-08 17:41:31', NULL, NULL),
(2, 'Ian Gabriel', 'ianbielbia223@gmail.com', '$2y$10$htvoCKHwF3wn0IoxTo5ZfuJ.Wj6ki1UAvJtT1v5.AEJPR3KyiZ/Fy', 'cliente', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-06-08 18:27:03', NULL, NULL),
(3, 'Amanda', 'amanda123@gmail.com', '$2y$10$3DaL1hsHo/MJh9ta1NZ0M.CsSMwzddgjjMZ50WDlx5TPRlX7SXysW', 'cliente', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-09-21 19:36:15', NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`);

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
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `fk_item_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
