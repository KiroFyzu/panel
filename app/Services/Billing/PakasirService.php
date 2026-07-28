<?php

namespace Pterodactyl\Services\Billing;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class PakasirService
{
    public const BASE_URL = 'https://app.pakasir.com/api';
    public const PAYMENT_METHOD_QRIS = 'qris';

    public function __construct(
        private Client $client,
        private SettingsRepositoryInterface $settings,
    ) {
    }

    /**
     * Create a QRIS transaction via Pakasir.
     *
     * @return array{payment_number: string, fee: int, total_payment: int, expired_at: string}
     *
     * @throws Exception
     */
    public function createQrisTransaction(string $orderId, int $amount): array
    {
        $project = (string) $this->settings->get('settings::billing:pakasir_project', env('PAKASIR_PROJECT', ''));
        $apiKey = (string) $this->settings->get('settings::billing:pakasir_api_key', env('PAKASIR_API_KEY', ''));

        if ($project === '' || $apiKey === '') {
            throw new Exception('Pakasir project or api_key is not configured. Set PAKASIR_PROJECT & PAKASIR_API_KEY in .env or via admin settings.');
        }

        try {
            $response = $this->client->post(self::BASE_URL . '/transactioncreate/' . self::PAYMENT_METHOD_QRIS, [
                'json' => [
                    'project' => $project,
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'api_key' => $apiKey,
                ],
                'timeout' => 30,
                'connect_timeout' => 5,
            ]);

            $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($body['payment'])) {
                throw new Exception('Invalid Pakasir response: missing payment payload.');
            }

            $payment = $body['payment'];

            return [
                'payment_number' => (string) ($payment['payment_number'] ?? ''),
                'fee' => (int) ($payment['fee'] ?? 0),
                'total_payment' => (int) ($payment['total_payment'] ?? ($payment['amount'] ?? $amount)),
                'expired_at' => (string) ($payment['expired_at'] ?? ''),
                'amount' => (int) ($payment['amount'] ?? $amount),
            ];
        } catch (RequestException $e) {
            throw new Exception('Pakasir request failed: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Check transaction status. Endpoint format:
     * https://app.pakasir.com/api/transactiondetail?project=...&order_id=...&amount=...&api_key=...
     */
    public function checkTransaction(string $orderId, int $amount): array
    {
        $project = (string) $this->settings->get('settings::billing:pakasir_project', '');
        $apiKey = (string) $this->settings->get('settings::billing:pakasir_api_key', '');

        $response = $this->client->get(self::BASE_URL . '/transactiondetail', [
            'query' => [
                'project' => $project,
                'order_id' => $orderId,
                'amount' => $amount,
                'api_key' => $apiKey,
            ],
            'timeout' => 15,
        ]);

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}