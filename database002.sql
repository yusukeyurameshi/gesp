DROP TABLE IF EXISTS tipos_produtos;

-- Criar tabela de tipos de produtos
CREATE TABLE IF NOT EXISTS tipos_produtos (
    tipo_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir tipos de produtos padrão
INSERT INTO tipos_produtos (nome, descricao) VALUES
('Matéria-Prima', 'Materiais básicos para produção'),
('Material de Escritório', 'Materiais utilizados em escritório'),
('Material de Limpeza', 'Produtos para limpeza e higiene'),
('Material de Construção', 'Materiais utilizados em construção'),
('Material Elétrico', 'Componentes e materiais elétricos'),
('Material de Informática', 'Equipamentos e acessórios de informática'),
('Material de Segurança', 'Equipamentos de segurança'),
('Material de Manutenção', 'Materiais para manutenção'),
('Material de Embalagem', 'Materiais para embalagem'),
('Outros', 'Outros tipos de materiais');

ALTER TABLE produtos ADD (
    tipo_id INT NOT NULL
);

update produtos set tipo_id = 1;

ALTER TABLE produtos ADD (
    FOREIGN KEY (tipo_id) REFERENCES tipos_produtos(tipo_id)
);

ALTER TABLE produtos DROP COLUMN codigo;