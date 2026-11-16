-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/11/2026 às 20:46
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
-- Estrutura para tabela `cupons`
--

CREATE TABLE `cupons` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `tipo` enum('percentual','fixo') NOT NULL DEFAULT 'percentual',
  `valor` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cupons`
--

INSERT INTO `cupons` (`id`, `codigo`, `tipo`, `valor`, `ativo`, `data_criacao`) VALUES
(1, 'VOIDBYTE10', 'percentual', 10.00, 1, '2026-11-16 19:34:44');

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
  `desconto_cupom` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cupom_usado` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'confirmado',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `nome_destinatario`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cep`, `forma_pagamento`, `subtotal`, `frete`, `desconto_cupom`, `cupom_usado`, `total`, `status`, `data_criacao`) VALUES
(1, 3, 'Amanda', 'Rua Pão de Queijo', '67', '', 'Goiabada', 'Sobremesa', 'SP', '18341-973', 'pix', 250.00, 29.90, 0.00, NULL, 279.90, 'confirmado', '2026-09-22 00:56:53');

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
(13, 'Gabinete Void Monolith', 'Design escuro, sóbrio e sem vidro temperado lateral para setups minimalistas. Conta com isolamento acústico nas tampas laterais para um ambiente de trabalho estritamente silencioso.', 680.00, './assets/imgs/products/hw_6ab2c578cc8a2.png', 'Gabinetes', '{\"Formato\":\"ATX\",\"Baias\":\"4\",\"Fans Inclusos\":\"3\",\"Lateral\":\"Metal com isolamento\",\"Peso (kg)\":\"8.5\"}', 0.00, '2026-09-22 18:14:16'),
(14, 'Processador Neural Ryzen 9 Quantum', 'Arquitetura de última geração com 16 núcleos e alta velocidade para renderização pesada, compilação de códigos complexos e inteligência artificial local.', 3499.90, './assets/imgs/products/hw_6afb42501f316.jpg', 'Processadores', '{\"Núcleos\":\"16\",\"Threads\":\"32\",\"Clock Base (GHz)\":\"3.8\",\"Clock Turbo (GHz)\":\"5.4\",\"Socket\":\"LGA 1851\",\"Consumo (W)\":\"170\",\"Cache (MB)\":\"64\"}', 5.00, '2026-11-16 17:50:08'),
(15, 'Placa de Vídeo Void RTX 5080 Shadow', 'Desempenho gráfico extremo com ray tracing avançado e resfriamento silencioso a líquido integrado.', 5899.90, './assets/imgs/products/hw_6afb42f4686a5.jpg', 'Placas de Vídeo', '{\"VRAM (GB)\":\"16\",\"Tipo de Memória\":\"GDDR7\",\"Clock (MHz)\":\"2625\",\"Consumo (W)\":\"350\",\"Conexões\":\"HDMI 2.1, 3x DisplayPort 2.1\",\"Ray Tracing\":\"Sim (4ª Geração)\"}', 0.00, '2026-11-16 17:52:52'),
(16, 'Placa-Mãe Z790 CyberMatrix Wi-Fi', 'Suporte a memórias DDR5, dissipadores em alumínio escovado e portas de rede de alta velocidade para estabilidade máxima.', 1850.00, './assets/imgs/products/hw_6afb44110d4ba.png', 'Placas-Mãe', '{\"Socket\":\"LGA 1851\",\"Chipset\":\"Intel Z790\",\"Formato\":\"ATX\",\"Slots RAM\":\"4\",\"Tipo de RAM\":\"DDR5\",\"Slots M.2\":\"4\"}', 10.00, '2026-11-16 17:57:37'),
(17, 'Memória RAM VoidCore RGB 32GB (2x16) 6000MHz', 'Kits de alto desempenho com baixa latência (CL30) e iluminação personalizável por perfil de software.', 899.90, './assets/imgs/products/hw_6afb44dee4fee.png', 'Memórias RAM', '{\"Capacidade (GB)\":\"32\",\"Tipo\":\"DDR5\",\"Frequência (MHz)\":\"6000\",\"Latência (CL)\":\"30\",\"Módulos\":\"2x16GB\"}', 15.00, '2026-11-16 18:01:02'),
(18, 'SSD M.2 NVMe VoidDrive 4TB Gen5', 'Velocidades absurdas de leitura e escrita para carregar sistemas operacionais, containers Docker e grandes bases de dados instantaneamente.', 1699.90, './assets/imgs/products/hw_6afb4580778a3.png', 'Armazenamento (SSD/NVMe)', '{\"Capacidade (GB)\":\"4000\",\"Tipo\":\"NVMe M.2\",\"Interface\":\"PCIe 5.0\",\"Leitura (MB\\/s)\":\"14000\",\"Escrita (MB\\/s)\":\"12000\"}', 0.00, '2026-11-16 18:03:44'),
(19, 'Fonte Modular VoidPower 1000W 80 Plus Platinum', 'Eficiência energética máxima com cabos totalmente modulares e proteção contra oscilações de energia.', 1199.90, './assets/imgs/products/hw_6afb52b4a7e00.png', 'Fontes de Alimentação', '{\"Potência (W)\":\"1000\",\"Certificação\":\"80 Plus Platinum\",\"Modular\":\"Sim (Totalmente Modular)\",\"Formato\":\"ATX\"}', 8.00, '2026-11-16 19:00:04'),
(20, 'Water Cooler AIO VoidLiquid 360mm LCD', 'Refrigeração líquida com tela personalizável no bloco para monitoramento de temperatura da CPU em tempo real.', 980.00, './assets/imgs/products/hw_6afb5350bac88.png', 'Refrigeração e Coolers', '{\"Tipo\":\"Water Cooler AIO\",\"Sockets Suportados\":\"LGA 1851, AM5, LGA 1700\",\"Ruído (dB)\":\"28\",\"Tamanho do Fan (mm)\":\"3x 120mm\"}', 0.00, '2026-11-16 19:02:40'),
(21, 'Gabinete Aquário Panoramic Void Glass', 'Estrutura em vidro temperado sem colunas frontais, excelente fluxo de ar e espaço de sobra para cable management.', 750.00, './assets/imgs/products/hw_6afb54052b1c6.png', 'Gabinetes', '{\"Formato\":\"ATX \\/ E-ATX\",\"Baias\":\"2x 3.5\\\" \\/ 2x 2.5\\\"\",\"Fans Inclusos\":\"4x ARGB\",\"Lateral\":\"Vidro Temperado Panorâmico\",\"Peso (kg)\":\"11.2\"}', 12.00, '2026-11-16 19:05:41'),
(22, 'Teclado Mecânico Custom Void 65% Wireless', 'Switches hot-swappable lineares, keycaps em PBT de alta durabilidade e conectividade tri-mode (Bluetooth, 2.4GHz e Cabo).', 649.90, './assets/imgs/products/hw_6afb549eb7c74.jpg', 'Teclados Mecânicos', '{\"Switch\":\"Linear Custom Red (Hot-swappable)\",\"Layout\":\"ANSI 65%\",\"Conexão\":\"Wireless 2.4GHz \\/ Bluetooth \\/ USB-C\",\"Iluminação\":\"RGB por tecla\",\"Material\":\"Alumínio e PBT\",\"Taxa de Resposta (ms)\":\"1\"}', 10.00, '2026-11-16 19:08:14'),
(23, 'Mouse Ergonômico CyberPrecision 26K', 'Sensor óptico de alta precisão, design ultraleve (60g) e switches mecânicos de clique ultrarrápido.', 399.90, './assets/imgs/products/hw_6afb5537cfeed.png', 'Mouses Cyber', '{\"DPI\":\"26000\",\"Sensor\":\"Óptico Avançado\",\"Botões\":\"6 programáveis\",\"Peso (g)\":\"60\",\"Conexão\":\"Sem Fio \\/ Cabo USB-C\",\"Taxa de Resposta (ms)\":\"1\"}', 0.00, '2026-11-16 19:10:47'),
(24, 'Monitor Ultrawide Curvo 38\" IPS 160Hz', 'Tela ultrawide imersiva para multitarefa avançada, permitindo abrir múltiplas janelas de IDE e terminais lado a lado.', 3299.90, './assets/imgs/products/hw_6afb55bd5eefb.png', 'Monitores', '{\"Tamanho (pol)\":\"38\",\"Resolução\":\"3840x1600\",\"Painel\":\"IPS Curvo\",\"Taxa de Atualização (Hz)\":\"160\",\"Tempo de Resposta (ms)\":\"1\",\"Conexões\":\"2x HDMI 2.1, 1x DisplayPort 1.4, USB-C\"}', 5.00, '2026-11-16 19:13:01'),
(25, 'Headset Wireless Stealth Focus ANC', 'Cancelamento ativo de ruído (ANC), drivers de 50mm e microfone com isolamento de voz por inteligência artificial.', 599.90, './assets/imgs/products/hw_6afb56452eecf.png', 'Headsets e Áudio', '{\"Driver (mm)\":\"50\",\"Conexão\":\"Wireless 2.4GHz \\/ Bluetooth\",\"Microfone\":\"Retrátil com IA ANC\",\"Surround\":\"Virtual 7.1\",\"Peso (g)\":\"290\"}', 0.00, '2026-11-16 19:15:17'),
(26, 'Cadeira Ergonômica Mesh Nocturne Pro', 'Revestimento total em malha mesh respirável, suporte lombar dinâmico e ajustes 4D para máximo conforto em longas jornadas.', 1899.90, './assets/imgs/products/hw_6afb56b26fcd0.png', 'Cadeiras Ergonômicas', '{\"Peso Suportado (kg)\":\"150\",\"Material\":\"Malha Mesh Premium\",\"Reclinação\":\"145°\",\"Apoio Lombar\":\"Dinâmico 4D\"}', 20.00, '2026-11-16 19:17:06'),
(27, 'Mesa Elevatória Motorizada Standing Desk Tech', 'Ajuste elétrico de altura com display de memória e sistema antichoque integrado para alternar entre trabalhar sentado ou em pé.', 2499.00, './assets/imgs/products/hw_6afb57796b04d.png', 'Mesas Tech', '{\"Tipo\":\"Altura Ajustável Elétrica (Dual Motor)\",\"Altura Mín\\/Máx (cm)\":\"70 - 120\",\"Memória de Posição\":\"4 perfis\",\"Sistema de Segurança\":\"Antichoque sensível a toque\"}', 0.00, '2026-11-16 19:20:25'),
(28, 'Dock Station USB-C 12-em-1 Pro', 'Dock Station USB-C 12-em-1 Pro', 450.00, './assets/imgs/products/hw_6afb580e3be0b.png', 'Hubs e Conectividade', '{\"Portas USB\":\"4x USB 3.2 \\/ 2x USB-C\",\"Saídas de Vídeo\":\"2x HDMI 4K, 1x DisplayPort\",\"Rede\":\"Ethernet Gigabit (RJ45)\",\"Leitor de Cartões\":\"SD e MicroSD\"}', 10.00, '2026-11-16 19:22:54');

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
(1, 'Admin Supremo', 'admin@voidbyte.com', '$2y$10$g.xTTbS6ph1ovPYeN7F83OzaJJ11N9W0cILAPDovHqpa0AmKQe.M.', 'admin', './assets/imgs/avatars/user_1_6ab2c5fd79080.png', '2026-06-08 17:41:31', NULL, NULL),
(2, 'Ian Gabriel', 'ianbielbia223@gmail.com', '$2y$10$htvoCKHwF3wn0IoxTo5ZfuJ.Wj6ki1UAvJtT1v5.AEJPR3KyiZ/Fy', 'cliente', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-06-08 18:27:03', NULL, NULL),
(3, 'Amanda', 'amanda123@gmail.com', '$2y$10$3DaL1hsHo/MJh9ta1NZ0M.CsSMwzddgjjMZ50WDlx5TPRlX7SXysW', 'cliente', 'https://cdn-icons-png.flaticon.com/512/149/149071.png', '2026-09-21 19:36:15', NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cupons`
--
ALTER TABLE `cupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

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
-- AUTO_INCREMENT de tabela `cupons`
--
ALTER TABLE `cupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
