CREATE DATABASE remedioja;

USE remedioja;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nome VARCHAR(100),
    dosagem VARCHAR(100),
    horario DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
