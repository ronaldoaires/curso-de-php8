<?php

use sistema\Nucleo\Helpers;

//Arquivo de configuração do sistema
//define o fuso horario
date_default_timezone_set('America/Sao_Paulo');

//informações do sistema
define('SITE_NOME', 'UnSet');
define('SITE_DESCRICAO', 'UnSet - Tecnologia em Sistemas');

//urls do sistema
define('URL_PRODUCAO', 'https://unset.com.br');
define('URL_DESENVOLVIMENTO', 'http://localhost/blog');

if (Helpers::localhost()) {
    //dados de acesso ao banco de dados em localhost
    define('DB_HOST', 'localhost');
    define('DB_PORTA', '3306');
    define('DB_NOME', 'blog');
    define('DB_USUARIO', 'root');
    define('DB_SENHA', '');

    define('URL_SITE', 'blog/');
    define('URL_ADMIN', 'blog/admin/');
} else {
    //dados de acesso ao banco de dados na hospedagem
    define('DB_HOST', 'localhost');
    define('DB_PORTA', '3306');
    define('DB_NOME', '');
    define('DB_USUARIO', '');
    define('DB_SENHA', '');

    define('URL_SITE', '/');
    define('URL_ADMIN', '/admin/');
}

//autenticação do servidor de emails
define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_PORTA', '465');
define('EMAIL_USUARIO', '');
define('EMAIL_SENHA', '');
define('EMAIL_REMETENTE', ['email' => EMAIL_USUARIO, 'nome' => SITE_NOME]);
