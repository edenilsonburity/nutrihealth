-- Migração: cadastro de Tipos de Serviço/Pacotes + Contratos de paciente
-- Uso: mysql -u root -p nutrihealth < migrations/2026_08_contracts.sql

USE nutrihealth;

CREATE TABLE service_type (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  code           VARCHAR(50) UNIQUE NOT NULL,
  name           VARCHAR(100) NOT NULL,
  description    VARCHAR(255) NULL,
  default_price  DECIMAL(10,2) NULL,
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
