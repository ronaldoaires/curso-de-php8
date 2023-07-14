<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\PostModelo;
use sistema\Modelo\CategoriaModelo;
use sistema\Nucleo\Helpers;
use Verot\Upload\Upload;

/**
 * Classe AdminPosts
 *
 * @author Ronaldo Aires
 */
class AdminPosts extends AdminControlador
{

    private string $capa;

    /**
     * Método responsável por exibir os dados tabulados utilizando o plugin datatables
     * @return void
     */
    public function datatable(): void
    {
        $datatable = $_REQUEST;
        $datatable = filter_var_array($datatable, FILTER_SANITIZE_SPECIAL_CHARS);

        $limite = $datatable['length'];
        $offset = $datatable['start'];
        $busca = $datatable['search']['value'];

        $colunas = [
            0 => 'id',
            2 => 'titulo',
            3 => 'categoria_id',
            4 => 'visitas',
            5 => 'status',
        ];

        $ordem = " " . $colunas[$datatable['order'][0]['column']] . " ";
        $ordem .= " " . $datatable['order'][0]['dir'] . " ";

        $posts = new PostModelo();

        if (empty($busca)) {
            $posts->busca()->ordem($ordem)->limite($limite)->offset($offset);
            $total = (new PostModelo())->busca(null, 'COUNT(id)', 'id')->total();
        } else {
            $posts->busca("id LIKE '%{$busca}%' OR titulo LIKE '%{$busca}%' ")->limite($limite)->offset($offset);
            $total = $posts->total();
        }

        $dados = [];

        if ($posts->resultado(true)) {
            foreach ($posts->resultado(true) as $post) {
                $dados[] = [
                    $post->id,
                    $post->capa,
                    $post->titulo,
                    $post->categoria()->titulo ?? '-----',
                    Helpers::formatarNumero($post->visitas),
                    $post->status
                ];
            }
        }


        $retorno = [
            "draw" => $datatable['draw'],
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $dados
        ];

        echo json_encode($retorno);
    }

    /**
     * Lista posts
     * @return void
     */
    public function listar(): void
    {
        $posts = new PostModelo();

        echo $this->template->renderizar('posts/listar.html', [
            'total' => [
                'posts' => $posts->busca(null, 'COUNT(id)', 'id')->total(),
                'postsAtivo' => $posts->busca('status = :s', 's=1 COUNT(status))', 'status')->total(),
                'postsInativo' => $posts->busca('status = :s', 's=0 COUNT(status)', 'status')->total(),
            ]
        ]);
    }

    /**
     * Cadastra posts
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {
                $post = new PostModelo();

                $post->usuario_id = $this->usuario->id;
                $post->categoria_id = $dados['categoria_id'];
                $post->slug = Helpers::slug($dados['titulo']);
                $post->titulo = $dados['titulo'];
                $post->texto = $dados['texto'];
                $post->status = $dados['status'];
                $post->capa = $this->capa ?? null;
                $post->capa_ativa = $dados['capa_ativa'];

                if ($post->salvar()) {
                    $this->mensagem->sucesso('Post cadastrado com sucesso')->flash();
                    Helpers::redirecionar('admin/posts/listar');
                } else {
                    $this->mensagem->erro($post->erro())->flash();
                    Helpers::redirecionar('admin/posts/listar');
                }
            }
        }

        echo $this->template->renderizar('posts/formulario.html', [
            'categorias' => (new CategoriaModelo())->busca('status = 1')->resultado(true),
            'post' => $dados
        ]);
    }

    /**
     * Edita post pelo ID
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $post = (new PostModelo())->buscaPorId($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {



            if ($this->validarDados($dados)) {
                $post = (new PostModelo())->buscaPorId($id);

                $post->usuario_id = $this->usuario->id;
                $post->categoria_id = $dados['categoria_id'];
                $post->slug = Helpers::slug($dados['titulo']);
                $post->titulo = $dados['titulo'];
                $post->texto = $dados['texto'];
                $post->status = $dados['status'];
                $post->atualizado_em = date('Y-m-d H:i:s');
                $post->capa_ativa = $dados['capa_ativa'];

                //atualizar a capa no DB e no servidor, se um novo arquivo de imagem for enviado
                if (!empty($_FILES['capa']["name"])) {
                    if ($post->capa && file_exists("uploads/imagens/{$post->capa}")) {
                        unlink("uploads/imagens/{$post->capa}");
                        unlink("uploads/imagens/thumbs/{$post->capa}");
                    }
                    $post->capa = $this->capa ?? null;
                }

                if ($post->salvar()) {
                    $this->mensagem->sucesso('Post atualizado com sucesso')->flash();
                    Helpers::redirecionar('admin/posts/listar');
                } else {
                    $this->mensagem->erro($post->erro())->flash();
                    Helpers::redirecionar('admin/posts/listar');
                }
            }
        }

        echo $this->template->renderizar('posts/formulario.html', [
            'post' => $post,
            'categorias' => (new CategoriaModelo())->busca('status = 1')->resultado(true)
        ]);
    }

    /**
     * Valida os dados do formulário
     * @param array $dados
     * @return bool
     */
    public function validarDados(array $dados): bool
    {

        if (empty($dados['titulo'])) {
            $this->mensagem->alerta('Escreva um título para o Post!')->flash();
            return false;
        }
        if (empty($dados['texto'])) {
            $this->mensagem->alerta('Escreva um texto para o Post!')->flash();
            return false;
        }

        if (!empty($_FILES['capa'])) {
            $upload = new Upload($_FILES['capa'], 'pt_BR');
            if ($upload->uploaded) {
                $titulo = $upload->file_new_name_body = Helpers::slug($dados['titulo']);
                $upload->jpeg_quality = 90;
                $upload->image_convert = 'jpg';
                $upload->process('uploads/imagens/');

                if ($upload->processed) {
                    $this->capa = $upload->file_dst_name;
                    $upload->file_new_name_body = $titulo;
                    $upload->image_resize = true;
                    $upload->image_x = 540;
                    $upload->image_y = 304;
                    $upload->jpeg_quality = 70;
                    $upload->image_convert = 'jpg';
                    $upload->process('uploads/imagens/thumbs/');
                    $upload->clean();
                } else {
                    $this->mensagem->alerta($upload->error)->flash();
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Deleta posts por ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $post = (new PostModelo())->buscaPorId($id);
            if (!$post) {
                $this->mensagem->alerta('O post que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/posts/listar');
            } else {
                if ($post->deletar()) {

                    if ($post->capa && file_exists("uploads/imagens/{$post->capa}")) {
                        unlink("uploads/imagens/{$post->capa}");
                        unlink("uploads/imagens/thumbs/{$post->capa}");
                    }

                    $this->mensagem->sucesso('Post deletado com sucesso!')->flash();
                    Helpers::redirecionar('admin/posts/listar');
                } else {
                    $this->mensagem->erro($post->erro())->flash();
                    Helpers::redirecionar('admin/posts/listar');
                }
            }
        }
    }

}
