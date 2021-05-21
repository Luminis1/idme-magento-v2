<?php

namespace IDme\GroupVerification\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;

/**
 * Class IDmeRules
 * @package IDme\GroupVerification\Model\Rule\Condition
 */
class IDmeRules extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \IDme\GroupVerification\Helper\Data
     */
    protected $helper;

    /**
     * IDmeRules constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \IDme\GroupVerification\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \IDme\GroupVerification\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @return $this|\Magento\Rule\Model\Condition\AbstractCondition
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'idme_verification' => __('ID.me Verification'),
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return array|mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData(
                'value_select_options',
                $this->policiesOptionArray()
            );
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate ID.me verification
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $verified = 0;

        if ($this->helper->getIsVerified()) {
            $verified = $this->helper->getUserGroup();
        }
        $model->setData('idme_verification', $verified);
        return parent::validate($model);
    }

    /**
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function policiesOptionArray()
    {
        $policies = $this->helper->getPolicies();

        $options = [];

        foreach ($policies as $policy) {
            $subgroups = [];
            foreach ($policy['groups'] as $subgroup) {
                $option = [
                    'value' =>  $subgroup['handle'],
                    'label' =>  $subgroup['name'],
                ];
                $subgroups[] = $option;
            }
            $option = [
                'value' => $subgroups,
                'label' => $policy['name'],
            ];
            $options[] = $option;
        }
        return $options;
    }
}
