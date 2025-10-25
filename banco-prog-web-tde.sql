DROP database if exists cadastro;
create database cadastro;
use cadastro;
CREATE TABLE endereco (
    id_endereco SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    cep varchar(255) NOT NULL,
    nome_logradouro varchar(255) NOT NULL,
    numero_residencia VARCHAR(255) NOT NULL,
    tipo_logradouro varchar(255) NOT NULL
);

CREATE TABLE pessoa(
    id_pessoa SMALLINT UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    id_endereco SMALLINT UNSIGNED,
    FOREIGN KEY (id_endereco) REFERENCES endereco(id_endereco),
    nome varchar(255) NOT NULL,
    data_nascimento varchar(255) NOT NULL,
    senha_login varchar(255),
    email varchar(255) unique
);

CREATE TABLE contato(
    id_pessoa SMALLINT UNSIGNED,
    FOREIGN KEY (id_pessoa) REFERENCES pessoa(id_pessoa),
    numero_contato varchar(255) NOT NULL,
    PRIMARY KEY(id_pessoa, numero_contato)
);