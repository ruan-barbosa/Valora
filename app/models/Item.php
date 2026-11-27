<?php

// Tabela item (insumos). Toda consulta filtra pela empresa do usuário logado.
class Item
{
    public const TIPOS = ['material', 'mao_de_obra', 'maquina'];

    // Último fornecedor informado em algum preço do insumo (mão de obra e máquina podem não ter nenhum).
    private const ULTIMO_FORNECEDOR_ID = 'select h.id_fornecedor from historico_preco h
        where h.id_item = i.id_item and h.id_fornecedor is not null
        order by h.data_alteracao desc, h.id_historico desc limit 1';

    /** Lista da tela de insumos. Por padrão só os ativos. */
    public static function listar(int $idEmpresa, bool $incluirInativos = false): array
    {
        $sql = 'select i.id_item, i.nome, i.tipo, i.id_unidade, u.sigla, i.preco_atual, i.ativo,
                       (select f.nome from fornecedor f where f.id_fornecedor = (' . self::ULTIMO_FORNECEDOR_ID . ')) as ultimo_fornecedor
                from item i
                join unidade_medida u on u.id_unidade = i.id_unidade
                where i.id_empresa = ? and i.tipo <> \'revenda\'' . ($incluirInativos ? '' : ' and i.ativo = 1') . '
                order by i.ativo desc, i.nome';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa]);

        $itens = $consulta->fetchAll();
        foreach ($itens as &$item) {
            $item['preco_atual'] = (float) $item['preco_atual'];
            $item['ativo'] = (bool) $item['ativo'];
        }

        return $itens;
    }

    public static function buscar(int $idItem, int $idEmpresa): ?array
    {
        $sql = 'select i.id_item, i.nome, i.tipo, i.id_unidade, u.sigla, u.grupo, u.fator_conversao, i.preco_atual, i.ativo,
                       (' . self::ULTIMO_FORNECEDOR_ID . ') as id_ultimo_fornecedor
                from item i
                join unidade_medida u on u.id_unidade = i.id_unidade
                where i.id_item = ? and i.id_empresa = ? and i.tipo <> ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idItem, $idEmpresa, 'revenda']);

        $item = $consulta->fetch();
        if (!$item) {
            return null;
        }

        $item['preco_atual'] = (float) $item['preco_atual'];
        $item['fator_conversao'] = (float) $item['fator_conversao'];
        $item['ativo'] = (bool) $item['ativo'];
        $item['id_ultimo_fornecedor'] = $item['id_ultimo_fornecedor'] !== null ? (int) $item['id_ultimo_fornecedor'] : null;

        return $item;
    }

    public static function nomeExiste(int $idEmpresa, string $nome, int $ignorarId = 0): bool
    {
        $sql = 'select 1 from item where id_empresa = ? and nome = ? and id_item <> ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa, $nome, $ignorarId]);

        return (bool) $consulta->fetchColumn();
    }

    /** O insumo já faz parte da ficha técnica de algum produto? */
    public static function emUso(int $idItem): bool
    {
        $consulta = Database::conectar()->prepare('select 1 from composicao where id_item = ? limit 1');
        $consulta->execute([$idItem]);

        return (bool) $consulta->fetchColumn();
    }

    /** Cria o insumo e grava o primeiro preço no histórico, tudo ou nada. */
    public static function criar(int $idEmpresa, int $idUsuario, string $nome, string $tipo, int $idUnidade, float $preco, ?int $idFornecedor): int
    {
        $pdo = Database::conectar();
        $propria = !$pdo->inTransaction();   // se quem chamou já abriu uma transação, ela decide o commit

        try {
            if ($propria) {
                $pdo->beginTransaction();
            }

            $sql = 'insert into item (id_empresa, id_unidade, nome, tipo, preco_atual) values (?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([$idEmpresa, $idUnidade, $nome, $tipo, $preco]);
            $idItem = (int) $pdo->lastInsertId();

            HistoricoPreco::registrar($idItem, $idFornecedor, $idUsuario, $preco);

            if ($propria) {
                $pdo->commit();
            }
        } catch (Throwable $erro) {
            if ($propria && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $erro;
        }

        return $idItem;
    }

    /** Dados do cadastro. O preço muda só por atualizarPreco(), que guarda o histórico. */
    public static function atualizar(int $idItem, int $idEmpresa, string $nome, string $tipo, int $idUnidade): void
    {
        $sql = 'update item set nome = ?, tipo = ?, id_unidade = ? where id_item = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$nome, $tipo, $idUnidade, $idItem, $idEmpresa]);
    }

    /** Muda o preço atual e grava a linha no histórico, tudo ou nada. */
    public static function atualizarPreco(int $idItem, int $idEmpresa, int $idUsuario, float $preco, ?int $idFornecedor): void
    {
        $pdo = Database::conectar();
        $propria = !$pdo->inTransaction();

        try {
            if ($propria) {
                $pdo->beginTransaction();
            }

            $sql = 'update item set preco_atual = ? where id_item = ? and id_empresa = ?';
            $pdo->prepare($sql)->execute([$preco, $idItem, $idEmpresa]);

            HistoricoPreco::registrar($idItem, $idFornecedor, $idUsuario, $preco);

            if ($propria) {
                $pdo->commit();
            }
        } catch (Throwable $erro) {
            if ($propria && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $erro;
        }
    }

    public static function definirAtivo(int $idItem, int $idEmpresa, bool $ativo): void
    {
        $sql = 'update item set ativo = ? where id_item = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$ativo ? 1 : 0, $idItem, $idEmpresa]);
    }
}
