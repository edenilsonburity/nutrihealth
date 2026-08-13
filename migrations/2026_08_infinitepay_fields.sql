-- Migração: campos adicionais nas parcelas para a integração de cobrança InfinitePay
-- Uso: mysql -u root -p nutrihealth < migrations/2026_08_infinitepay_fields.sql

USE nutrihealth;

ALTER TABLE contract_installment
  ADD COLUMN infinitepay_link            VARCHAR(500) NULL COMMENT 'Link de pagamento gerado' AFTER infinitepay_charge_id,
  ADD COLUMN infinitepay_transaction_nsu VARCHAR(100) NULL COMMENT 'ID da transação após pagamento' AFTER infinitepay_link,
  ADD COLUMN infinitepay_capture_method  VARCHAR(30)  NULL COMMENT 'pix ou credit_card, vindo do webhook' AFTER infinitepay_transaction_nsu;
