<?php

namespace IDme\GroupVerification\Model\ResourceModel\Order;

/**
 * Class Collection
 * @package IDme\GroupVerification\Model\ResourceModel\Order
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * @var string
     */
    protected $countAmountExpression;

    /**
     * @param $isFilter
     * @return $this
     */
    public function calculateIdmeSales($isFilter)
    {
        $statuses = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }
        $this->setMainTable('sales_order');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getIdmeSalesAmountExpression();

        if ($isFilter == 0) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }

        $this->getSelect()->columns(
            ['lifetime' => "SUM({$expr})", 'average' => "AVG({$expr})"]
        )->where(
            'main_table.status NOT IN(?)',
            $statuses
        )->where(
            'main_table.state NOT IN(?)',
            [\Magento\Sales\Model\Order::STATE_NEW, \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]
        );

        return $this;
    }

    /**
     * @param $isFilter
     * @return $this
     */
    public function calculateIdmeUpt($isFilter)
    {
        $statuses = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }
        $this->setMainTable('sales_order');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getIdmeCountExpression();

        if ($isFilter == 0) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }

        $this->getSelect()->columns(
            ['lifetime' => "SUM({$expr})", 'average' => "AVG({$expr})"]
        )->where(
            'main_table.status NOT IN(?)',
            $statuses
        )->where(
            'main_table.state NOT IN(?)',
            [\Magento\Sales\Model\Order::STATE_NEW, \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]
        );

        return $this;
    }

    /**
     * @return string
     */
    protected function _getIdmeSalesAmountExpression()
    {
        if (null === $this->_salesAmountExpression) {
            $connection = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s',
                    'arguments' => [
                        $connection->getIfNullSql('main_table.base_grand_total', 0),
                    ],
                ]
            );

            $this->_salesAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }

    /**
     * @return string
     */
    protected function _getIdmeCountExpression()
    {
        if (null === $this->countAmountExpression) {
            $connection = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s',
                    'arguments' => [
                        $connection->getIfNullSql('main_table.total_qty_ordered', 0),
                    ],
                ]
            );

            $this->countAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->countAmountExpression;
    }
}
