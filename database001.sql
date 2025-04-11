-- Criar banco de dados
-- DROP DATABASE IF EXISTS gesp;
-- CREATE DATABASE IF NOT EXISTS gesp;
-- USE gesp;

-- Criar tabela de unidades
DROP TABLE IF EXISTS movimentacoes;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS unidades;
DROP TABLE IF EXISTS localizacoes;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE IF NOT EXISTS unidades (
    unidade_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    sigla VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criar tabela de localizações
CREATE TABLE IF NOT EXISTS localizacoes (
    localizacao_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criar tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    produto_id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantidade_minima DECIMAL(10,2) NOT NULL DEFAULT 0,
    unidade_id INT NOT NULL,
    localizacao_id INT NOT NULL,
    tipo_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unidade_id) REFERENCES unidades(unidade_id),
    FOREIGN KEY (localizacao_id) REFERENCES localizacoes(localizacao_id),
    FOREIGN KEY (tipo_id) REFERENCES tipos_produtos(tipo_id)
);

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('Administrador', 'Colaborador', 'Leitor') NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criar tabela de movimentações
CREATE TABLE IF NOT EXISTS movimentacoes (
    movimentacao_id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    data DATETIME NOT NULL,
    observacao TEXT NOT NULL,
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(produto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id)
);

-- Inserir unidades padrão
INSERT INTO unidades (nome, sigla) VALUES
('Unidade', 'un'),
('Quilograma', 'kg'),
('Metro', 'm'),
('Litro', 'l'),
('Metro Quadrado', 'm²'),
('Metro Cúbico', 'm³'),
('Caixa', 'cx'),
('Pacote', 'pct'),
('Rolo', 'rl'),
('Fardo', 'fd');

-- Inserir usuário padrão (senha: admin123)
INSERT INTO usuarios (nome, username, senha, perfil, ativo) VALUES
('Administrador', 'admin', '$2y$10$Rv9ttEAArv7AYsuhTvJ6.uDO5Fx2iLynF0GPZ1FOAlXPPqbghKgMe', 'Administrador', FALSE),
('xxxxxx', 'xxxxx', '$2y$10$Rv9ttEAArv7AYsuhTvJ6.uDO5Fx2iLynF0GPZ1FOAlXPPqbghKgMe', 'Administrador', TRUE); 

-- Inserir produtos de exemplo
INSERT INTO produtos (codigo, nome, quantidade, quantidade_minima, unidade_id, localizacao_id, tipo_id) VALUES
('P001', 'Papel A4', 500.00, 100.00, 1, 1, 2),
('P002', 'Tinta Azul', 25.50, 5.00, 2, 2, 1),
('P003', 'Cabo de Rede', 100.00, 20.00, 3, 3, 6),
('P004', 'Água Sanitária', 15.00, 3.00, 4, 4, 3),
('P005', 'Piso Cerâmico', 50.00, 10.00, 5, 5, 4),
('P006', 'Cimento', 1000.00, 200.00, 6, 6, 4),
('P007', 'Lápis', 200.00, 40.00, 1, 7, 2),
('P008', 'Papel Higiênico', 30.00, 6.00, 7, 8, 3),
('P009', 'Papel Toalha', 20.00, 4.00, 8, 9, 3),
('P010', 'Fita Adesiva', 15.00, 3.00, 9, 10, 2);

-- Inserir movimentações de exemplo
INSERT INTO movimentacoes (produto_id, quantidade, tipo, data, observacao, usuario_id) VALUES
(1, 100.00, 'entrada', '2025-03-04 10:00:00', 'Compra de material', 1),
(2, 5.00, 'saida', '2025-03-04 14:30:00', 'Uso em projeto', 1),
(3, 20.00, 'entrada', '2025-03-05 09:15:00', 'Reposição de estoque', 1),
(4, 3.00, 'saida', '2025-03-05 16:45:00', 'Limpeza', 1),
(5, 10.00, 'entrada', '2025-03-06 11:20:00', 'Compra de material', 1);