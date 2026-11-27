<?php

// Tabela historico_preco: cada preço informado para um insumo vira uma linha (nunca é apagada).
class HistoricoPreco
{
    public static function registrar(int $idItem, ?int $idFornecedor, int $idUsuario, float $preco): void
    {
        $sql = 'insert into historico_preco (id_item, id_fornecedor, id_usuario, preco) values (?, ?, ?, ?)';
        Database::conectar()->prepare($sql)->execute([$idItem, $idFornecedor, $idUsuario, $preco]);
    }

    /** Do mais recente para o mais antigo. Só devolve se o insumo for da empresa informada. */
    public static function listarPorItem(int $idItem, int $idEmpresa): array
    {
        $sql = 'select h.data_alteracao, h.preco, f.nome as fornecedor, u.nome as usuario
                from historico_preco h
                join item i on i.id_item = h.id_item
                left join fornecedor f on f.id_fornecedor = h.id_fornecedor
                left join usuario u on u.id_usuario = h.id_usuario
                where h.id_item = ? and i.id_empresa = ?
                order by h.data_alteracao desc, h.id_historico desc';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idItem, $idEmpresa]);

        $linhas = $consulta->fetchAll();
        foreach ($linhas as &$linha) {
            $linha['preco'] = (float) $linha['preco'];
        }

        return $linhas;
    }

    /** Preços de um insumo entre duas datas (AAAA-MM-DD), do mais antigo para o mais recente, para o gráfico do dashboard. */
    public static function evolucao(int $idItem, int $idEmpresa, string $inicio, string $fim): array
    {
        $sql = "select date_format(h.data_alteracao, '%d/%m/%Y') as data, h.preco
                from historico_preco h
                join item i on i.id_item = h.id_item
                where h.id_item = ? and i.id_empresa = ? and h.data_alteracao >= ? and h.data_alteracao <= ?
                order by h.data_alteracao, h.id_historico";
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idItem, $idEmpresa, $inicio . ' 00:00:00', $fim . ' 23:59:59']);

        $linhas = $consulta->fetchAll();
        foreach ($linhas as &$linha) {
            $linha['preco'] = (float) $linha['preco'];
        }

        return $linhas;
    }
}
