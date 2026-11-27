<?php

// Fornecedores da empresa (tela Insumos e preços > Fornecedores).
class FornecedorController extends Controller
{
    private const TELA = 'insumos/fornecedores';

    public function index(): void
    {
        $incluirInativos = $this->get('inativos') === '1';

        $this->view('insumos/fornecedores', [
            'fornecedores' => Fornecedor::listar(Auth::empresaId(), $incluirInativos),
            'incluirInativos' => $incluirInativos,
        ]);
    }

    /** Cria o fornecedor (sem id_fornecedor) ou edita um existente (com id_fornecedor). */
    public function salvar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id      = (int) Validador::texto('id_fornecedor');
        $nome    = Validador::texto('nome');
        $contato = Validador::texto('contato');

        $antigos = ['nome' => $nome, 'contato' => $contato];
        $erros = [];

        if ($id > 0 && !Fornecedor::buscar($id, $idEmpresa)) {
            $this->redirecionar(self::TELA, 'Fornecedor não encontrado.', 'erro');
        }

        if (!Validador::tamanho($nome, 2, 100)) {
            $erros[] = 'Informe o nome do fornecedor (2 a 100 caracteres).';
        } elseif (Fornecedor::nomeExiste($idEmpresa, $nome, $id)) {
            $erros[] = 'Já existe um fornecedor com esse nome.';
        }
        if (!Validador::tamanho($contato, 0, 100)) {
            $erros[] = 'O contato deve ter no máximo 100 caracteres.';
        }

        if ($erros) {
            $this->redirecionarComErros(self::TELA, $erros, $antigos);
        }

        try {
            if ($id > 0) {
                Fornecedor::atualizar($id, $idEmpresa, $nome, $contato);
                $mensagem = 'Fornecedor atualizado.';
            } else {
                Fornecedor::criar($idEmpresa, $nome, $contato);
                $mensagem = 'Fornecedor cadastrado.';
            }
        } catch (PDOException $erro) {
            if (($erro->errorInfo[1] ?? 0) === 1062) {
                $this->redirecionarComErros(self::TELA, ['Já existe um fornecedor com esse nome.'], $antigos);
            }
            throw $erro;
        }

        $this->redirecionar(self::TELA, $mensagem);
    }

    public function inativar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_fornecedor');

        if (!Fornecedor::buscar($id, $idEmpresa)) {
            $this->redirecionar(self::TELA, 'Fornecedor não encontrado.', 'erro');
        }

        Fornecedor::definirAtivo($id, $idEmpresa, false);
        $this->redirecionar(self::TELA, 'Fornecedor inativado.');
    }
    public function reativar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_fornecedor');
        if (!Fornecedor::buscar($id, $idEmpresa)) {
            $this->redirecionar(self::TELA, 'Fornecedor não encontrado.', 'erro');
        }
        Fornecedor::definirAtivo($id, $idEmpresa, true);
        $this->redirecionar(self::TELA . '?inativos=1', 'Fornecedor reativado.');
    }

}
