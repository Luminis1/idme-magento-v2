<?php

namespace IDme\GroupVerification\Controller\Authorize;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Start
 * @package IDme\GroupVerification\Controller\Authorize
 */
class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Start constructor.
     * @param Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param Context $context
     */
    public function __construct(
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        Context $context
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->checkoutSession->getQuote()->setData('idme_verify_started', 1);
        $this->quoteRepository->save($this->checkoutSession->getQuote());

        $result['code'] = 'ok';
        $result['message'] = __('ID.me verification started.');
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
