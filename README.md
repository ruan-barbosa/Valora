# Valora

Sistema web para controlar custos de produtos, serviços e itens de revenda, acompanhar margens e alertar quando uma margem fica abaixo do mínimo definido pela empresa.

Projeto SA · SENAI Joinville.

## Funcionalidades incluídas

- Cadastro inicial da empresa e do administrador, login e troca de senha.
- Gestão de usuários por perfil: administrador, gestor, comprador e produção.
- Cadastro, edição, inativação e reativação de insumos e fornecedores.
- Histórico de cada alteração de preço, com data, fornecedor e responsável.
- Cadastro e manutenção de produtos, serviços e revendas.
- Fichas técnicas com itens, unidades compatíveis, quantidades e perdas.
- Conversão automática de unidades do mesmo grupo (ex.: 500 g para 0,5 kg).
- Cálculo de custo direto, rateio de custos indiretos, custo total e margem.
- Revenda com composição criada automaticamente a partir de um item e quantidade 1.
- Alertas de margem, recálculo ao mudar preços e encerramento do alerta quando a margem se recupera.
- Simulador de reajuste que não grava os preços reais.
- Registros de produção/execução com custo unitário congelado no momento do lançamento.
- Dashboard com alertas, gráfico de margens por período e evolução histórica do preço de um insumo.
- Consulta do histórico de produção, filtros por período e correção/exclusão de lançamentos pelo administrador.
- Rótulos de produção adaptados para empresas do segmento de serviços.

## Perfis e permissões

| Perfil | Permissões principais |
|---|---|
| Administrador | Acesso integral, configura empresa e usuários, consulta/edita/exclui lançamentos de produção |
| Gestor | Produtos, fichas técnicas, alertas, dashboard e simulador |
| Comprador | Insumos, fornecedores, atualização de preços e histórico |
| Produção | Registra produção/execução e consulta o próprio histórico de lançamentos |

O administrador recebe acesso integral pelo controle central de permissões. As demais permissões ficam declaradas em `app/routes.php` e são conferidas pelo roteador antes da ação.

## Tecnologias

- PHP 8.0 ou superior, PDO e padrão MVC.
- MariaDB/MySQL com `utf8mb4` e extensão PHP `pdo_mysql`.
- Bootstrap 5, JavaScript e Chart.js.
- Cypress para testes de navegação/autenticação.

## Instalação local

1. Instale PHP 8+, MariaDB/MySQL e habilite a extensão `pdo_mysql`.
2. Crie o banco:

   ```sql
   CREATE DATABASE valora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Importe `database/Script-Valora.sql` no banco `valora`.
4. Copie `config/config.example.php` para `config/config.php` e preencha host, banco, usuário e senha. Não compartilhe o arquivo real de configuração.
5. Na raiz do projeto, execute:

   ```bash
   php -S localhost:8000 -t public
   ```

6. Abra `http://localhost:8000`, crie a empresa e o usuário administrador.

### Atualizar um banco já existente

Se o banco foi criado com uma versão anterior do script, faça primeiro um backup e execute **uma única vez** `database/Migracao-Valora-Final.sql`. Essa migração adiciona o estado de resolução aos alertas, o histórico de snapshots de margem e índices usados pelas consultas. Não execute a migração em banco recém-criado pelo script atualizado, pois as estruturas já estarão presentes.

## Critério de cálculo da margem

A ficha de levantamento v2 deixa o campo da fórmula da margem (RN-03) sem definição. Para manter um critério único no sistema, a implementação atual calcula a margem sobre o preço de venda: `(preço de venda - custo total) / preço de venda × 100`. Essa é uma decisão técnica adotada para a implementação e deve ser confirmada pelo responsável pelo projeto antes do aceite final, caso a regra de negócio pretendida seja outra.

## Backup periódico

O script `scripts/backup_database.php` é executado somente pela linha de comando e usa `mysqldump`. Os arquivos `.sql.gz` ficam em `storage/backups`, fora da pasta pública, com permissão restrita. O padrão mantém 14 dias de backups; ajuste `backup_retencao_dias`, `backup_path` e `mysqldump` em `config/config.php` conforme o ambiente.

Execução manual:

```bash
php scripts/backup_database.php
```

Agende esse comando diariamente no cron (Linux) ou no Agendador de Tarefas do Windows. Verifique periodicamente se o arquivo foi criado e faça um teste de restauração em um banco separado. A disponibilidade de `mysqldump` e de tarefas agendadas depende do provedor de hospedagem.

## Testes

### Testes isolados de regras de negócio

Execute sem precisar conectar ao MariaDB:

```bash
php testes/php/teste_regras_negocio.php
```

O teste valida conversão de unidade, perda, custos indiretos e margem.

### Cypress

```bash
cd testes
npm install
npm test
```

O teste básico cobre a tela de login e o redirecionamento de visitante para área protegida. Para habilitar também o teste autenticado, configure as variáveis de ambiente `CYPRESS_VALORA_TEST_USER` e `CYPRESS_VALORA_TEST_PASSWORD` com uma conta de teste já cadastrada (preferencialmente uma conta descartável).

No PowerShell:

```powershell
$env:CYPRESS_BASE_URL = "http://localhost:8000"
$env:CYPRESS_VALORA_TEST_USER = "usuario_teste"
$env:CYPRESS_VALORA_TEST_PASSWORD = "senha_de_teste"
npm test
```

### Medição de desempenho

O script `testes/performance/check-p95.mjs` mede latência HTTP e informa o percentil 95. Exemplo:

```bash
VALORA_URL=http://localhost:8000/login VALORA_REQUISICOES=100 node testes/performance/check-p95.mjs
```

O requisito RNF-01 considera P95 de até 2 segundos. A medição deve ser repetida no ambiente alvo, com autenticação e dados representativos. O pacote não afirma que esse limite foi comprovado em produção.

### Verificações locais incluídas nesta entrega

- Verificação de sintaxe PHP em todos os arquivos PHP do projeto.
- Verificação de sintaxe JavaScript com Node.js.
- Testes isolados de cálculo de custo, conversão de unidade e margem.
- Inspeção estrutural do script SQL, rotas e pacotes.

O teste integrado com MariaDB, a medição de carga em servidor real, o teste de restauração de backup e a validação em Chrome/Edge/Firefox e dispositivos físicos precisam ser executados no ambiente de instalação.

## Publicação

1. Configure o banco de produção e importe o script SQL atualizado (ou aplique a migração se o banco já existir).
2. Envie os arquivos da aplicação, sem `.git/`, `testes/` e, se desejado, sem `docs/`.
3. Crie `config/config.php` no servidor com as credenciais daquele ambiente e `debug` desativado.
4. Configure HTTPS e permissões de escrita apenas para `storage/backups`.
5. Programe e teste backups. Caso o provedor não permita executar `mysqldump` ou tarefas agendadas, faça os backups pelo painel do banco/provedor.
6. Teste o login e os fluxos com uma conta de cada perfil antes da entrega.

## Estrutura principal

```text
app/
  controllers/   controladores MVC
  core/          roteador, sessão, CSRF, autenticação e helpers
  models/        acesso ao MariaDB por PDO
  services/      cálculo, conversão e alertas
  views/         telas da aplicação
  routes.php     rotas e permissões
config/          config.example.php (config.php é local e ignorado pelo Git)
database/        script completo e migração
public/          entrada web, CSS e JavaScript
scripts/         backup CLI
testes/          Cypress, testes PHP e medição P95
```

## Fora do escopo da versão inicial

Emissão de notas fiscais, integração com ERP/contabilidade, inventário completo de estoque, aplicativo mobile nativo, previsão de preços por IA, relatórios avançados, trilha de auditoria futura e explicações de alertas por IA.
