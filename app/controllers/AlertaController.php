<?php

// Alertas de margem. A lista aparece no dashboard; aqui fica só a ação de marcar como visto.
class AlertaController extends Controller
{
    public function marcarVisto(): void
    {
        $id = (int) Validador::texto('id_alerta');

        if (Alerta::marcarVisto($id, Auth::empresaId())) {
            $this->redirecionar('dashboard', 'Alerta marcado como visto.');
        }

        $this->redirecionar('dashboard', 'Alerta não encontrado.', 'erro');
    }
}
