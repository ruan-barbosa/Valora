create table empresa (
  id_empresa int primary key auto_increment,
  nome varchar(100) not null,
  segmento varchar(20) not null,
  margem_minima_padrao decimal(5,2) not null default 0,
  percentual_custos_indiretos decimal(5,2) not null default 0
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table unidade_medida (
  id_unidade int primary key auto_increment,
  sigla varchar(10) not null unique,
  grupo varchar(20) not null,
  fator_conversao decimal(14,6) not null default 1
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table usuario (
  id_usuario int primary key auto_increment,
  id_empresa int not null,
  nome varchar(100) not null,
  login varchar(50) not null unique,
  senha_hash varchar(255) not null,
  perfil varchar(20) not null,
  ativo boolean not null default true,
  foreign key (id_empresa) references empresa (id_empresa)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table fornecedor (
  id_fornecedor int primary key auto_increment,
  id_empresa int not null,
  nome varchar(100) not null,
  contato varchar(100),
  ativo boolean not null default true,
  unique (id_empresa, nome),
  foreign key (id_empresa) references empresa (id_empresa)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table item (
  id_item int primary key auto_increment,
  id_empresa int not null,
  id_unidade int not null,
  nome varchar(100) not null,
  tipo varchar(20) not null,
  preco_atual decimal(12,4) not null default 0,
  ativo boolean not null default true,
  unique (id_empresa, nome),
  foreign key (id_empresa) references empresa (id_empresa),
  foreign key (id_unidade) references unidade_medida (id_unidade)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table produto (
  id_produto int primary key auto_increment,
  id_empresa int not null,
  nome varchar(100) not null,
  tipo varchar(20) not null default 'produto',
  preco_venda decimal(12,2) not null,
  margem_minima decimal(5,2),
  ativo boolean not null default true,
  unique (id_empresa, nome),
  foreign key (id_empresa) references empresa (id_empresa)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table historico_preco (
  id_historico int primary key auto_increment,
  id_item int not null,
  id_fornecedor int,
  id_usuario int not null,
  preco decimal(12,4) not null,
  data_alteracao datetime not null default current_timestamp,
  index idx_historico_item_data (id_item, data_alteracao),
  foreign key (id_item) references item (id_item),
  foreign key (id_fornecedor) references fornecedor (id_fornecedor),
  foreign key (id_usuario) references usuario (id_usuario)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table composicao (
  id_composicao int primary key auto_increment,
  id_produto int not null,
  id_item int not null,
  id_unidade int not null,
  quantidade decimal(12,6) not null,
  percentual_perda decimal(5,2) not null default 0,
  unique (id_produto, id_item),
  index idx_composicao_item (id_item),
  foreign key (id_produto) references produto (id_produto),
  foreign key (id_item) references item (id_item),
  foreign key (id_unidade) references unidade_medida (id_unidade)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table lancamento_producao (
  id_lancamento int primary key auto_increment,
  id_produto int not null,
  id_usuario int not null,
  quantidade decimal(12,3) not null,
  data_lancamento datetime not null default current_timestamp,
  custo_unitario decimal(20,4) not null,
  custo_total decimal(20,2) not null,
  preco_venda decimal(12,2) not null,
  index idx_lancamento_data (data_lancamento),
  foreign key (id_produto) references produto (id_produto),
  foreign key (id_usuario) references usuario (id_usuario)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table alerta (
  id_alerta int primary key auto_increment,
  id_produto int not null,
  id_item int,
  margem_calculada decimal(7,2),
  custo_calculado decimal(20,4),
  data_alerta datetime not null default current_timestamp,
  visto boolean not null default false,
  resolvido boolean not null default false,
  index idx_alerta_produto_visto (id_produto, visto),
  index idx_alerta_resolvido (resolvido, visto),
  foreign key (id_produto) references produto (id_produto),
  foreign key (id_item) references item (id_item)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

create table historico_margem (
  id_historico_margem int primary key auto_increment,
  id_produto int not null,
  custo_total decimal(20,4) not null,
  margem_calculada decimal(7,2) not null,
  margem_minima decimal(5,2) not null,
  data_registro datetime not null default current_timestamp,
  index idx_historico_margem_produto_data (id_produto, data_registro),
  foreign key (id_produto) references produto (id_produto)
) default charset=utf8mb4 collate=utf8mb4_unicode_ci;

insert into unidade_medida (sigla, grupo, fator_conversao) values
  ('g', 'massa', 1),
  ('kg', 'massa', 1000),
  ('ml', 'volume', 1),
  ('l', 'volume', 1000),
  ('mm', 'comprimento', 1),
  ('m', 'comprimento', 1000),
  ('un', 'unidade', 1),
  ('min', 'tempo', 1),
  ('h', 'tempo', 60);