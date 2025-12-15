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
  notes             TEXT NULL,
  idOccupation      int NOT NULL,
  KEY `fk_occupation` (`idOccupation`),
  CONSTRAINT `fk_occupation` FOREIGN KEY (`idOccupation`) REFERENCES `occupation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE appointment (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  patient_id       INT NOT NULL,
  nutritionist_id  INT NOT NULL,
  start_datetime   DATETIME NOT NULL,
  end_datetime     DATETIME NULL,
  type             ENUM(
                      'PRIMEIRA_CONSULTA',
                      'RETORNO',
                      'AVALIACAO_CORPORAL',
                      'ORIENTACAO_NUTRICIONAL'
                    ) NOT NULL,
  status           ENUM(
                      'PENDENTE',
                      'CONFIRMADO',
                      'CONCLUIDO',
                      'CANCELADO'
                    ) NOT NULL DEFAULT 'PENDENTE',
  notes            TEXT NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_appointment_patient
    FOREIGN KEY (patient_id) REFERENCES patient(id),
  CONSTRAINT fk_appointment_nutritionist
    FOREIGN KEY (nutritionist_id) REFERENCES `user`(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE consultation (
  id                    INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id        INT NOT NULL,
  consultation_date     DATETIME NOT NULL,
  weight_kg             DECIMAL(5,2) NULL,
  height_m              DECIMAL(3,2) NULL,
  bmi                   DECIMAL(4,1) NULL,
  activity_level        ENUM(
                           'SEDENTARIO',
                           'LEVE',
                           'MODERADO',
                           'INTENSO',
                           'MUITO_INTENSO'
                         ) NULL,
  goal                  TEXT NULL,  -- Objetivo da consulta
  dietary_restrictions  TEXT NULL,  -- Restrições alimentares / intolerâncias
  diseases              TEXT NULL,  -- Doenças pré-existentes
  medications           TEXT NULL,  -- Medicamentos em uso
  notes                 TEXT NULL,  -- Observações adicionais
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_consultation_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointment(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE consultation_body_measurements (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  consultation_id   INT NOT NULL,
  -- Dobras cutâneas (mm)
  triceps_mm        DECIMAL(5,2) NULL,
  subscapular_mm    DECIMAL(5,2) NULL,
  suprailiac_mm     DECIMAL(5,2) NULL,
  abdominal_mm      DECIMAL(5,2) NULL,
  thigh_mm          DECIMAL(5,2) NULL,
  calf_mm           DECIMAL(5,2) NULL,

  -- Circunferências (cm)
  waist_circ_cm     DECIMAL(5,2) NULL,
  hip_circ_cm       DECIMAL(5,2) NULL,
  arm_circ_cm       DECIMAL(5,2) NULL,
  thigh_circ_cm     DECIMAL(5,2) NULL,
  calf_circ_cm      DECIMAL(5,2) NULL,

  body_fat_percent  DECIMAL(5,2) NULL, -- % gordura calculado ou estimado
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_cbm_consultation
    FOREIGN KEY (consultation_id) REFERENCES consultation(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
