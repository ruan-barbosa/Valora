<?php

// Tabela composicao: os insumos de cada ficha técnica, com a quantidade, a unidade informada e a perda.
class Composicao
{
    private const CAMPOS = 'select c.id_produto, c.id_item, i.nome as nome_item, i.tipo as tipo_item, i.preco_atual,
                   c.quantidade, c.percentual_perda, c.id_unidade, uc.sigla,
                   uc.grupo as grupo_linha, uc.fator_conversao as fator_linha,
                   ui.grupo as grupo_item, ui.fator_conversao as fator_item,
                   (select h.id_fornecedor from historico_preco h
                    where h.id_item = i.id_item and h.id_fornecedor is not null
                    order by h.data_alteracao desc, h.id_historico desc limit 1) as id_ultimo_fornecedor
            from composicao c
            join item i on i.id_item = c.id_item
            join unidade_medida uc on uc.id_unidade = c.id_unidade
            join unidade_medida ui on ui.id_unidade = i.id_unidade';

    private static function normalizar(array $linha): array
    {
        $linha['id_produto']       = (int) $linha['id_produto'];
        $linha['id_item']          = (int) $linha['id_item'];
        $linha['id_unidade']       = (int) $linha['id_unidade'];
        $linha['preco_atual']      = (float) $linha['preco_atual'];
        $linha['quantidade']       = (float) $linha['quantidade'];
        $linha['percentual_perda'] = (float) $linha['percentual_perda'];
        $linha['fator_linha']      = (float) $linha['fator_linha'];
        $linha['fator_item']       = (float) $linha['fator_item'];
        $linha['id_ultimo_fornecedor'] = $linha['id_ultimo_fornecedor'] !== null ? (int) $linha['id_ultimo_fornecedor'] : null;

        return $linha;
    }

    /** Linhas de vários produtos de uma vez, agrupadas por id_produto (para não fazer uma consulta por produto). */
    public static function linhasPorProdutos(array $idsProdutos): array
    {
        if (!$idsProdutos) {
            return [];
        }

        $sql = self::CAMPOS . ' where c.id_produto in (' . implode(',', array_fill(0, count($idsProdutos), '?')) . ')
                order by c.id_composicao';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute(array_map('intval', $idsProdutos));

        $grupos = [];
        foreach ($consulta->fetchAll() as $linha) {
            $linha = self::normalizar($linha);
            $grupos[$linha['id_produto']][] = $linha;
        }

        return $grupos;
    }

    public static function listarDoProduto(int $idProduto): array
    {
        return self::linhasPorProdutos([$idProduto])[$idProduto] ?? [];
    }

    /** Troca toda a ficha técnica do produto. Chame dentro de uma transação. */
    public static function substituir(int $idProduto, array $linhas): void
    {
        $pdo = Database::conectar();
        $pdo->prepare('delete from composicao where id_produto = ?')->execute([$idProduto]);

        $insere = $pdo->prepare('insert into composicao (id_produto, id_item, id_unidade, quantidade, percentual_perda) values (?, ?, ?, ?, ?)');
        foreach ($linhas as $linha) {
            $insere->execute([$idProduto, $linha['id_item'], $linha['id_unidade'], $linha['quantidade'], $linha['percentual_perda']]);
        }
    }
}
