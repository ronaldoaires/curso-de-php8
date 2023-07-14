<?php

namespace sistema\Nucleo;

use PDO;
use PDOException;

/**
 * Classe Conexao - Padrão Singleton: Retorna uma instância única de uma classe.
 *
 * @author Ronaldo Aires
 */
class Conexao
{

    private static $instancia;

    public static function getInstancia(): PDO
    {
        if (empty(self::$instancia)) {
            try {
                self::$instancia = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORTA . ';dbname=' . DB_NOME, DB_USUARIO, DB_SENHA, [
                    //garante que o charset do PDO seja o mesmo do banco de dados
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    //todo erro através da PDO será uma exceção
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    //converter qualquer resultado como um objeto anônimo
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    //garante que o mesmo nome das colunas do banco seja utilizado
                    PDO::ATTR_CASE => PDO::CASE_NATURAL
                ]);
            } catch (PDOException $ex) {
                die("Erro de conexão:: " . $ex->getMessage());
            }            
        }
        return self::$instancia;
    }

    /**
     * Construtor do tipo protegido previne que uma nova instância da
     * Classe seja criada através do operador `new` de fora dessa classe.
     */
    protected function __construct()
    {
        
    }

    /**
     * Método clone do tipo privado previne a clonagem dessa instância da classe
     * @return void
     */
    private function __clone(): void
    {
        
    }

}
