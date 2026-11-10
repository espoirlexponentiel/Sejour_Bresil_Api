<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->cloudName = (string) config('services.cloudinary.cloud_name');
        $this->apiKey = (string) config('services.cloudinary.api_key');
        $this->apiSecret = (string) config('services.cloudinary.api_secret');
    }

    /**
     * Upload an image or PDF file to Cloudinary using signed upload.
     *
     * @return array{secure_url: string, public_id: string, resource_type: string, format: ?string, original_filename: string}
     */
    public function upload(UploadedFile $file, string $folder = 'sejour_bresil/assistance'): array
    {
        if (empty($this->cloudName) || empty($this->apiKey) || empty($this->apiSecret)) {
            throw new RuntimeException('Configuration Cloudinary manquante dans .env');
        }

        $timestamp = time();
        $extension = strtolower($file->getClientOriginalExtension());
        // Use 'raw' for PDFs/docs if desired, or 'auto'. Note: 'raw' guarantees unrestricted PDF download on all Cloudinary plans, while 'image' handles images with previews.
        $resourceType = $extension === 'pdf' ? 'raw' : 'auto';

        $paramsToSign = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        $signatureBase = collect($paramsToSign)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode('&') . $this->apiSecret;

        $signature = sha1($signatureBase);

        $endpoint = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/upload";

        $response = Http::timeout(45)
            ->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )
            ->post($endpoint, [
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'folder' => $folder,
                'signature' => $signature,
            ]);

        if (!$response->successful()) {
            $body = $response->json();
            $message = $body['error']['message'] ?? $response->body();
            throw new RuntimeException("Erreur Cloudinary ({$response->status()}): {$message}");
        }

        $data = $response->json();

        return [
            'secure_url' => $data['secure_url'],
            'public_id' => $data['public_id'],
            'resource_type' => $data['resource_type'] ?? $resourceType,
            'format' => $data['format'] ?? $extension,
            'original_filename' => $file->getClientOriginalName(),
        ];
    }
}
