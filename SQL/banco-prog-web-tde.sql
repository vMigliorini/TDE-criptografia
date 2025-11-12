-- Apagando e recriando o banco de dados para um ambiente limpo
DROP DATABASE IF EXISTS bikes;
CREATE DATABASE bikes;
USE bikes;

-- Estrutura das Tabelas (ATUALIZADA)
CREATE TABLE endereco (
    id_endereco SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    cep varchar(9) NOT NULL,
    nome_logradouro VARCHAR(255) NOT NULL,
    numero_residencia int NOT NULL,
    tipo_logradouro varchar(255) NOT NULL
);

CREATE TABLE pessoa(
    id_pessoa SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_endereco SMALLINT UNSIGNED,
    FOREIGN KEY (id_endereco) REFERENCES endereco(id_endereco),
    nome varchar(255) NOT NULL,
    data_nascimento timestamp,
    senha_login varchar(255),
    email varchar(255) unique
);

CREATE TABLE contato(
    id_pessoa SMALLINT UNSIGNED,
    FOREIGN KEY (id_pessoa) REFERENCES pessoa(id_pessoa),
    numero_contato varchar(30) NOT NULL,
    PRIMARY KEY(id_pessoa, numero_contato)
);

CREATE TABLE cliente(
    id_cliente SMALLINT UNSIGNED PRIMARY KEY,
    FOREIGN KEY (id_cliente) REFERENCES pessoa(id_pessoa),
    limite_credito DECIMAL(15,2),
    data_cadastro timestamp default current_timestamp
);

CREATE TABLE vendedor(
    id_vendedor SMALLINT UNSIGNED PRIMARY KEY,
    FOREIGN KEY (id_vendedor) REFERENCES pessoa(id_pessoa),
    cargo VARCHAR(50) NOT NULL,
    meta_mensal DECIMAL(15, 2),
    data_admissao DATE,
    salario DECIMAL(10, 2) NOT NULL,
    comissao DECIMAL(4, 2)
);

CREATE TABLE vendedor_cliente(
    id_vendedor SMALLINT UNSIGNED,
    FOREIGN KEY (id_vendedor) REFERENCES vendedor(id_vendedor),
    id_cliente SMALLINT UNSIGNED,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    data_atendimento DATE,
    PRIMARY KEY(id_vendedor, id_cliente)
);

CREATE TABLE estoquista(
    id_estoquista SMALLINT UNSIGNED PRIMARY KEY,
    FOREIGN KEY (id_estoquista) REFERENCES pessoa(id_pessoa),
    cargo VARCHAR(50) NOT NULL,
    salario DECIMAL(10, 2) NOT NULL,
    data_admissao DATE
);

CREATE TABLE produto(
    id_produto SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    categoria ENUM('Mountain Bike', 'Road Bike', 'Electric Bike', 'BMX Bike', 'Folding Bike', 'Cruiser Bike', 'Hybrid Bike', 'Infantil', 'Pecas', 'Acessorios'),
    nome_produto VARCHAR(255),
    preco DECIMAL(10, 2), -- Preço de Venda
    quantidade INT
);

CREATE TABLE estoquista_produto(
    id_produto SMALLINT UNSIGNED,
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto),
    id_estoquista SMALLINT UNSIGNED,
    FOREIGN KEY (id_estoquista) REFERENCES estoquista(id_estoquista),
    PRIMARY KEY(id_produto, id_estoquista)
);

CREATE TABLE pedido(
    id_pedido SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_vendedor SMALLINT UNSIGNED,
    FOREIGN KEY (id_vendedor) REFERENCES vendedor(id_vendedor),
    id_cliente SMALLINT UNSIGNED NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_estoquista SMALLINT UNSIGNED,
    FOREIGN KEY (id_estoquista) REFERENCES estoquista(id_estoquista),
    valor_total DECIMAL(15, 2) NOT NULL
);

CREATE TABLE item_pedido(
    id_produto SMALLINT UNSIGNED,
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto),
    id_pedido SMALLINT UNSIGNED,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido),
    quantidade_pedida SMALLINT,
    preco_unitario DECIMAL(10, 2), -- Preço de Venda Unitário do Item no Pedido
    PRIMARY KEY (id_produto, id_pedido)
);

CREATE TABLE pagamento(
    id_pagamento SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_pedido SMALLINT UNSIGNED,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido),
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_pagamento ENUM('Pendente', 'Aprovado', 'Recusado') NOT NULL DEFAULT 'Pendente'
);

CREATE TABLE admin(
    id_admin SMALLINT UNSIGNED PRIMARY KEY,
    FOREIGN KEY (id_admin) REFERENCES pessoa(id_pessoa),
    cargo VARCHAR(50) NOT NULL,
    data_admissao DATE,
    salario DECIMAL(10, 2) NOT NULL
);

CREATE TABLE historico(
    id_historico SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_admin SMALLINT UNSIGNED,
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin),
    id_pessoa SMALLINT UNSIGNED,
    FOREIGN KEY (id_pessoa) REFERENCES pessoa(id_pessoa)
);

CREATE TABLE venda(
    id_venda SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_pagamento SMALLINT UNSIGNED,
    comissao decimal(4, 2),
    FOREIGN KEY (id_pagamento) REFERENCES pagamento(id_pagamento),
    id_historico SMALLINT UNSIGNED,
    FOREIGN KEY (id_historico) REFERENCES historico(id_historico)
);

CREATE TABLE pedido_de_compra_de_estoque(
    id_pedido_de_compra_de_estoque SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_estoquista SMALLINT UNSIGNED,
    FOREIGN KEY (id_estoquista) REFERENCES estoquista(id_estoquista),
    id_admin SMALLINT UNSIGNED,
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin),
    valor_estimado DECIMAL(10, 2),
    data_pedido DATE,
    status ENUM('Aprovado', 'Cancelado', 'Pendente') -- Trocado para ENUM
);

CREATE TABLE produto_pedido_estoque(
    id_produto SMALLINT UNSIGNED,
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto),
    id_pedido_de_compra_de_estoque SMALLINT UNSIGNED,
    FOREIGN KEY (id_pedido_de_compra_de_estoque) REFERENCES pedido_de_compra_de_estoque(id_pedido_de_compra_de_estoque),
    quantidade_pedida INT,
    preco_unitario DECIMAL(10, 2), -- Adicionado para registrar o custo unitário da compra
    PRIMARY KEY(id_produto, id_pedido_de_compra_de_estoque)
);

CREATE TABLE compra_de_estoque(
    id_compra_de_estoque SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_estoquista SMALLINT UNSIGNED,
    FOREIGN KEY (id_estoquista) REFERENCES estoquista(id_estoquista),
    id_pedido_de_compra_de_estoque SMALLINT UNSIGNED,
    FOREIGN KEY (id_pedido_de_compra_de_estoque) REFERENCES pedido_de_compra_de_estoque(id_pedido_de_compra_de_estoque),
    valor_final_compra DECIMAL(10, 2),
    id_pagamento SMALLINT UNSIGNED,
    -- id_produto foi removido daqui
    FOREIGN KEY (id_pagamento) REFERENCES pagamento(id_pagamento)
);

CREATE TABLE nota_fiscal(
    id_venda SMALLINT UNSIGNED,
    id_compra_de_estoque SMALLINT UNSIGNED,
    chave_acesso CHAR(44) PRIMARY KEY,
    serie VARCHAR(3) NOT NULL,
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_saida TIMESTAMP NULL DEFAULT NULL,
    CFOP VARCHAR(4) NOT NULL,
    valores_de_impostos DECIMAL(10, 2) NOT NULL,
    status ENUM('Emitida', 'Cancelada', 'Denegada') NOT NULL,
    FOREIGN KEY (id_venda) REFERENCES venda(id_venda),
    FOREIGN KEY (id_compra_de_estoque) REFERENCES compra_de_estoque(id_compra_de_estoque),
    CONSTRAINT chk_tipo_nota CHECK ((id_venda IS NOT NULL AND id_compra_de_estoque IS NULL) OR (id_venda IS NULL AND id_compra_de_estoque IS NOT NULL))
);

CREATE TABLE movimentacao_estoque(
    id_movimentacao_estoque SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_venda SMALLINT UNSIGNED,
    FOREIGN KEY (id_venda) REFERENCES venda(id_venda),
    id_historico SMALLINT UNSIGNED,
    FOREIGN KEY (id_historico) REFERENCES historico(id_historico),
    id_compra_de_estoque SMALLINT UNSIGNED,
    FOREIGN KEY (id_compra_de_estoque) REFERENCES compra_de_estoque(id_compra_de_estoque)
);