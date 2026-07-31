<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FinancialDataService
{
    private $apiKey;
    private $baseUrl;
    private $timeout;

    public function __construct()
    {
        $this->apiKey = 'INTERNAL_SECRET_KEY_2024_FINANCIAL_APP';
        $this->baseUrl = 'http://localhost:8001';
        $this->timeout = 10;
    }

    /**
     * Recupera i dati finanziari dal servizio interno
     */
    public function getFinancialData($endpoint = null)
    {
        $url = $endpoint ?: $this->baseUrl . '/user-data.php';
        
        try {
            $response = Http::withHeaders([
                'X-Internal-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'VulnBlog-FinancialService/1.0'
            ])->timeout($this->timeout)->get($url);

            if ($response->successful()) {
                Log::info('Dati finanziari recuperati', ['url' => $url]);
                return [
                    'status' => 'success',
                    'data' => $response->json(),
                    'status_code' => $response->status()
                ];
            }

            Log::warning('Errore recupero dati finanziari', ['url' => $url, 'status' => $response->status()]);
            return ['status' => 'error', 'error' => 'Servizio non disponibile'];

        } catch (Exception $e) {
            Log::error('Errore servizio finanziario', ['url' => $url, 'exception' => $e->getMessage()]);
            return ['status' => 'error', 'error' => 'Errore di connessione'];
        }
    }

    /**
     * VULNERABILE A SSRF - Metodo per chiamare URL arbitrari
     * Mantiene la vulnerabilità SSRF per scopi didattici
     */
    public function fetchExternalData($url)
    {
        if (empty($url)) {
            return ['status' => 'error', 'error' => 'URL parameter required'];
        }

        try {
            // VULNERABILITÀ SSRF: Nessuna validazione dell'URL
            $response = Http::withHeaders([
                'X-Internal-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'VulnBlog-FinancialService/1.0'
            ])->timeout($this->timeout)->get($url);

            Log::info('Chiamata esterna eseguita', ['url' => $url, 'status' => $response->status()]);

            return [
                'status' => 'success',
                'url' => $url,
                'status_code' => $response->status(),
                'data' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('Errore chiamata esterna', ['url' => $url, 'exception' => $e->getMessage()]);
            return ['status' => 'error', 'url' => $url, 'error' => $e->getMessage()];
        }
    }

    /**
     * Scarica i dati finanziari come file JSON
     */
    public function downloadFinancialData()
    {
        $result = $this->getFinancialData();
        
        if ($result['status'] === 'success') {
            return [
                'status' => 'success',
                'data' => $result['data'],
                'filename' => 'financial-data-' . date('Y-m-d-H-i-s') . '.json'
            ];
        }

        return $result;
    }

    /**
     * Verifica lo stato del servizio finanziario
     */
    public function checkServiceHealth()
    {
        try {
            $response = Http::withHeaders(['X-Internal-Api-Key' => $this->apiKey])
                ->timeout(5)
                ->get($this->baseUrl . '/health.php');

            return [
                'status' => 'success',
                'service_status' => $response->successful() ? 'healthy' : 'unhealthy'
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'service_status' => 'unreachable',
                'error' => $e->getMessage()
            ];
        }
    }
}
