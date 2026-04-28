<?php

namespace Octavenz\Reoako\Client;

use Exception;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use SilverStripe\Control\Director;

class ReoakoClient
{
    /**
     * API Domain
     *
     * @var string
     * @config
     */
    private static $default_api_domain = 'https://api.reoako.nz';
    /**
     *  API base path
     *
     * @var string
     * @config
     */
    private static $default_api_base_path = 'api/v1';

    /**
     *  API key
     *
     * @var string
     * @config
     */
    private static $api_key = '';

    /**
     * The API key that will be used for the service. Can be set on the singleton to take priority over configuration.
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var string
     */
    protected $origin = '';

    /**
     * @var string
     */
    protected $endpoint = '';

    /**
     * Get the API key. Priority is given first to explicitly set values on a singleton, then to configuration values
     * and finally to environment values.
     *
     * @return string
     */
    public function getApiKey()
    {
        // Priority given to explicitly set API keys on the singleton object
        if ($this->apiKey) {
            return $this->apiKey;
        }

        // Check environment as override
        if ($envApiKey = Environment::getEnv('SS_REOAKO_API_KEY')) {
            return $envApiKey;
        }

        // Check config for a value defined in YAML
        $key = Config::inst()->get(ReoakoClient::class, 'api_key');
        if (!empty($key)) {
            return $key;
        }

        // Check in the site config for the ReoakoAPI key
        $key = SiteConfig::current_site_config()->ReoakoAPI;
        if (!empty($key)) {
            return $key;
        }

        return '';
    }


    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
        $this->domain = self::$default_api_domain;
        $this->origin = Director::protocolAndHost();
        $this->endpoint = $this->domain . '/' . self::$default_api_base_path;
    }

    public function search($term)
    {
        $client = new Client();

        if (empty($this->apiKey)) {
            throw new Exception("API key not set");
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Token ' . $this->apiKey,
            'Origin'        => $this->origin,
            'Accept'        => 'application/json',
        ];

        try {
            // Use urlencode to handle spaces/special characters
            $url = $this->endpoint . '/entries/?search=' . urlencode($term);

            $response = $client->get($url, ['headers' => $headers]);

            // Cast getBody() to string specifically to ensure json_decode works
            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException | ServerException $error) {
            $response = $error->getResponse();
            $content = $response ? $response->getBody()->getContents() : 'No response body';
            $json = json_decode($content, true);

            // Always return an array so the Controller doesn't crash
            return [
                'error' => $json['message'] ?? ($json['detail'] ?? $error->getMessage())
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
