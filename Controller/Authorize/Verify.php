<?php

namespace IDme\GroupVerification\Controller\Authorize;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use IDme\GroupVerification\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class Verify
 * @package IDme\GroupVerification\Controller\Authorize
 */
class Verify extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var \IDme\GroupVerification\Helper\Oauth
     */
    protected $oauth;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Verify constructor.
     * @param Context $context
     * @param Data $helper
     * @param Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param PageFactory $pageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        Data $helper,
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        PageFactory $pageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->helper = $helper;
        $this->oauth = $helper->getOauth();
        $this->checkoutSession = $checkoutSession;
        $this->request = $context->getRequest();
        $this->quoteRepository = $quoteRepository;
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $result = [];

        /* code from initial callback */
        $code = $this->request->getParam('code');

        /* if code was not found, invalid callback request */
        if (empty($code)) {
            $result['code'] = 'error';
            $result['message'] = __('ID.me verification failed, please contact the store owner (code 101).');
        } else {
            /* request a token with the given code */
            $tokenData = $this->oauth->getAccessToken($code);
            $token = $tokenData['access_token'];

            /* request user profile data with the token */

            $data = $this->oauth->getProfileData($token);

            if (empty($data)) {
                $result['code'] = 'error';
                $result['message'] = __('ID.me verification failed, please contact the store owner (code 102).');
            } else {
                $success = $this->setUserData($data);
                if ($success) {
                    $response['code'] = 'ok';
                    $response['message'] = __('Successfully verified your affiliation via ID.me');
                    $response['userData'] = json_encode($data);
                } else {
                    $response['code'] = 'error';
                    $response['message'] = __('Unfortunately, you have not verified your affiliation with ID.me.');
                }
            }
        }

        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        $resultPage = $this->pageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock(\IDme\GroupVerification\Block\Cart\Buttons::class)
            ->setTemplate('IDme_GroupVerification::checkout/verified.phtml')
            ->toHtml();

        $this->getResponse()->setBody($block);
    }

    /**
     * @param $profile
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function setUserData($profile)
    {
        $this->quote = $this->checkoutSession->getQuote();

        $attributes = $profile['attributes'];
        foreach ($attributes as $attribute) {
            if ($attribute['handle'] === 'uuid') {
                $this->quote->setData('idme_uuid', $attribute['value']);
                $this->customerSession->setData('idme_uuid', $attribute['value']);
            }
        }

        $status = $profile['status'][0];
        if ($status['verified']) {
            $this->quote->setData('idme_verify_started', 1);
            $this->quote->setData('idme_group', $status['group']);
            $this->customerSession->setData('idme_group', $status['group']);
            $subgroups = $this->getSubgroupHandles($status['group'], $status['subgroups']);
            $this->quote->setData('idme_subgroups', json_encode($subgroups));
            $this->customerSession->setData('idme_subgroups', json_encode($subgroups));
        }
        $this->quoteRepository->save($this->quote);

        return $status['verified'];
    }

    /**
     * @param $group
     * @param $subgroups
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function getSubgroupHandles($group, $subgroups)
    {
        $values = [];

        foreach ($this->helper->getPolicies() as $policy) {
            foreach ($policy['groups'] as $affiliation) {
                if ($affiliation['handle'] === $group) {
                    foreach ($affiliation['subgroups'] as $subgroup) {
                        if (in_array($subgroup['name'], $subgroups, true)) {
                            $values[] = $subgroup['handle'];
                        }
                    }
                }
            }
        }
        return $values;
    }
}
