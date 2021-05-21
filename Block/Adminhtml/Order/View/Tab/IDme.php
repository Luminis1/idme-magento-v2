<?php

namespace IDme\GroupVerification\Block\Adminhtml\Order\View\Tab;

/**
 * Class IDme
 * @package IDme\GroupVerification\Block\Adminhtml\Order\View\Tab
 */
class IDme extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * IDme constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_template = 'IDme_GroupVerification::order/view/tab/idme.phtml';
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('ID.me');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('ID.me');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get ID.me data for an order
     * @return array
     */
    public function getIdmeInfo()
    {
        $order = $this->getOrder();

        $idmeOrderInfo = [
            ['label' => 'UUID', 'value' => $order->getData('idme_uuid')],
            ['label' => 'Group', 'value' => $order->getData('idme_group')],
            ['label' => 'Subgroups', 'value' => json_decode($order->getData('idme_subgroups'))],
        ];

        return $idmeOrderInfo;
    }
}
