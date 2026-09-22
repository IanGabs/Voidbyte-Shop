-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 22/09/2026 às 20:33
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

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `nome_destinatario`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cep`, `forma_pagamento`, `subtotal`, `frete`, `total`, `status`, `data_criacao`) VALUES
(1, 3, 'Amanda', 'Rua Pão de Queijo', '67', '', 'Goiabada', 'Sobremesa', 'SP', '18341-973', 'pix', 250.00, 29.90, 279.90, 'confirmado', '2026-09-22 00:56:53');

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

--
-- Despejando dados para a tabela `pedido_itens`
--

INSERT INTO `pedido_itens` (`id`, `pedido_id`, `produto_id`, `nome_produto`, `imagem_produto`, `preco_unitario`, `quantidade`) VALUES
(1, 1, 6, 'Mouse Cyberpunk', './assets/imgs/default-hardware.png', 250.00, 1);

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
(6, 'Mouse Cyberpunk', 'teste 2', 250.00, './assets/imgs/default-hardware.png', 'Mouses Cyber', '{\"Sensor\": \"Laser 25K\", \"Peso\": \"80g\", \"Botões\": \"8\", \"Conexão\": \"Cabo USB-C\"}', 0.00, '2026-06-09 19:54:45'),
(7, 'Teclado Mecânico Shadow Code TKL', 'Formato TKL ideal para maximizar o espaço na mesa. Switches táteis Brown proporcionam feedback preciso sem ruído excessivo, perfeito para longas sessões de código em escritórios partilhados.', 749.90, './assets/imgs/products/hw_6ab2c12c049f6.jpg', 'Teclados Mecânicos', '{\"Switch\":\"Brown Tátil\",\"Layout\":\"ISO-PT\",\"Conexão\":\"Wireless\\/Bluetooth\",\"Iluminação\":\"Branca Minimalista\",\"Material\":\"Alumínio\",\"Taxa de Resposta (ms)\":\"1\"}', 10.00, '2026-09-22 17:55:56'),
(8, 'Monitor Dev Ultrawide 34\" Eclipse', 'Ecrã ultrawide com painel VA e contraste profundo. Permite visualizar múltiplas janelas da IDE, o terminal e a documentação em simultâneo, eliminando a necessidade de um segundo monitor.', 2899.00, './assets/imgs/products/hw_6ab2c2478e32d.png', 'Monitores', '{\"Tamanho (pol)\":\"34\",\"Resolução\":\"3440x1440\",\"Painel\":\"VA\",\"Taxa de Atualização (Hz)\":\"100\",\"Tempo de Resposta (ms)\":\"4\",\"Conexões\":\"HDMI, DisplayPort, USB-C\"}', 0.00, '2026-09-22 18:00:39'),
(9, 'SSD NVMe VoidDrive 2TB', 'Velocidade extrema de leitura e escrita, concebido para reduzir a zero os tempos de carregamento de ambientes virtuais, contentores Docker e bases de dados locais pesadas.', 950.00, './assets/imgs/products/hw_6ab2c2f385582.png', 'Armazenamento (SSD/NVMe)', '{\"Capacidade (GB)\":\"2000\",\"Tipo\":\"NVMe M.2\",\"Interface\":\"PCIe 4.0\",\"Leitura (MB\\/s)\":\"7300\",\"Escrita (MB\\/s)\":\"6800\"}', 5.00, '2026-09-22 18:03:31'),
(10, 'Cadeira Ergonómica Nocturne', 'Construída com malha mesh preta totalmente respirável. Inclui suporte lombar e cervical ajustável para garantir o conforto absoluto durante compilações demoradas.', 1599.90, './assets/imgs/products/hw_6ab2c3a3eca6f.png', 'Cadeiras Ergonômicas', '{\"Peso Suportado (kg)\":\"130\",\"Material\":\"Malha Mesh\",\"Reclinação\":\"135°\",\"Apoio Lombar\":\"Ajustável 3D\"}', 15.00, '2026-09-22 18:06:27'),
(11, 'Headset Stealth Focus', 'Isolamento acústico premium e cancelamento ativo de ruído (ANC). Ideal para bloquear distrações e manter o foco na resolução de bugs complexos.', 450.00, './assets/imgs/products/hw_6ab2c43270852.png', 'Headsets e Áudio', '{\"Driver (mm)\":\"50\",\"Conexão\":\"Wireless 2.4GHz\",\"Microfone\":\"Destacável\",\"Surround\":\"Estéreo\",\"Peso (g)\":\"285\"}', 0.00, '2026-09-22 18:08:50'),
(12, 'Processador Intel Core Neural X9', 'Arquitetura híbrida com dezenas de threads, desenvolvida para virtualização fluida e execução de processos pesados em background sem comprometer o ambiente de desenvolvimento principal.', 3200.00, './assets/imgs/products/hw_6ab2c4e20b2e4.png', 'Processadores', '{\"Núcleos\":\"16\",\"Threads\":\"24\",\"Clock Base (GHz)\":\"3.4\",\"Clock Turbo (GHz)\":\"5.6\",\"Socket\":\"LGA 1700\",\"Consumo (W)\":\"253\",\"Cache (MB)\":\"36\"}', 0.00, '2026-09-22 18:11:46'),
(13, 'Gabinete Void Monolith', 'Design escuro, sóbrio e sem vidro temperado lateral para setups minimalistas. Conta com isolamento acústico nas tampas laterais para um ambiente de trabalho estritamente silencioso.', 680.00, './assets/imgs/products/hw_6ab2c578cc8a2.png', 'Gabinetes', '{\"Formato\":\"ATX\",\"Baias\":\"4\",\"Fans Inclusos\":\"3\",\"Lateral\":\"Metal com isolamento\",\"Peso (kg)\":\"8.5\"}', 0.00, '2026-09-22 18:14:16');

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
(1, 'Admin Supremo', 'admin@voidbyte.com', '$2y$10$ScaQHj1CSqoZW7392TpAlukwVZzKok.nlxmaIv9VzXpL5.F3BoRmC', 'admin', './assets/imgs/avatars/user_1_6ab2c5fd79080.png', '2026-06-08 17:41:31', NULL, NULL),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
