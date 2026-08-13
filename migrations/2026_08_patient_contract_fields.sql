-- Migração: campos do paciente necessários para o contrato de prestação de serviços
-- (RG, nacionalidade, estado civil, CEP), conforme o modelo de contrato da clínica.
-- Uso: mysql -u root -p nutrihealth < migrations/2026_08_patient_contract_fields.sql

USE nutrihealth;

ALTER TABLE patient
  ADD COLUMN rg             VARCHAR(20) NULL COMMENT 'Documento de identidade' AFTER notes,
  ADD COLUMN nationality    VARCHAR(50) NULL COMMENT 'Ex.: Brasileiro(a)' AFTER rg,
  ADD COLUMN marital_status VARCHAR(30) NULL COMMENT 'Ex.: Solteiro(a), Casado(a)' AFTER nationality,
  ADD COLUMN cep            VARCHAR(9)  NULL COMMENT 'CEP separado do endereço' AFTER marital_status;
