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
