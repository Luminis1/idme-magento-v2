<?php

namespace IDme\GroupVerification\Block\Adminhtml\Dashboard;

use Magento\Backend\Block\Template\Context;

/**
 * Class Idme
 * @package IDme\GroupVerification\Block\Adminhtml\Dashboard
 */
class Idme extends \Magento\Backend\Block\Dashboard\Bar
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \IDme\GroupVerification\Model\ResourceModel\Order\CollectionFactory
     */
    protected $idmeCollectionFactory;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $idmeQuoteCollectionFactory;

    /**
     * Idme constructor.
     * @param Context $context
     * @param \IDme\GroupVerification\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        \IDme\GroupVerification\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->idmeCollectionFactory = $collectionFactory;
        $this->idmeQuoteCollectionFactory = $quoteCollectionFactory;
        parent::__construct($context, $orderCollectionFactory, $data);
    }

    /**
     * @return $this|\Magento\Backend\Block\Dashboard\Bar
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }

        $isFilter = $this->getRequest()->getParam(
                'store'
            ) || $this->getRequest()->getParam(
                'website'
            ) || $this->getRequest()->getParam(
                'group'
            );

        $collection = $this->idmeCollectionFactory->create()->calculateIdmeSales($isFilter);
        $sales = $this->getSalesCollection($collection);

        $unitsPerTransaction = $this->getUpt($isFilter);
        $conversionRate = $this->getStartedCount() === 0 ? 0 : $collection->getTotalCount()/$this->getStartedCount();

        $this->addTotal(__('Revenue'), $sales->getLifetime());
        $this->addTotal(__('Average Order Value'), $sales->getAverage());
        $this->addTotal(__('Conversion Rate'), number_format($conversionRate, 6), true);
        $this->addTotal(__('Units Per Transaction'), number_format($unitsPerTransaction, 2), true);

        return parent::_prepareLayout();
    }

    /**
     * @param $isFilter
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getUpt($isFilter)
    {
        $collection = $this->idmeCollectionFactory->create()->calculateIdmeUpt($isFilter);
        return $this->getSalesCollection($collection)->getAverage();
    }

    /**
     * @return int
     */
    protected function getStartedCount()
    {
        $quoteCollection = $this->idmeQuoteCollectionFactory->create();
        $quoteCollection->addFieldToFilter('idme_verify_started', 1);

        return $quoteCollection->getSize();
    }

    /**
     * @param $collection
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSalesCollection($collection)
    {
        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        }
        $collection->addFieldToFilter('idme_uuid', ['nin' => ['', null]]);
        $collection->setPageSize(1);
        return $collection->getFirstItem();
    }
}
