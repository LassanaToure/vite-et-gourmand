<?php
declare(strict_types=1);

final class Geocoder
{
    public function __construct(
        private string $url,
        private int $timeout,
        private float $minScore
    ) {
    }

    public static function fromConfig(): self
    {
        $config = order_config();

        return new self($config['geocoder_url'], $config['geocoder_timeout'], $config['geocoder_min_score']);
    }

    public function locate(string $address, string $postcode, string $city): ?array
    {
        $body = $this->request(trim($address . ' ' . $postcode . ' ' . $city));
        if ($body === null) {
            return null;
        }

        $data = json_decode($body, true);
        $feature = is_array($data) ? ($data['features'][0] ?? null) : null;
        if (!is_array($feature)) {
            return null;
        }

        $coordinates = $feature['geometry']['coordinates'] ?? null;
        $properties = $feature['properties'] ?? [];
        if (!is_array($coordinates) || count($coordinates) < 2 || (float) ($properties['score'] ?? 0) < $this->minScore) {
            return null;
        }

        return [
            'lon' => (float) $coordinates[0],
            'lat' => (float) $coordinates[1],
            'citycode' => (string) ($properties['citycode'] ?? ''),
        ];
    }

    private function request(string $query): ?string
    {
        $handle = curl_init($this->url . '?' . http_build_query(['q' => $query, 'limit' => 1]));
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'ViteGourmand/1.0',
        ]);

        $body = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return is_string($body) && $status === 200 ? $body : null;
    }
}
