<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\Contract;
use App\Repositories\ContractInstallmentRepository;
use App\Repositories\ContractRepository;
use App\Repositories\PatientRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\ContractDocxService;
use App\Services\InfinitePayService;

class ContractController
{
    private ContractRepository $repo;
    private ContractInstallmentRepository $installmentRepo;
    private PatientRepository $patientRepo;
    private ServiceTypeRepository $serviceTypeRepo;
    private InfinitePayService $infinitePay;
    private ContractDocxService $docxService;

    public function __construct()
    {
        $db  = new Database();
        $pdo = $db->getConnection();

        $this->repo            = new ContractRepository($pdo);
        $this->installmentRepo = new ContractInstallmentRepository($pdo);
        $this->patientRepo     = new PatientRepository($pdo);
        $this->serviceTypeRepo = new ServiceTypeRepository($pdo);
        $this->infinitePay     = new InfinitePayService();
        $this->docxService     = new ContractDocxService();
    }

    public function index(): void
    {
        $this->view('contracts/list', ['contracts' => $this->repo->all()]);
    }

    public function create(): void
    {
        $patients     = $this->patientRepo->all();
        $serviceTypes = $this->serviceTypeRepo->allActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->readFormData();
            $errors = $this->validate($data);

            if (!empty($errors)) {
                $this->view('contracts/create', [
                    'errors'       => $errors,
                    'old'          => $data,
                    'patients'     => $patients,
                    'serviceTypes' => $serviceTypes,
                ]);
                return;
            }

            $contract = new Contract(
                id: null,
                patientId: $data['patient_id'],
                serviceTypeId: $data['service_type_id'],
                totalValue: $data['total_value'],
                installments: $data['installments'],
                paymentCondition: $data['payment_condition'],
                startDate: $data['start_date'],
                status: 'ATIVO',
                notes: $data['notes'] ?: null
            );

            $id = $this->repo->create($contract);
            $contract->id = $id;
            $this->installmentRepo->ensureGenerated($contract);

            header('Location: ' . BASE_URL . '/?controller=contract&action=print&id=' . $id);
            exit;
        }

        $this->view('contracts/create', ['patients' => $patients, 'serviceTypes' => $serviceTypes]);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $this->repo->find($id);
        if (!$contract) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=notfound');
            exit;
        }

        $patients     = $this->patientRepo->all();
        $serviceTypes = $this->serviceTypeRepo->allActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->readFormData();
            $data['status'] = $_POST['status'] ?? $contract->status;
            $errors = $this->validate($data);

            if (!empty($errors)) {
                $this->view('contracts/edit', [
                    'errors'       => $errors,
                    'contract'     => $contract,
                    'old'          => $data,
                    'patients'     => $patients,
                    'serviceTypes' => $serviceTypes,
                ]);
                return;
            }

            $mudouParcelamento = (
                $contract->totalValue != $data['total_value'] ||
                $contract->installments != $data['installments'] ||
                $contract->startDate != $data['start_date']
            );

            $contract->patientId        = $data['patient_id'];
            $contract->serviceTypeId    = $data['service_type_id'];
            $contract->totalValue       = $data['total_value'];
            $contract->installments     = $data['installments'];
            $contract->paymentCondition = $data['payment_condition'];
            $contract->startDate        = $data['start_date'];
            $contract->status           = $data['status'];
            $contract->notes            = $data['notes'] ?: null;

            $this->repo->update($contract);

            $msg = 'updated';
            if ($mudouParcelamento) {
                if ($this->installmentRepo->hasPaidInstallments((int)$contract->id)) {
                    // Já existe parcela paga: não mexe no parcelamento pra não perder o histórico
                    $msg = 'updated_kept_installments';
                } else {
                    $this->installmentRepo->regenerate($contract);
                }
            }

            header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=' . $msg);
            exit;
        }

        $this->view('contracts/edit', ['contract' => $contract, 'patients' => $patients, 'serviceTypes' => $serviceTypes]);
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->repo->delete($id);
        }
        header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=deleted');
        exit;
    }

    /**
     * Tela de impressão: puxa os dados do paciente/serviço/parcelas já
     * cadastrados e monta o contrato pronto para imprimir (Ctrl+P / botão).
     */
    public function print(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $this->repo->find($id);
        if (!$contract) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=notfound');
            exit;
        }

        $this->installmentRepo->ensureGenerated($contract);
        $installments = $this->installmentRepo->findByContractId($id);

        $this->view('contracts/print', ['contract' => $contract, 'installments' => $installments]);
    }

    /**
     * Gera o contrato preenchido em formato Word (.docx), a partir do template
     * baseado no modelo original da clínica, e envia para download.
     */
    public function downloadDocx(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $this->repo->find($id);
        if (!$contract) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=notfound');
            exit;
        }

        $this->installmentRepo->ensureGenerated($contract);
        $installmentsRaw = $this->installmentRepo->findByContractId($id);

        $mesesPt = [1=>'janeiro',2=>'fevereiro',3=>'março',4=>'abril',5=>'maio',6=>'junho',
                    7=>'julho',8=>'agosto',9=>'setembro',10=>'outubro',11=>'novembro',12=>'dezembro'];
        $dataExtenso = (int)date('d') . ' de ' . $mesesPt[(int)date('n')] . ' de ' . date('Y');

        $fields = [
            'nome_paciente'   => $contract->patientName ?? '',
            'nacionalidade'   => $contract->patientNationality ?: '[nacionalidade não informada]',
            'estado_civil'    => $contract->patientMaritalStatus ?: '[estado civil não informado]',
            'profissao'       => $contract->patientOccupation ?: '[profissão não informada]',
            'rg'              => $contract->patientRg ?: '[RG não informado]',
            'cpf'             => $contract->patientCpf ?: '[CPF não informado]',
            'endereco'        => $contract->patientAddress ?: '[endereço não informado]',
            'cep'             => $contract->patientCep ?: '[CEP não informado]',
            'tratamento'      => mb_strtoupper($contract->serviceTypeName ?? ''),
            'valor_total'     => number_format($contract->totalValue, 2, ',', '.'),
            'forma_pagamento' => Contract::paymentConditionLabel($contract->paymentCondition),
            'data_emissao'    => $dataExtenso,
        ];

        $n = count($installmentsRaw);
        $installments = [];
        foreach ($installmentsRaw as $inst) {
            $installments[] = [
                'numero'     => $inst->installmentNumber . '/' . $n,
                'vencimento' => date('d/m/Y', strtotime($inst->dueDate)),
                'valor'      => 'R$ ' . number_format($inst->amount, 2, ',', '.'),
            ];
        }

        $path = $this->docxService->generate($fields, $installments);

        if (!$path) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=print&id=' . $id . '&docxError=1');
            exit;
        }

        $safeName = preg_replace('/[^A-Za-z0-9]+/', '_', $contract->patientName ?? 'paciente');
        $filename = 'Contrato_' . trim((string)$safeName, '_') . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');

        readfile($path);
        unlink($path);
        exit;
    }

    /**
     * Tela de acompanhamento de pagamento das parcelas de um contrato.
     */
    public function installments(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $this->repo->find($id);
        if (!$contract) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=notfound');
            exit;
        }

        $this->installmentRepo->ensureGenerated($contract);
        $summary = $this->installmentRepo->summaryByContractId($id);

        $this->view('contracts/installments', [
            'contract' => $contract,
            'summary'  => $summary,
        ]);
    }

    /**
     * Marca uma parcela como paga (registro manual de pagamento).
     */
    public function markInstallmentPaid(): void
    {
        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $contractId    = (int)($_POST['contract_id'] ?? 0);
        $installment   = $this->installmentRepo->find($installmentId);

        if ($installment && $installment->contractId === $contractId) {
            $paidAt     = trim($_POST['paid_at'] ?? '') ?: date('Y-m-d');
            $paidAmount = trim(str_replace(',', '.', $_POST['paid_amount'] ?? ''));
            $paidAmount = is_numeric($paidAmount) && (float)$paidAmount > 0 ? (float)$paidAmount : $installment->amount;
            $method     = trim($_POST['payment_method'] ?? '') ?: null;
            $notes      = trim($_POST['notes'] ?? '') ?: null;

            $this->installmentRepo->markAsPaid($installmentId, $paidAt . ' ' . date('H:i:s'), $paidAmount, $method, $notes);
        }

        header('Location: ' . BASE_URL . '/?controller=contract&action=installments&id=' . $contractId);
        exit;
    }

    /**
     * Desfaz a marcação de pagamento (volta a parcela para pendente).
     */
    public function unmarkInstallmentPaid(): void
    {
        $installmentId = (int)($_GET['installment_id'] ?? 0);
        $contractId    = (int)($_GET['contract_id'] ?? 0);
        $installment   = $this->installmentRepo->find($installmentId);

        if ($installment && $installment->contractId === $contractId) {
            $this->installmentRepo->markAsPending($installmentId);
        }

        header('Location: ' . BASE_URL . '/?controller=contract&action=installments&id=' . $contractId);
        exit;
    }

    /**
     * Gera um link de cobrança (Pix ou Cartão) via InfinitePay para uma parcela específica.
     */
    public function generateCharge(): void
    {
        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $contractId    = (int)($_POST['contract_id'] ?? 0);

        $installment = $this->installmentRepo->find($installmentId);
        $contract    = $this->repo->find($contractId);

        if (!$installment || !$contract || $installment->contractId !== $contractId) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=index&msg=notfound');
            exit;
        }

        if (!in_array($contract->paymentCondition, ['PIX', 'CARTAO'], true)) {
            header('Location: ' . BASE_URL . '/?controller=contract&action=installments&id=' . $contractId . '&chargeError=condicao_invalida');
            exit;
        }

        $patient = $this->patientRepo->find($contract->patientId);

        // order_nsu estável: se gerar de novo, sempre aponta pra mesma parcela
        $orderNsu = 'NB-' . $contract->id . '-' . $installment->id;

        $result = $this->infinitePay->createPaymentLink(
            orderNsu: $orderNsu,
            description: 'Contrato #' . $contract->id . ' - Parcela ' . $installment->installmentNumber . '/' . $contract->installments . ' - ' . ($contract->serviceTypeName ?? ''),
            amount: $installment->amount,
            redirectUrl: app_absolute_url('/?controller=contract&action=installments&id=' . $contractId),
            webhookUrl: app_absolute_url('/?controller=contract&action=infinitepayWebhook'),
            customer: [
                'name'         => $patient->fullName ?? null,
                'email'        => $patient->email ?: null,
                'phone_number' => $patient->cellphone ?: null,
            ]
        );

        if ($result['success']) {
            $this->installmentRepo->saveInfinitePayCharge($installmentId, $orderNsu, $result['url']);
            header('Location: ' . BASE_URL . '/?controller=contract&action=installments&id=' . $contractId . '&chargeOk=1');
        } else {
            header('Location: ' . BASE_URL . '/?controller=contract&action=installments&id=' . $contractId . '&chargeError=' . urlencode($result['error'] ?? 'erro desconhecido'));
        }
        exit;
    }

    /**
     * Endpoint público chamado pela InfinitePay quando um pagamento é confirmado.
     * Precisa responder rápido (idealmente < 1s) com 200 OK.
     */
    public function infinitepayWebhook(): void
    {
        header('Content-Type: application/json');

        try {
            $raw  = file_get_contents('php://input');
            $body = json_decode((string)$raw, true);

            $orderNsu = $body['order_nsu'] ?? null;

            if (!$orderNsu) {
                http_response_code(400);
                echo json_encode(['error' => 'order_nsu ausente']);
                return;
            }

            $installment = $this->installmentRepo->findByInfinitePayOrderNsu($orderNsu);

            if (!$installment) {
                // order_nsu não corresponde a nenhuma parcela nossa: rejeita
                http_response_code(400);
                echo json_encode(['error' => 'order_nsu não reconhecido']);
                return;
            }

            // Idempotência: se já estava paga, só confirma 200 sem processar de novo
            if ($installment->status !== 'PAGO') {
                $paidAmountCents = $body['paid_amount'] ?? $body['amount'] ?? 0;
                $paidAmount      = ((float)$paidAmountCents) / 100;
                $captureMethod   = $body['capture_method'] ?? 'pix';
                $transactionNsu  = $body['transaction_nsu'] ?? null;

                $this->installmentRepo->confirmInfinitePayPayment(
                    (int)$installment->id,
                    $paidAmount,
                    $captureMethod,
                    $transactionNsu
                );
            }

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            // Nunca deixa vazar erro 500 aqui — responde 400 para a InfinitePay tentar de novo
            http_response_code(400);
            echo json_encode(['error' => 'erro ao processar webhook']);
        }
    }

    private function readFormData(): array
    {
        return [
            'patient_id'        => (int)($_POST['patient_id'] ?? 0),
            'service_type_id'   => (int)($_POST['service_type_id'] ?? 0),
            'total_value'       => trim(str_replace(',', '.', $_POST['total_value'] ?? '')),
            'installments'      => (int)($_POST['installments'] ?? 1),
            'payment_condition' => $_POST['payment_condition'] ?? 'PIX',
            'start_date'        => trim($_POST['start_date'] ?? ''),
            'notes'             => trim($_POST['notes'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (($data['patient_id'] ?? 0) <= 0) {
            $errors[] = 'Selecione o paciente.';
        }

        if (($data['service_type_id'] ?? 0) <= 0) {
            $errors[] = 'Selecione o tipo de serviço/pacote.';
        }

        if ($data['total_value'] === '' || !is_numeric($data['total_value']) || (float)$data['total_value'] <= 0) {
            $errors[] = 'Informe um valor total válido para o contrato.';
        }

        if (($data['installments'] ?? 0) < 1 || $data['installments'] > 60) {
            $errors[] = 'Número de parcelas deve ser entre 1 e 60.';
        }

        if (!in_array($data['payment_condition'] ?? '', ['PIX', 'CARTAO', 'BOLETO'], true)) {
            $errors[] = 'Selecione uma condição de pagamento válida.';
        }

        if ($data['start_date'] === '') {
            $errors[] = 'Informe a data de início do contrato.';
        }

        return $errors;
    }

    private function view(string $path, array $data = []): void
    {
        // total_value precisa virar float antes de instanciar o Contract
        if (isset($data['old']['total_value']) && is_numeric($data['old']['total_value'])) {
            $data['old']['total_value'] = (float)$data['old']['total_value'];
        }
        extract($data);
        $base = dirname(__DIR__, 2);
        include $base . "/views/{$path}.php";
    }
}
