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
  rg                VARCHAR(20) NULL,  -- documento de identidade, usado nos contratos
  nationality       VARCHAR(50) NULL,  -- ex.: Brasileiro(a)
  marital_status    VARCHAR(30) NULL,  -- ex.: Solteiro(a), Casado(a)
  cep               VARCHAR(9)  NULL,  -- CEP separado do endereço, usado nos contratos
  idOccupation      int NOT NULL,
  KEY `fk_occupation` (`idOccupation`),
  CONSTRAINT `fk_occupation` FOREIGN KEY (`idOccupation`) REFERENCES `occupation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE appointment_type (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(50) UNIQUE NOT NULL, -- identificador estável usado internamente (ex.: PRIMEIRA_CONSULTA)
  name        VARCHAR(100) NOT NULL,       -- rótulo exibido na tela (editável pelo usuário)
  active      TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO appointment_type (code, name) VALUES
  ('PRIMEIRA_CONSULTA',      'Primeira consulta'),
  ('RETORNO',                'Retorno'),
  ('AVALIACAO_CORPORAL',     'Avaliação corporal'),
  ('ORIENTACAO_NUTRICIONAL', 'Orientação nutricional');

CREATE TABLE appointment (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  patient_id       INT NOT NULL,
  nutritionist_id  INT NOT NULL,
  start_datetime   DATETIME NOT NULL,
  end_datetime     DATETIME NULL,
  type             VARCHAR(50) NOT NULL, -- referencia appointment_type.code (antes era ENUM fixo)
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
    FOREIGN KEY (nutritionist_id) REFERENCES `user`(id),
  CONSTRAINT fk_appointment_type
    FOREIGN KEY (type) REFERENCES appointment_type(code) ON UPDATE CASCADE
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
  goal                  TEXT NULL,  -- Objetivo da consulta (Queixa relatada pelo paciente)
  meta                  TEXT NULL,  -- Meta definida com o paciente (preenchida na 1ª consulta)
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

  body_fat_percent  DECIMAL(5,2) NULL, -- % gordura (calculado ou obtido por bioimpedância)
  lean_mass_percent DECIMAL(5,2) NULL, -- % massa magra
  metabolic_age     SMALLINT UNSIGNED NULL, -- idade metabólica (anos), obtida por bioimpedância
  visceral_fat_level SMALLINT UNSIGNED NULL, -- nível de gordura visceral (escala do aparelho de bioimpedância)
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_cbm_consultation
    FOREIGN KEY (consultation_id) REFERENCES consultation(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Contratos: cadastro de Tipos de Serviço/Pacotes + Contratos
-- =========================================================

CREATE TABLE service_type (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  code           VARCHAR(50) UNIQUE NOT NULL, -- identificador interno estável
  name           VARCHAR(100) NOT NULL,       -- ex.: "Acompanhamento Mensal", "Avaliação Única"
  description    VARCHAR(255) NULL,
  default_price  DECIMAL(10,2) NULL,          -- valor sugerido (o contrato pode usar outro valor)
  active         TINYINT(1) NOT NULL DEFAULT 1,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contract (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  patient_id         INT NOT NULL,
  service_type_id    INT NOT NULL,
  total_value        DECIMAL(10,2) NOT NULL,
  installments        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  payment_condition   ENUM('PIX','CARTAO','BOLETO') NOT NULL,
  start_date          DATE NOT NULL,
  status              ENUM('ATIVO','CONCLUIDO','CANCELADO') NOT NULL DEFAULT 'ATIVO',
  notes               TEXT NULL,
  -- Reservado para a próxima etapa (integração de cobrança via InfinitePay - Pix/Cartão)
  infinitepay_link    VARCHAR(500) NULL,
  infinitepay_order_nsu VARCHAR(100) NULL,
  infinitepay_status  VARCHAR(50) NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_contract_patient
    FOREIGN KEY (patient_id) REFERENCES patient(id),
  CONSTRAINT fk_contract_service_type
    FOREIGN KEY (service_type_id) REFERENCES service_type(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Parcelas do contrato: acompanhamento individual de pagamento
-- =========================================================
CREATE TABLE contract_installment (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  contract_id         INT NOT NULL,
  installment_number  SMALLINT UNSIGNED NOT NULL,
  due_date            DATE NOT NULL,
  amount              DECIMAL(10,2) NOT NULL,
  status              ENUM('PENDENTE','PAGO','CANCELADO') NOT NULL DEFAULT 'PENDENTE',
  paid_at             DATETIME NULL,
  paid_amount         DECIMAL(10,2) NULL,
  payment_method      VARCHAR(50) NULL, -- Pix, Cartão, Boleto, Dinheiro etc. (pode diferir do previsto)
  -- Reservado para a integração InfinitePay (baixa automática via webhook)
  infinitepay_charge_id VARCHAR(100) NULL, -- order_nsu que enviamos pra InfinitePay
  infinitepay_link            VARCHAR(500) NULL, -- link de pagamento gerado
  infinitepay_transaction_nsu VARCHAR(100) NULL, -- ID da transação, preenchido após pagamento
  infinitepay_capture_method  VARCHAR(30)  NULL, -- pix ou credit_card, vindo do webhook
  notes               VARCHAR(255) NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ci_contract FOREIGN KEY (contract_id) REFERENCES contract(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
