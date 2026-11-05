Ideia de projeto SA

Um sistema que calcula quanto custa produzir cada produto (ou executar cada serviço), compara com o preço de venda e avisa quando a margem de lucro fica abaixo do mínimo.

Como o sistema vai funcionar:

1. criação de usuário administrador  
2. administrador cria uma empresa e define a margem de lucro mínima e o percentual de custos indiretos  
3. administrador cria os demais usuários, cada um com seu perfil (gestor, comprador e produção)  
4. define um fornecedor  
5. insere os itens/insumos (material, mão de obra e máquina), cada um com unidade de medida e preço; todo preço informado fica guardado no histórico  
6. insere um produto com seu preço de venda. O tipo do produto define como ele é cadastrado: **produto ou serviço:** informa a composição, ou seja, os itens que ele usa, com a quantidade e a perda de cada um; a quantidade pode ser informada em outra unidade do mesmo grupo (por exemplo, gramas para um item cadastrado em kg), e o sistema converte automaticamente **revenda:** informa apenas o preço de compra e o fornecedor; o sistema monta a composição sozinho, com quantidade 1  
7. o sistema calcula o custo do produto: material \+ mão de obra \+ máquina formam o custo direto, e sobre ele é aplicado o percentual de custos indiretos, resultando no custo total  
8. calcula a margem de lucro: (preço de venda − custo total) ÷ preço de venda, e compara com a margem mínima do produto (ou, se ele não tiver, a da empresa)  
9. cria um alerta caso a margem esteja abaixo do esperado  
10. quando o comprador atualiza o preço de um insumo, o sistema grava o novo preço no histórico, recalcula todos os produtos que usam aquele insumo e cria alertas para os que ficarem abaixo da margem mínima  
11. a produção registra a quantidade produzida de cada produto, e o sistema guarda o custo do momento, formando um histórico que não muda quando os preços são reajustados  
12. o gestor pode simular um reajuste de preço de um insumo (por exemplo, \+10%) e ver como ficam o custo e a margem dos produtos afetados, sem alterar nenhum dado  
13. um painel mostra a lista de alertas coloridos e dois gráficos simples (margem por produto e evolução do preço de um insumo), com filtro por período, além da consulta dos históricos de preços e de produção