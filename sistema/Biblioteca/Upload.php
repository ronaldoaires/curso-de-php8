<?php

namespace sistema\Biblioteca;

/**
 * Classe Upload
 *
 * @author Ronaldo Aires
 */
class Upload
{

    private ?string $diretorio;
    private ?array $arquivo;
    private ?string $nome;
    private ?string $subDiretorio;
    private ?int $tamanho;
    private ?string $resultado = null;
    private ?string $erro;

    /**
     * Retorna o resultado, 
     * @return string|null = Nome do arquivo
     */
    public function getResultado(): ?string
    {
        return $this->resultado;
    }

    /**
     * Retorna os erros
     * @return string|null
     */
    public function getErro(): ?string
    {
        return $this->erro;
    }

    /**
     * Verifica e cria o diretório padrão de uploads! Opcionalmente defina um diretório para envio dos arquivos
     * @param string $diretorio
     */
    public function __construct(string $diretorio = null)
    {
        $this->diretorio = $diretorio ?? 'uploads';

        if (!file_exists($this->diretorio) && !is_dir($this->diretorio)) {
            mkdir($this->diretorio, 0755);
        }
    }

    /**
     * Realiza a validação e o envio dos arquivos
     * @param array $arquivo
     * @param string $nome
     * @param string $subDiretorio
     * @param int $tamanho
     */
    public function arquivo(array $arquivo, string $nome = null, string $subDiretorio = null, int $tamanho = null)
    {
        $this->arquivo = $arquivo;
        $this->nome = $nome ?? pathinfo($this->arquivo['name'], PATHINFO_FILENAME);
        $this->subDiretorio = $subDiretorio ?? 'arquivos';
        $extensao = pathinfo($this->arquivo['name'], PATHINFO_EXTENSION);
        $this->tamanho = $tamanho ?? 1;

        $extensoesValidas = [
            'pdf',
            'png',
            'docx',
            'jpg',
            'gif',
            'txt'
        ];

        $tiposValidos = [
            'application/pdf',
            'text/plain',
            'image/png',
            'image/x-png',
            'image/gif',
            'image/jpeg',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($extensao, $extensoesValidas)) {
            $this->erro = 'Erro: A extensão do arquivo que você está tentando enviar não é permitida! Extensões permitidas .' . implode(' .', $extensoesValidas);
        } elseif (!in_array($this->arquivo['type'], $tiposValidos)) {
            $this->erro = 'Erro: O tipo de arquivo que você está tentando enviar não é permitido!';
        } elseif ($this->arquivo['size'] > $this->tamanho * (1024 * 1024)) {
            $this->erro = "Erro: O arquivo que você está tentando enviar é muito grande. O tamanho máximo é {$this->tamanho}MB";
        } else {
            $this->criarSubDiretorio();
            $this->renomarArquivo();
            $this->moverArquivo();
        }
    }

    /**
     * Cria sub diretório dentro do diretório padrão.
     * @return void
     */
    private function criarSubDiretorio(): void
    {
        if (!file_exists($this->diretorio . DIRECTORY_SEPARATOR . $this->subDiretorio) && !is_dir($this->diretorio . DIRECTORY_SEPARATOR . $this->subDiretorio)) {
            mkdir($this->diretorio . DIRECTORY_SEPARATOR . $this->subDiretorio, 0755);
        }
    }

    /**
     * Renomeia o arquivo. Se o arquivo existir concatena com um id único, se não renomeia para o novo nome informado.
     * @return void
     */
    private function renomarArquivo(): void
    {
        $arquivo = $this->nome . strrchr($this->arquivo['name'], '.');
        if (file_exists($this->diretorio . DIRECTORY_SEPARATOR . $this->subDiretorio . DIRECTORY_SEPARATOR . $arquivo)) {
            $arquivo = $this->nome . '-' . uniqid() . strrchr($this->arquivo['name'], '.');
        }
        $this->nome = $arquivo;
    }

    /**
     * move os arquivos para o diretório e armazena o nome do arquivo no resultado.
     * @return void
     */
    private function moverArquivo(): void
    {
        if (move_uploaded_file($this->arquivo['tmp_name'], $this->diretorio . DIRECTORY_SEPARATOR . $this->subDiretorio . DIRECTORY_SEPARATOR . $this->nome)) {
            $this->resultado = $this->nome;
        } else {
            $this->resultado = null;
            $this->erro = 'Erro ao enviar arquivo';
        }
    }

}
