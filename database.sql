CREATE DATABASE IF NOT EXISTS nutrihealth;
USE nutrihealth;

CREATE TABLE `user` (
  id int not null AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  typeUser char(1) not null,
  primary key (id)
);

CREATE TABLE `occupation` (
  id int not null AUTO_INCREMENT,
  code CHAR(7) UNIQUE NOT NULL,
  description_occupation VARCHAR(25)  NOT NULL,
  primary key (id) 
);

CREATE TABLE patient (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  name_patient      VARCHAR(150) NOT NULL,
  cpf               CHAR(11) NOT NULL UNIQUE,
  birth_date        DATE NULL,
  phone             VARCHAR(20) NULL,
  cellphone         VARCHAR(20) NOT NULL,
  email             VARCHAR(100) NULL,
  address           VARCHAR(255) NULL,
  emergency_contact VARCHAR(150) NULL,
  guardian_name     VARCHAR(150) NULL,
  status            ENUM('A','I') NOT NULL DEFAULT 'A', -- A = Ativo, I = Inativo
  notes             TEXT NULL
);
