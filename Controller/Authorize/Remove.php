<?php

namespace IDme\GroupVerification\Controller\Authorize;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class Remove
 * @package IDme\GroupVerification\Controller\Authorize
 */
class Remove extends \Magento\Framework\App\Action\Action
{
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
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Remove constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $context->getRequest();
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->removeIdmeVerification();

        $result['code'] = 'ok';
        $result['message'] = __('You have successfully removed your ID.me verification from the session.');
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    /**
     * Remove ID.me variables from quote and customer session, except idme_verify_started.
     */
    public function removeIdmeVerification()
    {
        $this->quote = $this->checkoutSession->getQuote();
        $this->quote->setData('idme_uuid', null);
        $this->customerSession->setData('idme_uuid', null);
        $this->quote->setData('idme_group', null);
        $this->customerSession->setData('idme_group', null);
        $this->quote->setData('idme_subgroups', null);
        $this->customerSession->setData('idme_subgroups', null);
        $this->quoteRepository->save($this->quote);
    }
}
