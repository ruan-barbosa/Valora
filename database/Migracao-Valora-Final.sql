-- MIGRAÇÃO PARA BASES VALORA JÁ EXISTENTES
-- Execute uma única vez no banco atual. Não execute se a coluna `resolvido`
-- já existir na tabela `alerta` ou se a tabela `historico_margem` já tiver sido criada.

ALTER TABLE alerta
  ADD COLUMN resolvido BOOLEAN NOT NULL DEFAULT FALSE AFTER visto,
  ADD INDEX idx_alerta_resolvido (resolvido, visto);

ALTER TABLE lancamento_producao
  MODIFY COLUMN custo_unitario DECIMAL(20,4) NOT NULL,
  MODIFY COLUMN custo_total DECIMAL(20,2) NOT NULL;

ALTER TABLE alerta MODIFY COLUMN custo_calculado DECIMAL(20,4) NULL;

ALTER TABLE composicao ADD INDEX idx_composicao_item (id_item);
ALTER TABLE lancamento_producao ADD INDEX idx_lancamento_data (data_lancamento);

CREATE TABLE historico_margem (
  id_historico_margem INT PRIMARY KEY AUTO_INCREMENT,
  id_produto INT NOT NULL,
  custo_total DECIMAL(20,4) NOT NULL,
  margem_calculada DECIMAL(7,2) NOT NULL,
  margem_minima DECIMAL(5,2) NOT NULL,
  data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_historico_margem_produto_data (id_produto, data_registro),
  CONSTRAINT fk_historico_margem_produto
    FOREIGN KEY (id_produto) REFERENCES produto (id_produto)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
