<?php

namespace IDme\GroupVerification\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use IDme\GroupVerification\Helper\Data;

/**
 * Class ConfigProvider
 * @package IDme\GroupVerification\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected $idmeHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * ConfigProvider constructor.
     * @param Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->idmeHelper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * Get config for ID.me buttons
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function getConfig()
    {
        $isVerified = $this->idmeHelper->getIsVerified();
        $idmeCheckoutConfig = [
            'idmeCheckout' => [
                'redirectUri' => $this->idmeHelper->getRedirectUri(),
                'enabled' => $this->idmeHelper->getKey('enabled'),
                'verified' => ($isVerified !== null) ? $isVerified : false,
                'clientId' => $this->idmeHelper->getKey('client_id'),
                'affiliation' => $this->customerSession->getData('idme_group'),
                'policies' => $this->idmeHelper->getPolicies(),
                'aboutContent' => $this->idmeHelper->getKey('about'),
                'removeUrl' =>  $this->idmeHelper->getRemoveUrl(),
                'startUrl'  =>  $this->idmeHelper->getStartUrl(),
            ],
        ];
        return $idmeCheckoutConfig;
    }
}
