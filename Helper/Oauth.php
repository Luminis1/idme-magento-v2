<?php

namespace IDme\GroupVerification\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;

/**
 * Handles Verification via ID.me API
 * Class Oauth
 * @package IDme\GroupVerification\Helper
 */
class Oauth extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENDPOINT_PRODUCTION = 'https://api.id.me';
    const AUTHORIZE_PATH = '/oauth/authorize';
    const TOKEN_PATH = '/oauth/token';
    const POLICIES_PATH = '/api/public/v3/policies';
    const ATTRIBUTES_PATH = '/api/public/v3/attributes.json';
    const API_ORIGIN = 'MAGENTO2-IDME';
    const IMG_URL = 'https://s3.amazonaws.com/idme/buttons/v3/equal/';

    private $baseParams;
    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Oauth constructor.
     * @param Context $context
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        Context $context,
        ZendClientFactory $httpClientFactory
    ) {
        $this->httpClientFactory = $httpClientFactory;
        parent::__construct($context);
    }

    /**
     * Redirect URI for ID.me
     * @return string
     */
    public function getCallbackUrl()
    {
        $routeParams = [
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            '_secure' => $this->_getRequest()->isSecure(),
            '_nosid' => true,
        ];
        return $this->_getUrl('idme/authorize/verify', $routeParams);
    }

    /**
     * Url to Remove ID.me from session
     * @return string
     */
    public function getRemoveUrl()
    {
        $routeParams = [
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            '_secure' => $this->_getRequest()->isSecure(),
            '_nosid' => true,
        ];
        return $this->_getUrl('idme/authorize/remove', $routeParams);
    }

    /**
     * @param $key
     * @param null $storeId
     * @return mixed
     */
    public function getKey($key, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'idme/settings/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get parameters used in all API calls
     * return array
     */
    private function getBaseParams()
    {
        if ($this->baseParams !== null) {
            return $this->baseParams;
        }
        $this->baseParams = [
            'client_id' => trim($this->getKey('client_id')),
            'client_secret' => trim($this->getKey('client_secret')),
            'redirect_uri' => $this->getCallbackUrl(),
        ];

        return $this->baseParams;
    }

    /**
     * Trade a given code for a User's Access Token
     * @param $code
     * @return array
     */
    public function getAccessToken($code)
    {
        $uri = self::ENDPOINT_PRODUCTION . self::TOKEN_PATH;
        $params = $this->getBaseParams();
        $params['code'] = $code;
        $params['grant_type'] = 'authorization_code';

        $response = $this->apiPost($params, $uri);
        return ['access_token' => $response->access_token, 'scope' => $response->scope];
    }

    /**
     * Trade token for User Profile Data
     * @param $token
     * @return bool|mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getProfileData($token)
    {
        $uri = self::ENDPOINT_PRODUCTION . self::ATTRIBUTES_PATH;
        $params = [
            'access_token' => $token,
        ];

        return $this->apiGet($params, $uri);
    }

    /**
     * Glue together the Authorize URL (use to get a code)
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return self::ENDPOINT_PRODUCTION . self::AUTHORIZE_PATH;
    }

    /**
     * Retrieve a list of groups a merchant is authorized to verify
     * @return bool|mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getPolicies()
    {
        $params = [
            'client_id' => $this->getKey('client_id'),
            'client_secret' => $this->getKey('client_secret'),
        ];
        $uri = self::ENDPOINT_PRODUCTION . self::POLICIES_PATH;

        $response = $this->apiGet($params, $uri);

        $policies = [];
        if (is_array($response)) {
            foreach ($response as $data) {
                if ($data['handle'] === 'military') {
                    $imgName = 'troop.png';
                } else {
                    $imgName = $data['handle'] . '.png';
                }
                $data['img_url'] = self::IMG_URL . $imgName;
                $data['popup_url'] = $this->getAuthorizeUrl() . '?client_id=' . $this->getKey(
                        'client_id'
                    ) . '&redirect_uri=' . $this->getCallbackUrl() . '&response_type=code&scope=' . $data['handle'];
                $policies[] = $data;
            }
        }
        return $policies;
    }

    /**
     * @return ZendClient
     */
    public function getClient()
    {
        return $this->httpClientFactory->create();
    }

    /**
     * Make GET requests
     * @param $params
     * @param $uri
     * @return bool|mixed
     * @throws \Zend_Http_Client_Exception
     */
    private function apiGet($params, $uri)
    {
        $client = $this->getClient();
        $client->setUri($uri);
        $client->setHeaders(
            [
                'X-API-ORIGIN' => self::API_ORIGIN,
            ]
        );

        $client->setParameterGet($params);
        $response = $client->request('GET');

        if ($response->isError()) {
            return false;
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Make POST requests
     * @param $params
     * @param $uri
     * @return bool|mixed
     */
    private function apiPost($params, $uri)
    {
        try {
            $client = $this->getClient();
            $client->setUri($uri);
            $client->setHeaders(
                [
                    'X-API-ORIGIN' => self::API_ORIGIN,
                ]
            );

            $client->setParameterPost($params);


            $response = $client->request('POST');
        } catch (\Zend_Http_Client_Exception $e) {
            return false;
        }

        if ($response->isError()) {
            return false;
        }

        return json_decode($response->getBody());
    }
}
