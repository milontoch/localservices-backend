<?php

namespace App\Services;

use GuzzleHttp\Client;

class MapboxService
{
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->apiKey = config('services.mapbox.api_key');
        $this->client = new Client();
    }

    /**
     * Geocode an address to latitude and longitude
     * 
     * @param string $address
     * @return array ['latitude' => float, 'longitude' => float]
     * @throws \Exception
     */
    public function geocode($address)
    {
        try {
            $response = $this->client->get("https://api.mapbox.com/geocoding/v5/mapbox.places/" . urlencode($address) . ".json", [
                'query' => [
                    'access_token' => $this->apiKey,
                    'limit' => 1
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (empty($data['features'])) {
                throw new \Exception('Address not found');
            }

            $coordinates = $data['features'][0]['geometry']['coordinates'];

            return [
                'longitude' => $coordinates[0],
                'latitude' => $coordinates[1]
            ];
        } catch (\Exception $e) {
            throw new \Exception('Geocoding failed: ' . $e->getMessage());
        }
    }
}
