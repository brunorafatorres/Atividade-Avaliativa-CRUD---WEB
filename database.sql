-- ============================================
-- BANCO DE DADOS: crud_exercicios
-- PROFESSOR: Me. Juan Carlos Quevedo Weimar
-- DISCIPLINA: Programação Web II
-- ============================================

-- Criar banco de dados (opcional)
CREATE DATABASE IF NOT EXISTS crud_exercicios;
USE crud_exercicios;

-- ========== EXERCÍCIO 1: Cardápio Digital ==========
CREATE TABLE pratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50) NOT NULL
);

-- ========== EXERCÍCIO 2: Playlist de Músicas ==========
CREATE TABLE musicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    artista VARCHAR(100) NOT NULL,
    duracao INT NOT NULL
);

-- ========== EXERCÍCIO 3: Lista de Filmes ==========
CREATE TABLE filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    genero VARCHAR(50),
    status ENUM('assistido', 'nao_assistido') DEFAULT 'nao_assistido'
);

-- ========== EXERCÍCIO 4: Controle de Despesas ==========
CREATE TABLE despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('fixa', 'variavel') NOT NULL,
    data DATE NOT NULL
);

-- ========== EXERCÍCIO 5: Registro de Treinos ==========
CREATE TABLE treinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercicio VARCHAR(100) NOT NULL,
    series INT NOT NULL,
    repeticoes INT NOT NULL,
    carga DECIMAL(8,2) NOT NULL
);

-- ========== EXERCÍCIO 6: Controle de Entregas ==========
CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(100) NOT NULL,
    endereco TEXT NOT NULL,
    status ENUM('pendente', 'entregue') DEFAULT 'pendente'
);

-- ========== EXERCÍCIO 7: Feedback Anônimo ==========
CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL,
    nota INT CHECK (nota BETWEEN 1 AND 5)
);

-- ========== EXERCÍCIO 8: Cadastro de Cursos ==========
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    carga_horaria INT NOT NULL,
    nivel ENUM('basico', 'intermediario', 'avancado') NOT NULL
);

-- ========== EXERCÍCIO 9: Cadastro de Pets ==========
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    idade INT NOT NULL,
    tutor VARCHAR(100) NOT NULL
);

-- ========== EXERCÍCIO 10: Sistema de Enquetes ==========
CREATE TABLE enquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta VARCHAR(255) NOT NULL
);

CREATE TABLE opcoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enquete_id INT,
    texto VARCHAR(100) NOT NULL,
    votos INT DEFAULT 0,
    FOREIGN KEY (enquete_id) REFERENCES enquetes(id) ON DELETE CASCADE
);

-- ========== EXERCÍCIO 11: Registro de Leitura ==========
CREATE TABLE leituras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livro VARCHAR(100) NOT NULL,
    paginas_totais INT NOT NULL,
    paginas_lidas INT DEFAULT 0
);

-- ========== EXERCÍCIO 12: Banco de Ideias ==========
CREATE TABLE ideias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    dificuldade ENUM('baixa', 'media', 'alta') NOT NULL
);

-- ========== EXERCÍCIO 13: Sistema de Metas ==========
CREATE TABLE metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meta VARCHAR(200) NOT NULL,
    prazo DATE NOT NULL,
    status ENUM('pendente', 'concluida') DEFAULT 'pendente',
    progresso INT DEFAULT 0
);

-- ========== EXERCÍCIO 14: Lista de Compras ==========
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto VARCHAR(100) NOT NULL,
    quantidade INT NOT NULL,
    comprado BOOLEAN DEFAULT 0
);

-- ========== EXERCÍCIO 15: Ranking de Pontuação ==========
CREATE TABLE rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    pontuacao INT NOT NULL
);

-- ============================================
-- FIM DOS SCRIPTS SQL
-- ============================================