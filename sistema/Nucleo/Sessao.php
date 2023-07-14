<?php

namespace sistema\Nucleo;

/**
 * Classe Sessao
 *
 * @author Ronaldo Aires
 */
class Sessao
{

    public function __construct()
    {
        //checa se não existe um ID de sessão
        if (!session_id()) {
            //inicia uma nova sessão ou resume uma sessão existente
            session_start();
        }
    }

    /**
     * Cria uma sessão
     * @param string $chave
     * @param mixed $valor
     * @return Sessao
     */
    public function criar(string $chave, mixed $valor): Sessao
    {
        $_SESSION[$chave] = (is_array($valor) ? (object) $valor : $valor);
        return $this;
    }

    /**
     * Carrega uma sessão
     * @return object|null
     */
    public function carregar(): ?object
    {
        return (object) $_SESSION;
    }

    /**
     * Checa se uma sessão existe
     * @param string $chave
     * @return bool
     */
    public function checar(string $chave): bool
    {
        return isset($_SESSION[$chave]);
    }

    /**
     * Limpa a sessão especificada
     * @param string $chave
     * @return Sessao
     */
    public function limpar(string $chave): Sessao
    {
        unset($_SESSION[$chave]);
        return $this;
    }

    /**
     * Destrói todos os dados registrados em uma sessão
     * @return Sessao
     */
    public function deletar(): Sessao
    {
        session_destroy();
        return $this;
    }

    /**
     * __get() é utilizado para ler dados de atributos inacessíveis.
     * @param type $atributo
     * @return type
     */
    public function __get($atributo)
    {
        if (!empty($_SESSION[$atributo])) {
            return $_SESSION[$atributo];
        }
    }

    /**
     * Checa ou limpa mensagens flash
     * @return Mensagem|null
     */
    public function flash(): ?Mensagem
    {
        if ($this->checar('flash')) {
            $flash = $this->flash;
            $this->limpar('flash');
            return $flash;
        }
        return null;
    }

}
