-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07-Ago-2025 às 01:17
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agenda_medicamentos`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `medicamento_id` int(11) NOT NULL,
  `dosagem` varchar(100) DEFAULT NULL,
  `data_agendada` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lembretes`
--

CREATE TABLE `lembretes` (
  `id` int(11) NOT NULL,
  `medicamento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime NOT NULL,
  `tomado` tinyint(1) DEFAULT 0,
  `data_tomada` timestamp NULL DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `medicamentos`
--

CREATE TABLE `medicamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `dosagem` varchar(100) DEFAULT NULL,
  `frequencia` varchar(100) DEFAULT NULL,
  `horarios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`horarios`)),
  `observacoes` text DEFAULT NULL,
  `link_compra` varchar(500) DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `DESCRICAO` varchar(500) DEFAULT NULL,
  `PRECO` float DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `medicamentos`
--

INSERT INTO `medicamentos` (`id`, `usuario_id`, `nome`, `dosagem`, `frequencia`, `horarios`, `observacoes`, `link_compra`, `data_inicio`, `data_fim`, `ativo`, `data_criacao`, `data_atualizacao`, `DESCRICAO`, `PRECO`, `imagem`) VALUES
(5, NULL, 'DIPIRONA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-08-06 22:46:46', '2025-08-06 22:46:46', 'Descrição não disponível.', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `senha_hash`, `nome`, `data_criacao`, `data_atualizacao`, `ativo`) VALUES
(1, 'joaomiguel.derocha@gmail.com', '$2y$10$SWKOu6e/eApqprtkRul6TepQyTrlMuAr1GyXPlMgZa6PRfZ.B1ll6', NULL, '2025-08-04 23:20:05', '2025-08-04 23:20:05', 1),
(2, 'joao@gmail.com', '$2y$10$XvLKXc5yjaQAlQFXi9EU/e8g5E7HF6slwMySe3r4jpyOnjen1N6x6', NULL, '2025-08-06 22:35:03', '2025-08-06 22:35:03', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `medicamento_id` (`medicamento_id`);

--
-- Índices para tabela `lembretes`
--
ALTER TABLE `lembretes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_medicamento_id` (`medicamento_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_data_hora` (`data_hora`),
  ADD KEY `idx_tomado` (`tomado`),
  ADD KEY `idx_data_tomada` (`data_tomada`);

--
-- Índices para tabela `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_data_criacao` (`data_criacao`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `lembretes`
--
ALTER TABLE `lembretes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `medicamentos`
--
ALTER TABLE `medicamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`medicamento_id`) REFERENCES `medicamentos` (`id`);

--
-- Limitadores para a tabela `lembretes`
--
ALTER TABLE `lembretes`
  ADD CONSTRAINT `lembretes_ibfk_1` FOREIGN KEY (`medicamento_id`) REFERENCES `medicamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lembretes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
