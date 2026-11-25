<?php

// PROVISÓRIO (fase A/B): só mostra a tela para o login poder terminar em algum lugar.
// Os dados reais do painel (resumo, margens, alertas e gráficos) entram na fase H.
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->view('dashboard/index');
    }
}