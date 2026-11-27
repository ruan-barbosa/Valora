<?php

// Painel principal: resumo, últimos alertas e dados dos gráficos.
class DashboardController extends Controller
{
    public function index(): void
    {
        $idEmpresa = Auth::empresaId();

        $resumo = ['produtos_abaixo_margem' => 0, 'alertas_nao_vistos' => 0, 'producao_mes' => 0];
        $alertas = [];
        $insumos = [];

        // Resumo, alertas e gráficos são do gestor (e do administrador).
        if (Auth::permite(['gestor'])) {
            $resumo = [
                'produtos_abaixo_margem' => Alerta::contarProdutosAbaixoDaMargem($idEmpresa),
                'alertas_nao_vistos'     => Alerta::contarNaoVistos($idEmpresa),
                'producao_mes'           => LancamentoProducao::totalDoMes($idEmpresa),
            ];
            $alertas = Alerta::listarRecentes($idEmpresa, 8);
            $insumos = array_map(
                fn($i) => ['id_item' => $i['id_item'], 'nome' => $i['nome']],
                Item::listar($idEmpresa)
            );
        }

        $this->view('dashboard/index', [
            'resumo'         => $resumo,
            'alertas'        => $alertas,
            'insumos_filtro' => $insumos,
        ]);
    }

    /** JSON dos alertas e contadores, consultado pelo painel a cada poucos segundos. */
    public function alertas(): void
    {
        if (!Auth::permite(['gestor'])) {
            $this->json(['erro' => 'Acesso não autorizado.'], 403);
        }

        $idEmpresa = Auth::empresaId();
        $this->json([
            'produtos_abaixo_margem' => Alerta::contarProdutosAbaixoDaMargem($idEmpresa),
            'alertas_nao_vistos' => Alerta::contarNaoVistos($idEmpresa),
            'alertas' => Alerta::listarRecentes($idEmpresa, 8),
        ]);
    }

    /** JSON dos gráficos. GET /dashboard/graficos?periodo_inicio=AAAA-MM-DD&periodo_fim=AAAA-MM-DD&id_item=N */
    public function graficos(): void
    {
        if (!Auth::permite(['gestor'])) {
            $this->erro403();
            return;
        }

        $idEmpresa = Auth::empresaId();
        $inicio = $this->dataValida($this->get('periodo_inicio'), date('Y-m-01'));
        $fim = $this->dataValida($this->get('periodo_fim'), date('Y-m-d'));
        if ($inicio > $fim) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        // O gráfico respeita o período usando fotografias de margem gravadas a cada recálculo.
        $margens = HistoricoMargem::ultimasPorProduto($idEmpresa, $inicio, $fim);

        $evolucao = ['datas' => [], 'precos' => []];
        $idItem = (int) $this->get('id_item');
        if ($idItem > 0 && Item::buscar($idItem, $idEmpresa)) {
            foreach (HistoricoPreco::evolucao($idItem, $idEmpresa, $inicio, $fim) as $linha) {
                $evolucao['datas'][] = $linha['data'];
                $evolucao['precos'][] = $linha['preco'];
            }
        }

        $this->json(['margem_produtos' => $margens, 'evolucao_insumo' => $evolucao]);
    }

    /** Aceita só datas no formato AAAA-MM-DD que existam de verdade; senão usa o padrão. */
    private function dataValida(string $texto, string $padrao): string
    {
        $data = DateTime::createFromFormat('Y-m-d', $texto);
        return ($data && $data->format('Y-m-d') === $texto) ? $texto : $padrao;
    }
}
