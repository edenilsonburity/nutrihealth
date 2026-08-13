-- Migração: permite digitar a profissão livremente no cadastro do paciente,
-- criando automaticamente no cadastro de Profissões quando ainda não existir.
-- Uso: mysql -u root -p nutrihealth < migrations/2026_08_occupation_free_text.sql

USE nutrihealth;

-- A descrição digitada livremente pode ser mais longa que as 25 colunas antigas
ALTER TABLE occupation
  MODIFY COLUMN description_occupation VARCHAR(100) NOT NULL;
