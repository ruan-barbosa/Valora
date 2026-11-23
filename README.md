# Valora

Sistema web que calcula quanto custa produzir cada produto (ou executar cada serviço), compara com o preço de venda e avisa quando a margem de lucro fica abaixo do mínimo.

Projeto SA · Senai Joinville · em desenvolvimento.

## Como o sistema funciona

1. Criação do usuário administrador.
2. O administrador cria uma empresa e define a margem de lucro mínima e o percentual de custos indiretos.
3. O administrador cria os demais usuários, cada um com seu perfil (gestor, comprador e produção).
4. O comprador cadastra os fornecedores.
5. O comprador insere os itens/insumos (material, mão de obra e máquina), cada um com unidade de medida e preço. Todo preço informado fica guardado no histórico.
6. O gestor insere um produto com seu preço de venda. O tipo do produto define como ele é cadastrado:
   - **Produto ou serviço:** informa a composição, ou seja, os itens que ele usa, com a quantidade e a perda de cada um. A quantidade pode ser informada em outra unidade do mesmo grupo (por exemplo, gramas para um item cadastrado em kg), e o sistema converte automaticamente.
   - **Revenda:** informa apenas o preço de compra e o fornecedor. O sistema monta a composição sozinho, com quantidade 1.
7. O sistema calcula o custo do produto: material + mão de obra + máquina formam o custo direto, e sobre ele é aplicado o percentual de custos indiretos, resultando no custo total.
8. Calcula a margem de lucro: (preço de venda − custo total) ÷ preço de venda, e compara com a margem mínima do produto (ou, se ele não tiver, a da empresa).
9. Cria um alerta caso a margem esteja abaixo do esperado.
10. Quando o comprador atualiza o preço de um insumo, o sistema grava o novo preço no histórico, recalcula todos os produtos que usam aquele insumo e cria alertas para os que ficarem abaixo da margem mínima.
11. A produção registra a quantidade produzida de cada produto, e o sistema guarda o custo do momento, formando um histórico que não muda quando os preços são reajustados.
12. O gestor pode simular um reajuste de preço de um insumo (por exemplo, +10%) e ver como ficam o custo e a margem dos produtos afetados, sem alterar nenhum dado.
13. Um painel mostra a lista de alertas coloridos e dois gráficos simples (margem por produto e evolução do preço de um insumo), com filtro por período, além da consulta dos históricos de preços e de produção.

## Perfis de usuário

| Perfil | O que faz | Valor no sistema |
|---|---|---|
| Administrador | Configura a empresa e os usuários; acessa todas as telas | `administrador` |
| Gestor | Cadastra produtos e fichas técnicas, acompanha alertas e usa o simulador | `gestor` |
| Comprador | Cadastra fornecedores e insumos e atualiza preços | `comprador` |
| Produção | Registra a produção e consulta fichas técnicas | `producao` |

As permissões por rota ficam em `app/routes.php` e podem ser ajustadas ali.

## Tecnologias

- **Back-end:** PHP 8.0 ou superior (testado com 8.3), PDO, padrão MVC próprio (sem framework)
- **Banco de dados:** MariaDB (compatível com MySQL), charset `utf8mb4`
- **Front-end:** HTML, Bootstrap 5, JavaScript e Chart.js
- **Testes:** Cypress (pasta `testes/`)
- **Hospedagem:** InfinityFree

## Estrutura de pastas

```
Valora/
├── .htaccess              manda todo acesso para a pasta public/
├── app/
│   ├── core/              núcleo: roteador, sessão, CSRF, login, conexão com o banco
│   ├── controllers/       recebem as requisições e escolhem a tela
│   ├── models/            acesso ao banco (um por tabela)
│   ├── services/          regras de negócio (cálculo de custo, alertas, conversão de unidades)
│   ├── views/             telas (layout, auth, dashboard, fichas, insumos, producao, simulador, configuracoes, erros)
│   └── routes.php         todas as rotas e quem pode acessar cada uma
├── config/
│   ├── config.example.php modelo de configuração (vai para o Git)
│   └── config.php         configuração real, com a senha do banco (NÃO vai para o Git)
├── database/
│   └── Script-Valora.sql  tabelas e unidades de medida iniciais
├── docs/                  ficha de requisitos e diagramas
├── public/                única pasta acessível pelo navegador
│   ├── index.php          ponto de entrada de todas as requisições
│   └── assets/            css, js e img
└── testes/                testes automatizados (Cypress)
```

Divisão de trabalho: `app/views` e `public/assets` são do front-end. O restante de `app/`, `config/` e `database/` é do back-end.

## Como rodar no seu computador

**Requisitos:** PHP 8.0+ com a extensão `pdo_mysql` e um servidor MariaDB ou MySQL.

1. **Banco de dados.** Crie o banco e importe o script (no DBeaver, phpMyAdmin ou terminal):

   ```sql
   create database valora character set utf8mb4 collate utf8mb4_unicode_ci;
   use valora;
   -- depois execute o conteúdo de database/Script-Valora.sql
   ```

   O script não tem `create database` nem `use`, porque na hospedagem o banco é criado pelo painel.

2. **Configuração.** Copie `config/config.example.php` para `config/config.php` e preencha:

   | Campo | Para que serve |
   |---|---|
   | `host` | Servidor do banco (`localhost` no seu computador) |
   | `banco` | Nome do banco (`valora`) |
   | `usuario` e `senha` | Usuário do banco com acesso a esse banco |
   | `debug` | `true` só no seu computador, para ver detalhes dos erros. Em produção, `false` |

3. **Servidor.** Escolha uma das opções:

   - **Servidor embutido do PHP**, na pasta do projeto:

     ```
     php -S localhost:8000 -t public
     ```

     Abra `http://localhost:8000`.

   - **XAMPP:** coloque a pasta em `htdocs/Valora` e abra `http://localhost/Valora/`. O Apache precisa estar com `mod_rewrite` ativo e `AllowOverride All`.

4. **Teste a conexão:** abra `/status`. O esperado é `{"app":"ok","banco":"ok"}`.

## Rotas

As rotas seguem o guia do front-end e estão em `app/routes.php`. Resumo:

| Área | Rotas |
|---|---|
| Acesso | `/login`, `/cadastro`, `/sair`, `/conta/senha` |
| Painel | `/dashboard`, `/dashboard/graficos`, `/alertas/visto` |
| Fichas técnicas | `/fichas`, `/fichas/novo`, `/fichas/editar/{id}`, `/fichas/salvar`, `/fichas/inativar`, `/fichas/calcular` |
| Insumos e fornecedores | `/insumos`, `/insumos/salvar`, `/insumos/atualizar-preco`, `/insumos/inativar`, `/insumos/historico`, `/fornecedores`, `/fornecedores/salvar`, `/fornecedores/inativar` |
| Produção | `/producao`, `/producao/registrar` |
| Simulador | `/simulador`, `/simulador/simular` |
| Configurações | `/configuracoes`, `/configuracoes/empresa`, `/usuarios/salvar`, `/usuarios/inativar` |

Toda requisição `POST` exige o token CSRF: o campo `csrf` do formulário ou, nas chamadas `fetch`, o cabeçalho `X-CSRF-Token`. As telas recebem a variável `$csrf`, e o `header.php` também a expõe em uma tag `<meta name="csrf">`.

## Publicação (InfinityFree)

1. Crie o banco pelo painel e importe o `database/Script-Valora.sql` pelo phpMyAdmin (selecione o banco antes de importar).
2. Envie os arquivos para a pasta `htdocs`, sem a pasta `.git`, sem `testes/` e sem `docs/`. O `.htaccess` da raiz precisa ficar na raiz de `htdocs`.
3. Crie o `config/config.php` no servidor com os dados do banco **de lá** (servidor do banco, nome com prefixo, usuário e senha do painel) e `debug` como `false`.
4. Abra `/status` para conferir a conexão.
5. Antes de entregar, remova a rota `/status` de `app/routes.php`.

## Segurança

- O `config/config.php` está no `.gitignore`: a senha do banco nunca vai para o GitHub.
- Só a pasta `public/` é acessível pelo navegador. O `.htaccess` da raiz esconde `app/`, `config/` e `database/`.
- Senhas são guardadas com `password_hash`, as consultas ao banco usam `PDO` com consultas preparadas, e todo texto vindo do banco é escapado nas telas.
- Em produção, os erros vão para o log do servidor e nunca para a tela.

## Testes

Os testes automatizados usam o Cypress e ficam em `testes/`:

```
cd testes
npm install
npm run open
```

Por enquanto a pasta tem só os exemplos da ferramenta. Os testes do Valora ainda serão escritos.

## Documentação

Na pasta `docs/`:

- Ficha de levantamento de requisitos (v2)
- Diagrama de classes

## Andamento

- [x] Requisitos e regras de negócio
- [x] Modelagem do banco e script SQL (banco no ar na hospedagem)
- [x] Estrutura MVC e núcleo do back-end (roteador, sessão, CSRF, login por perfil, helpers)
- [x] Telas do front-end (com dados de exemplo)
- [ ] Acesso: login, cadastro da empresa e do administrador, configurações
- [ ] Insumos, fornecedores e histórico de preços
- [ ] Fichas técnicas, cálculo de custo e margem, conversão de unidades
- [ ] Alertas e recálculo ao mudar um preço
- [ ] Registro de produção
- [ ] Simulador de reajuste
- [ ] Painel com alertas e gráficos
- [ ] Testes, integração das telas com o back-end e entrega

## Fora do escopo (versões futuras)

Relatórios avançados e outros gráficos, registro de auditoria das alterações e resumo explicativo dos alertas por IA.
