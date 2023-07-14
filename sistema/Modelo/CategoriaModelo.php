<?php

namespace sistema\Modelo;

use sistema\Nucleo\Conexao;
use sistema\Nucleo\Modelo;

/**
 * Classe CategoriaModelo
 *
 * @author Ronaldo Aires
 */
class CategoriaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('categorias');
    }

    /**
     * Retorna o total de posts de uma categoria
     * @param int $categoriaId
     * @return int
     */
    public function totalPosts(int $categoriaId): int
    {
        $query = "SELECT COUNT(*) as total FROM posts WHERE categoria_id = {$categoriaId} ";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $resultado['total'];
    }

    /**
     * Salva o post com slug
     * @return bool
     */
    public function salvar(): bool
    {
        $this->slug();
        return parent::salvar();
    }

}
