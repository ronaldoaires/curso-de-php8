<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Modelo\PostModelo;
use sistema\Nucleo\Helpers;
use sistema\Modelo\CategoriaModelo;
use sistema\Biblioteca\Paginar;
use sistema\Suporte\Email;

class SiteControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    /**
     * Home Page
     * @return void
     */
    public function index(): void
    {
        $posts = (new PostModelo())->busca("status = 1");

        echo $this->template->renderizar('index.html', [
            'posts' => [
                'slides' => $posts->ordem('id DESC')->limite(3)->resultado(true),
                'posts' => $posts->ordem('id DESC')->limite(4)->offset(3)->resultado(true),
                'maisLidos' => (new PostModelo())->busca("status = 1")->ordem('visitas DESC')->limite(5)->resultado(true),
            ],
            'categorias' => $this->categorias(),
        ]);
    }

    /**
     * Busca posts 
     * @return void
     */
    public function buscar(): void
    {
        $busca = filter_input(INPUT_POST, 'busca', FILTER_DEFAULT);
        if (isset($busca)) {
            $posts = (new PostModelo())->busca("status = 1 AND titulo LIKE '%{$busca}%'")->resultado(true);
            if ($posts) {
                foreach ($posts as $post) {
                    echo "<li class='list-group-item fw-bold'><a href=" . Helpers::url('post/') . $post->categoria()->slug . '/' . $post->slug . ">$post->titulo</a></li>";
                }
            }
        }
    }

    /**
     * Busca post por ID
     * @param string $categoria apenas para o slug da categoria
     * @param string $slug
     * @return void
     */
    public function post(string $categoria, string $slug): void
    {
        $post = (new PostModelo())->buscaPorSlug($slug);
        if (!$post) {
            Helpers::redirecionar('404');
        }
        $post->salvarVisitas();

        echo $this->template->renderizar('post.html', [
            'post' => $post,
            'categorias' => $this->categorias(),
        ]);
    }

    /**
     * Categorias
     * @return array|null
     */
    public function categorias(): ?array
    {
        return (new CategoriaModelo())->busca("status = 1")->resultado(true);
    }

    /**
     * Lista posts por categoria
     * @param string $slug
     * @return void
     */
    public function categoria(string $slug, int $pagina = null): void
    {
        $categoria = (new CategoriaModelo())->buscaPorSlug($slug);
        if (!$categoria) {
            Helpers::redirecionar('404');
        }
        $categoria->salvarVisitas();

        $posts = (new PostModelo());
        $total = $posts->busca('categoria_id = :c AND status = :s', "c={$categoria->id}&s=1 COUNT(id)", 'id')->total();

        $paginar = new Paginar(Helpers::url('categoria/' . $slug), ($pagina ?? 1), 10, 3, $total);

        echo $this->template->renderizar('categoria.html', [
            'posts' => $posts->busca("categoria_id = {$categoria->id} AND status = 1")->limite($paginar->limite())->offset($paginar->offset())->resultado(true),
            'paginacao' => $paginar->renderizar(),
            'paginacaoInfo' => $paginar->info(),
            'categorias' => $this->categorias(),
        ]);
    }

    /**
     * Sobre
     * @return void
     */
    public function sobre(): void
    {
        echo $this->template->renderizar('sobre.html', [
            'titulo' => 'Sobre nós',
            'categorias' => $this->categorias(),
        ]);
    }

    /**
     * ERRO 404
     * @return void
     */
    public function erro404(): void
    {
        echo $this->template->renderizar('404.html', [
            'titulo' => 'Página não encontrada',
            'categorias' => $this->categorias(),
        ]);
    }

    /**
     * Contato
     * @return void
     */
    public function contato(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        if (isset($dados)) {
            if (in_array('', $dados)) {
                Helpers::json('erro', 'Preencha todos os campos!');
            } elseif (!Helpers::validarEmail($dados['email'])) {
                Helpers::json('erro', 'E-mail inválido!');
            } else {
                try {
                    $email = new Email();

                    $view = $this->template->renderizar('emails/contato.html', [
                        'dados' => $dados,
                    ]);

                    $email->criar(
                            'Contato via Site - ' . SITE_NOME,
                            $view,
                            EMAIL_REMETENTE['email'],
                            EMAIL_REMETENTE['nome'],
                            $dados['email'],
                            $dados['nome']
                    );

                    $anexos = $_FILES['anexos'];

                    foreach ($anexos['tmp_name'] as $indice => $anexo) {
                        if (!$anexo == UPLOAD_ERR_OK) {
                            $email->anexar($anexo, $anexos['name'][$indice]);
                        }
                    }
                    $email->enviar(EMAIL_REMETENTE['email'], EMAIL_REMETENTE['nome']);

//                    $this->mensagem->sucesso('E-mail enviado com sucesso!')->flash();
//                    Helpers::json('redirecionar', Helpers::url());
                    
                    Helpers::json('successo', 'E-mail enviado com sucesso!');
                    
                } catch (\PHPMailer\PHPMailer\Exception $ex) {
                    $this->mensagem->alerta($ex->getMessage())->flash();
                }
            }
        }

        echo $this->template->renderizar('contato.html', [
            'categorias' => $this->categorias(),
        ]);
    }

}
