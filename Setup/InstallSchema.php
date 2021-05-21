<?php

namespace IDme\GroupVerification\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class InstallSchema
 * @package IDme\GroupVerification\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addQuoteColumns($setup);
        $this->addOrderColumns($setup);
        $this->addOrderGridColumns($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addQuoteColumns($setup)
    {
        $quoteTable = 'quote';
        $quoteDb = $setup->getConnection('checkout');
        $columns = [
            'idme_uuid' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '128',
                    'comment' => 'IDme UUID',
                ],
            'idme_group' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'IDme Group',
                ],
            'idme_subgroups' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'IDme Subgroup',
                ],
            'idme_verify_started' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => false,
                    'comment' => 'IDme verification started flag',
                ],
        ];

        $this->addIDmeColumns($setup, $quoteDb, $quoteTable, $columns);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addOrderColumns($setup)
    {
        $salesDb = $setup->getConnection('sales');
        $orderTable = 'sales_order';
        $columns = [
            'idme_uuid' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '128',
                    'default' => '',
                    'comment' => 'IDme UUID',
                ],
            'idme_group' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'default' => '',
                    'comment' => 'IDme Group',
                ],
            'idme_subgroups' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'default' => '',
                    'comment' => 'IDme Subgroup',
                ],
        ];

        $this->addIDmeColumns($setup, $salesDb, $orderTable, $columns);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addOrderGridColumns($setup)
    {
        $salesDb = $setup->getConnection('sales');
        $orderGridTable = 'sales_order_grid';

        $columns = [
            'idme_uuid' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '128',
                    'default' => '',
                    'comment' => 'IDme UUID',
                ],
            'idme_group' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'default' => '',
                    'comment' => 'IDme Group',
                ],
            'idme_subgroups' =>
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'default' => '',
                    'comment' => 'IDme Subgroup',
                ],
        ];

        $this->addIDmeColumns($setup, $salesDb, $orderGridTable, $columns);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $db
     * @param string $table
     * @param array $columns
     */
    public function addIDmeColumns($setup, $db, $table, $columns)
    {
        foreach ($columns as $column => $definition) {
            $db
                ->addColumn(
                    $setup->getTable($table),
                    $column,
                    $definition
                );
        }
    }
}
