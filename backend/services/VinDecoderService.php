<?php
require_once __DIR__ . '/ServiceException.php';

// External API: NHTSA vPIC VIN decoder (free, no API key needed).
// Returns the factory specs of a vehicle for its VIN.
//
// Failure handling (Detailed requirements, External API):
//  - a timeout is set on the request,
//  - HTTP errors and unexpected responses are treated as failures,
//  - every failure is written to backend/logs/error.log,
//  - the caller gets a friendly 503 message and the rest of the API keeps working.
class VinDecoderService
{
    private string $baseUrl;
    private int $timeout;
    private int $connectTimeout;

    public function __construct()
    {
        // Defaults are used if config.php has no 'external_api' block yet.
        $defaults = [
            'vin_decoder_url' => 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/',
            'timeout'         => 5,
            'connect_timeout' => 3,
        ];

        $configFile = __DIR__ . '/../config/config.php';
        $config = file_exists($configFile) ? (require $configFile) : [];
        $api = ($config['external_api'] ?? []) + $defaults;

        $this->baseUrl = $api['vin_decoder_url'];
        $this->timeout = (int) $api['timeout'];
        $this->connectTimeout = (int) $api['connect_timeout'];
    }

    public function decode(string $vin): array
    {
        $url = $this->baseUrl . rawurlencode($vin) . '?format=json';

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($curl);
        $curlError = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        if ($body === false) {
            $this->fail('request failed (' . $curlError . ')');
        }
        if ($status !== 200) {
            $this->fail('HTTP status ' . $status);
        }

        $json = json_decode($body, true);
        $row = $json['Results'][0] ?? null;
        if (!is_array($row)) {
            $this->fail('unexpected response format');
        }

        // The API answers 200 even for VINs it cannot decode; then Make is empty.
        $make = $this->value($row, 'Make');
        if ($make === null) {
            throw new ServiceException('No factory specs were found for this VIN.', 404);
        }

        return [
            'vin'           => $vin,
            'make'          => $make,
            'model'         => $this->value($row, 'Model'),
            'model_year'    => $this->value($row, 'ModelYear'),
            'body_class'    => $this->value($row, 'BodyClass'),
            'engine'        => $this->describeEngine($row),
            'fuel_type'     => $this->value($row, 'FuelTypePrimary'),
            'drive_type'    => $this->value($row, 'DriveType'),
            'plant_country' => $this->value($row, 'PlantCountry'),
            'plant_city'    => $this->value($row, 'PlantCity'),
            // ErrorCode "0" means a clean decode; otherwise the API explains what is uncertain.
            'note'          => ($row['ErrorCode'] ?? '0') === '0' ? null : $this->value($row, 'ErrorText'),
            'source'        => 'NHTSA vPIC',
        ];
    }

    // Trimmed text value of a field, or null if it is missing or empty.
    private function value(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        return trim($value);
    }

    // Example result: "2.5 L, 4 cylinders, 203 hp". Null if the API has no engine data.
    private function describeEngine(array $row): ?string
    {
        $parts = [];
        if ($this->value($row, 'DisplacementL') !== null) {
            $parts[] = round((float) $row['DisplacementL'], 1) . ' L';
        }
        if ($this->value($row, 'EngineCylinders') !== null) {
            $parts[] = $row['EngineCylinders'] . ' cylinders';
        }
        if ($this->value($row, 'EngineHP') !== null) {
            $parts[] = round((float) $row['EngineHP']) . ' hp';
        }
        return empty($parts) ? null : implode(', ', $parts);
    }

    // Logs the technical reason and gives the user a friendly message.
    private function fail(string $reason): void
    {
        $this->logError($reason);
        throw new ServiceException('Vehicle specs are temporarily unavailable. Please try again later.', 503);
    }

    // Minimal error log line for this service. Milestone 4 adds the shared logging.
    private function logError(string $reason): void
    {
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] ERROR external API NHTSA vPIC failed: %s\n", date('Y-m-d H:i:s'), $reason);
        @file_put_contents($dir . '/error.log', $line, FILE_APPEND | LOCK_EX);
    }
}
