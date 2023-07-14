<?php

namespace sistema\Biblioteca;

/**
 * Classe Paginar - esta classe fornece uma implementação básica de funcionalidade de paginação que pode ser facilmente integrada em outros sistemas.
 *
 * @author Ronaldo Aires
 */
class Paginar
{

    private string $url;
    private int $limite;
    private int $offset;
    private int $pagina;
    private int $totalPaginas;
    private int $arredor;
    private int $totalRegistros;

    /**
     * Construtor com os valores iniciais 
     * @param string $url
     * @param int $pagina
     * @param int $limite
     * @param int $arredor
     * @param int $total
     */
    public function __construct(
            string $url,
            int $pagina = 1,
            int $limite = 10,
            int $arredor = 3,
            int $total = 0
    )
    {
        $this->url = $url;
        $this->pagina = $pagina;
        $this->limite = $limite;
        $this->offset = ($this->pagina - 1) * $this->limite;
        $this->totalPaginas = ceil($total / $this->limite);
        $this->arredor = $arredor;
        $this->totalRegistros = $total;
    }

    /**
     * Retorna o limite de itens por página
     * @return int
     */
    public function limite(): int
    {
        return $this->limite;
    }

    /**
     * Retorna o índice do item de início da página atual
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * Gera a renderização da paginação
     * @return string|null
     */
    public function renderizar(): ?string
    {
        if ($this->limite < $this->totalRegistros) {
            $paginacao = '<ul class="pagination justify-content-center">';
            $paginacao .= '<li class="page-item">' . $this->primeira() . '</li>';
            $paginacao .= '<li class="page-item">' . $this->anterior() . '</li>';
            $paginacao .= '<li class="page-item">' . $this->barraPaginacao() . '</li>';
            $paginacao .= '<li class="page-item">' . $this->proxima() . '</li>';
            $paginacao .= '<li class="page-item">' . $this->ultima() . '</li>';
            $paginacao .= '</ul>';

            return $paginacao;
        }
        return null;
    }

    /**
     * Gera link para a primeira página
     * @return string|null
     */
    private function primeira(): ?string
    {
        if ($this->pagina > 2) {
            return ' <a class="page-link" href=" ' . $this->url . '/1 " tooltip="tooltip" title="Primeira Página"><i class="fa-solid fa-angles-left"></i></a>';
        }
        return null;
    }

    /**
     * Gera link para a página anterior
     * @return string|null
     */
    private function anterior(): ?string
    {
        if ($this->pagina > 1) {
            return ' <a class="page-link" href=" ' . $this->url . '/' . ($this->pagina - 1) . ' " tooltip="tooltip" title="Página Anterior"><i class="fa-solid fa-angle-left"></i></a>';
        } elseif ($this->pagina < 2) {
            return ' <a class="page-link disabled" href=" ' . $this->url . '/' . ($this->pagina - 1) . ' "><i class="fa-solid fa-angle-left"></i></a>';
        }
        return null;
    }

    /**
     * Gera links de paginação para as páginas intermediárias com o valor de arredondamento para determinar quantas páginas devem ser exibidas ao redor da página atual 
     * @return string|null
     */
    private function barraPaginacao(): ?string
    {
        $inicio = max(1, $this->pagina - $this->arredor);
        $fim = min($this->totalPaginas, $this->pagina + $this->arredor);

        $navegacao = null;

        for ($i = $inicio; $i <= $fim; $i++) {
            if ($i == $this->pagina) {
                $navegacao .= '<span class="page-link active">' . $i . '</span>';
            } else {
                $navegacao .= '<li class="page-item fw-bold"><a class="page-link" href=" ' . $this->url . '/' . $i . ' " tooltip="tooltip" title="Página ' . $i . ' ">' . $i . '</a></li>';
            }
        }
        return $navegacao;
    }

    /**
     * Gera link para a próxima página
     * @return string|null
     */
    private function proxima(): ?string
    {
        if ($this->pagina < $this->totalPaginas) {
            return ' <a class="page-link" href=" ' . $this->url . '/' . ($this->pagina + 1) . ' " " tooltip="tooltip" title="Próxima Página"><i class="fa-solid fa-angle-right"></i></a>';
        }
        return null;
    }

    /**
     * Gera link para a última página
     * @return string|null
     */
    private function ultima(): ?string
    {
        if ($this->pagina < $this->totalPaginas) {
            return ' <a class="page-link" href=" ' . $this->url . '/' . $this->totalPaginas . ' " tooltip="tooltip" title="Última Página"><i class="fa-solid fa-angles-right"></i></a>';
        }
        return null;
    }

    /**
     * Retorna o total inicial e final da página atual e o total de registros 
     * @return string
     */
    public function info(): string
    {
        $totalInicial = $this->offset + 1;
        $totalFinal = min($this->totalRegistros, $this->pagina * $this->limite);
        $totalRegistros = number_format($this->totalRegistros, 0, '.', '.');

        return "Mostrando de {$totalInicial} até {$totalFinal} de {$totalRegistros}";
    }

}
