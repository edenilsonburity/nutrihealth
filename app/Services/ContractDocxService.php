<?php
namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Throwable;

/**
 * Preenche o template Word do contrato (templates/contract_template.docx)
 * com os dados de um contrato específico, usando a biblioteca PHPWord.
 *
 * O template usa placeholders no formato ${nome_do_campo} (padrão do
 * PHPWord TemplateProcessor) — foi gerado a partir do modelo .docx
 * original da clínica, preservando toda a formatação/cláusulas.
 */
class ContractDocxService
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = dirname(__DIR__, 2) . '/templates/contract_template.docx';
    }

    public function isAvailable(): bool
    {
        return file_exists($this->templatePath) && class_exists(TemplateProcessor::class);
    }

    /**
     * Gera o .docx preenchido e retorna o caminho do arquivo temporário criado
     * (o chamador é responsável por apagar o arquivo depois de enviar pro navegador),
     * ou null se a geração falhar por qualquer motivo.
     *
     * @param array<string,string> $fields        Valores dos campos simples do contrato
     * @param array<int,array{numero:string,vencimento:string,valor:string}> $installments
     */
    public function generate(array $fields, array $installments): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }

        try {
            $tp = new TemplateProcessor($this->templatePath);

            foreach ($fields as $key => $value) {
                $tp->setValue($key, $value);
            }

            $count = max(1, count($installments));
            $tp->cloneRow('numero', $count);

            foreach (array_values($installments) as $i => $inst) {
                $n = $i + 1;
                $tp->setValue('numero#' . $n, $inst['numero']);
                $tp->setValue('vencimento#' . $n, $inst['vencimento']);
                $tp->setValue('valor#' . $n, $inst['valor']);
            }

            $outputPath = sys_get_temp_dir() . '/contrato_' . uniqid('', true) . '.docx';
            $tp->saveAs($outputPath);

            return $outputPath;
        } catch (Throwable $e) {
            return null;
        }
    }
}
