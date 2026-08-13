<?php
namespace App\Services;

use App\Config\InfinitePayConfig;
use Throwable;

/**
 * Integração com o Checkout Integrado da InfinitePay (Pix e Cartão).
 * Documentação: https://ajuda.infinitepay.io/pt-BR/articles/10766888
 *
 * Não usa nenhuma biblioteca externa — só file_get_contents com stream
 * context, então não precisa de nada extra no Dockerfile/composer.
 */
class InfinitePayService
{
    private const LINKS_ENDPOINT = 'https://api.checkout.infinitepay.io/links';
    private const CHECK_ENDPOINT = 'https://api.checkout.infinitepay.io/payment_check';

    /**
     * Cria um link de cobrança (Pix ou Cartão).
     *
     * @param string $orderNsu     Identificador único do nosso lado (usado para reconciliar o webhook)
     * @param string $description  Descrição do que está sendo cobrado
     * @param float  $amount       Valor em reais (será convertido para centavos)
     * @param string $redirectUrl  Para onde o paciente volta depois de pagar
     * @param string $webhookUrl   URL que a InfinitePay vai chamar para avisar do pagamento
     * @param array{name?:string,email?:string,phone_number?:string} $customer
     *
     * @return array{success: bool, url: ?string, error: ?string}
     */
    public function createPaymentLink(
        string $orderNsu,
        string $description,
        float $amount,
        string $redirectUrl,
        string $webhookUrl,
        array $customer = []
    ): array {
        $handle = InfinitePayConfig::getHandle();
        if (!$handle) {
            return ['success' => false, 'url' => null, 'error' => 'Integração com InfinitePay não configurada (falta INFINITEPAY_HANDLE).'];
        }

        $payload = [
            'handle'      => $handle,
            'redirect_url' => $redirectUrl,
            'webhook_url'  => $webhookUrl,
            'order_nsu'    => $orderNsu,
            'items' => [
                [
                    'quantity'    => 1,
                    'price'       => (int)round($amount * 100), // a API espera o valor em centavos
                    'description' => mb_substr($description, 0, 200),
                ],
            ],
        ];

        if (!empty($customer)) {
            $payload['customer'] = array_filter([
                'name'         => $customer['name'] ?? null,
                'email'        => $customer['email'] ?? null,
                'phone_number' => $customer['phone_number'] ?? null,
            ]);
        }

        $result = $this->postJson(self::LINKS_ENDPOINT, $payload);

        if (!$result['success']) {
            return ['success' => false, 'url' => null, 'error' => $result['error']];
        }

        $url = $result['data']['url'] ?? null;
        if (!$url) {
            return ['success' => false, 'url' => null, 'error' => 'Resposta da InfinitePay não trouxe a URL do link.'];
        }

        return ['success' => true, 'url' => $url, 'error' => null];
    }

    /**
     * Consulta manualmente o status de um pagamento (fallback, caso o webhook
     * não tenha chegado por algum motivo).
     *
     * @return array{success: bool, paid: bool, paidAmount: ?float, captureMethod: ?string, error: ?string}
     */
    public function checkPayment(string $orderNsu): array
    {
        $handle = InfinitePayConfig::getHandle();
        if (!$handle) {
            return ['success' => false, 'paid' => false, 'paidAmount' => null, 'captureMethod' => null, 'error' => 'Integração não configurada.'];
        }

        $result = $this->postJson(self::CHECK_ENDPOINT, [
            'handle'    => $handle,
            'order_nsu' => $orderNsu,
        ]);

        if (!$result['success']) {
            return ['success' => false, 'paid' => false, 'paidAmount' => null, 'captureMethod' => null, 'error' => $result['error']];
        }

        $data = $result['data'];

        return [
            'success'       => true,
            'paid'          => (bool)($data['paid'] ?? false),
            'paidAmount'    => isset($data['paid_amount']) ? $data['paid_amount'] / 100 : null,
            'captureMethod' => $data['capture_method'] ?? null,
            'error'         => null,
        ];
    }

    /**
     * POST genérico com JSON, sem depender de cURL (usa stream context nativo do PHP).
     *
     * @return array{success: bool, data: array, error: ?string}
     */
    private function postJson(string $url, array $payload): array
    {
        try {
            $body = json_encode($payload);

            $context = stream_context_create([
                'http' => [
                    'method'        => 'POST',
                    'header'        => "Content-Type: application/json\r\nAccept: application/json\r\n",
                    'content'       => $body,
                    'timeout'       => 15,
                    'ignore_errors' => true, // permite ler o corpo mesmo em respostas de erro (4xx/5xx)
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return ['success' => false, 'data' => [], 'error' => 'Não foi possível conectar à InfinitePay.'];
            }

            // Verifica o código HTTP retornado (ex.: "HTTP/1.1 200 OK")
            $statusLine = $http_response_header[0] ?? '';
            preg_match('/\s(\d{3})\s/', $statusLine, $m);
            $statusCode = isset($m[1]) ? (int)$m[1] : 0;

            $data = json_decode($response, true) ?? [];

            if ($statusCode < 200 || $statusCode >= 300) {
                $msg = $data['message'] ?? $data['error'] ?? ('HTTP ' . $statusCode);
                return ['success' => false, 'data' => $data, 'error' => mb_substr((string)$msg, 0, 300)];
            }

            return ['success' => true, 'data' => $data, 'error' => null];
        } catch (Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => mb_substr($e->getMessage(), 0, 300)];
        }
    }
}
