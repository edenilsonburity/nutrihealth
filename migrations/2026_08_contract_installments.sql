-- Migração: parcelas individuais do contrato (acompanhamento de pagamento)
-- Uso: mysql -u root -p nutrihealth < migrations/2026_08_contract_installments.sql

USE nutrihealth;

CREATE TABLE contract_installment (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  contract_id         INT NOT NULL,
  installment_number  SMALLINT UNSIGNED NOT NULL,
  due_date            DATE NOT NULL,
  amount              DECIMAL(10,2) NOT NULL,
  status              ENUM('PENDENTE','PAGO','CANCELADO') NOT NULL DEFAULT 'PENDENTE',
  paid_at             DATETIME NULL,
  paid_amount         DECIMAL(10,2) NULL,
  payment_method      VARCHAR(50) NULL,
  infinitepay_charge_id VARCHAR(100) NULL,
  notes               VARCHAR(255) NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ci_contract FOREIGN KEY (contract_id) REFERENCES contract(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gera as parcelas retroativamente para contratos que já existiam antes desta migração
-- (mesma lógica de cadência mensal usada na tela de impressão: 1 parcela por mês a partir de start_date)
INSERT INTO contract_installment (contract_id, installment_number, due_date, amount, status)
SELECT
  c.id,
  seq.n,
  DATE_ADD(c.start_date, INTERVAL (seq.n - 1) MONTH),
  CASE WHEN seq.n = c.installments
       THEN c.total_value - ROUND(c.total_value / c.installments, 2) * (c.installments - 1)
       ELSE ROUND(c.total_value / c.installments, 2)
  END,
  'PENDENTE'
FROM contract c
JOIN (
  SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
  UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
  UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18
  UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
) seq ON seq.n <= c.installments
WHERE NOT EXISTS (
  SELECT 1 FROM contract_installment ci WHERE ci.contract_id = c.id
);
