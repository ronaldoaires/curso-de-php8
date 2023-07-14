<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\CategoriaModelo;
use sistema\Nucleo\Helpers;
use sistema\Modelo\PostModelo;

/**
 * Classe AdminCategorias
 *
 * @author Ronaldo Aires
 */
class AdminCategorias extends AdminControlador
{

    /**
     * Lista categorias
     * @return void
     */
    public function listar(): void
    {
        $categorias = new CategoriaModelo();

        echo $this->template->renderizar('categorias/listar.html', [
            'categorias' => $categorias->busca()->ordem('titulo ASC')->resultado(true),
            'total' => [
                'categorias' => $categorias->total(),
                'categoriasAtiva' => $categorias->busca('status = 1')->total(),
                'categoriasInativa' => $categorias->busca('status = 0')->total(),
            ]
        ]);
    }

    /**
     * Cadastra uma categoria
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDados($dados)) {
                $categoria = new CategoriaModelo();

                $categoria->usuario_id = $this->usuario->id;
                $categoria->slug = Helpers::slug($dados['titulo']);
                $categoria->titulo = $dados['titulo'];
                $categoria->texto = $dados['texto'];
                $categoria->status = $dados['status'];

                if ($categoria->salvar()) {
                    $this->mensagem->sucesso('Categoria cadastrada com sucesso')->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                } else {
                    $this->mensagem->erro($categoria->erro())->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                }
            }
        }

        echo $this->template->renderizar('categorias/formulario.html', [
            'categoria' => $dados
        ]);
    }

    /**
     * Edita uma categoria pelo ID
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $categoria = (new CategoriaModelo())->buscaPorId($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDados($dados)) {
                $categoria = (new CategoriaModelo())->buscaPorId($categoria->id);

                $categoria->usuario_id = $this->usuario->id;
                $categoria->slug = Helpers::slug($dados['titulo']);
                $categoria->titulo = $dados['titulo'];
                $categoria->texto = $dados['texto'];
                $categoria->status = $dados['status'];
                $categoria->atualizado_em = date('Y-m-d H:i:s');

                if ($categoria->salvar()) {
                    $this->mensagem->sucesso('Categoria atualizada com sucesso')->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                } else {
                    $this->mensagem->erro($categoria->erro())->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                }
            }
        }

        echo $this->template->renderizar('categorias/formulario.html', [
            'categoria' => $categoria
        ]);
    }

    /**
     * Valida os dados do formulário
     * @param array $dados
     * @return bool
     */
    private function validarDados(array $dados): bool
    {
        if (empty($dados['titulo'])) {
            $this->mensagem->alerta('Escreva um título para a Categoria!')->flash();
            return false;
        }
        return true;
    }

    /**
     * Deleta uma categoria pelo ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $categoria = (new CategoriaModelo())->buscaPorId($id);
            $posts = (new PostModelo())->busca("categoria_id = {$categoria->id}")->resultado(true);

            if (!$categoria) {
                $this->mensagem->alerta('O categoria que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/categorias/listar');
            } elseif ($posts) {
                $this->mensagem->alerta("A categoria {$categoria->titulo} tem {$categoria->totalPosts($categoria->id)} posts cadastrados, delete ou altere os posts antes de deletar!")->flash();
                Helpers::redirecionar('admin/categorias/listar');
            } else {
                if ($categoria->deletar()) {
                    $this->mensagem->sucesso('Categoria deletada com sucesso!')->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                } else {
                    $this->mensagem->erro($categoria->erro())->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                }
            }
        }
    }

}
