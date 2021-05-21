<?php

namespace IDme\GroupVerification\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package IDme\GroupVerification\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'idme_connect';
    const CACHE_KEY = 'idme_group';
    const CACHE_LIFETIME = 60 * 60 * 24 * 7;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheInterface;
    /**
     * @var Oauth
     */
    protected $oauthHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\App\CacheInterface $cacheInterface
     * @param Oauth $oauth
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\CacheInterface $cacheInterface,
        Oauth $oauth,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->cacheInterface = $cacheInterface;
        $this->oauthHelper = $oauth;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * Get ID.me setting from config
     * @param string $key
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
     * @param $group
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function getFormattedName($group)
    {
        foreach ($this->getPolicies() as $policy) {
            if ($policy['handle'] === $group) {
                return $policy['name'];
            }
        }
        return '';
    }

    /**
     * @return Oauth
     */
    public function getOauth()
    {
        return $this->oauthHelper;
    }

    /**
     * Returns an array of policies associated with merchant ID.me account
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function getPolicies()
    {
        $values = $this->cacheInterface->load(self::CACHE_KEY.'_'.$this->storeManager->getStore()->getId());
        $values = json_decode($values, true);
        if (is_array($values)) {
            return $values;
        }

        $values = $this->oauthHelper->getPolicies();

        if (is_array($values) && count($values) > 0) {
            $this->cacheInterface->save(
                json_encode($values, true),
                self::CACHE_KEY.'_'.$this->storeManager->getStore()->getId(),
                [self::CACHE_TAG.'_'.$this->storeManager->getStore()->getId()],
                self::CACHE_LIFETIME
            );
        }

        return $values;
    }

    /**
     * Return callback uri that ID.me should use for verification
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->oauthHelper->getCallbackUrl();
    }

    /**
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->oauthHelper->getRemoveUrl();
    }

    /**
     * @return string
     */
    public function getStartUrl()
    {
        $routeParams = [
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB,
            '_secure' => $this->_getRequest()->isSecure(),
            '_nosid' => true,
        ];
        return $this->_getUrl('idme/authorize/start', $routeParams);
    }

    /**
     * @return bool
     */
    public function getIsVerified()
    {
        return $this->getUserGroup() !== null;
    }

    /**
     * @return mixed
     */
    public function getUserGroup()
    {
        return $this->customerSession->getData('idme_group');
    }

    public function getUserSubgroups()
    {
        return $this->customerSession->getData('idme_subgroups');
    }

    public function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getId();
    }
}
