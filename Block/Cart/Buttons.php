<?php

namespace IDme\GroupVerification\Block\Cart;

use Magento\Framework\View\Element\Template;
use IDme\GroupVerification\Helper\Data;

/**
 * Class Buttons
 * @package IDme\GroupVerification\Block\Cart
 */
class Buttons extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Buttons constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
    }

    /**
     * Check if module is enabled in configuration
     * @return mixed
     */
    public function isOperational()
    {
        return $this->_scopeConfig->getValue('idme/settings/enabled');
    }

    /**
     * Get list of policies from helper
     * @return array|mixed|string
     * @throws \Zend_Http_Client_Exception
     */
    public function getPolicies()
    {
        return $this->helper->getPolicies();
    }

    /**
     * check if user has already verified with ID.me
     * @return bool
     */
    public function hasVerified()
    {
        if ($this->getQuote()->getData('idme_group') !== null) {
            return $this->getQuote()->getData('idme_group');
        }
        return false;
    }

    /**
     * @return mixed|string
     * @throws \Zend_Http_Client_Exception
     */
    public function getAffiliation()
    {
        if ($this->getQuote()->getData('idme_group') !== null) {
            return $this->helper->getFormattedName($this->getQuote()->getData('idme_group'));
        }
        return '';
    }

    /**
     * get url to remove verification from ID.me
     */
    public function getRemoveUrl()
    {
        $routeParams = [
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            '_secure' => $this->getRequest()->isSecure(),
            '_nosid' => true,
        ];

        return $this->getUrl('idme/authorize/remove', $routeParams);
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->_scopeConfig->getValue('idme/settings/client_id');
    }

    /**
     * Redirect Uri for ID.me response
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->helper->getRedirectUri();
    }

    /**
     * Get config About text
     * @return mixed
     */
    public function getAbout()
    {
        return $this->_scopeConfig->getValue('idme/settings/about');
    }

    /**
     * get ID.me authorization url
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return $this->helper->getOauth()->getAuthorizeUrl();
    }
}
