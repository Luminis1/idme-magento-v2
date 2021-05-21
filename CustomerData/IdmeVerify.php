<?php

namespace IDme\GroupVerification\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class IdmeVerify extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \IDme\GroupVerification\Helper\Data
     */
    protected $helper;

    /**
     * IdmeVerify constructor.
     * @param \IDme\GroupVerification\Helper\Data $idmeHelper
     * @param array $data
     */
    public function __construct(
        \IDme\GroupVerification\Helper\Data $idmeHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->helper = $idmeHelper;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function getSectionData()
    {
        return [
            'clientId' => $this->helper->getKey('client_id'),
            'verified' => $this->helper->getIsVerified(),
            'verifiedGroup' => $this->helper->getFormattedName($this->helper->getUserGroup()),
            'policies' => $this->helper->getPolicies(),
            'aboutText' => $this->helper->getKey('about'),
            'removeUrl' => $this->helper->getRemoveUrl(),
            'redirectUri' => $this->helper->getRedirectUri(),
            'startUrl' => $this->helper->getStartUrl(),
            'website_id' => $this->helper->getWebsiteId(),
        ];
    }
}
