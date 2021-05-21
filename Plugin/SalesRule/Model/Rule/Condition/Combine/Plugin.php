<?php

namespace IDme\GroupVerification\Plugin\SalesRule\Model\Rule\Condition\Combine;

/**
 * Class Plugin
 * @package IDme\GroupVerification\Plugin\SalesRule\Model\Rule\Condition\Combine
 */
class Plugin
{
    /**
     * @param $subject
     * @param $result
     * @return array
     */
    public function afterGetNewChildSelectOptions(
        $subject,
        $result
    ) {
        $conditions = array_merge_recursive(
            $result,
            [
                $this->getIdmeVerificationConditions(),
            ],
            [
                $this->getIdmeSubgroupConditions(),
            ]
        );


        return $conditions;
    }

    /**
     * @return array
     */
    private function getIdmeVerificationConditions()
    {
        return [
            'label' =>  __('ID.me Verification'),
            'value' =>  \IDme\GroupVerification\Model\Rule\Condition\IDmeRules::class
        ];
    }

    /**
     * @return array
     */
    private function getIdmeSubgroupConditions()
    {
        return [
            'label' => __('ID.me Subgroup'),
            'value' => \IDme\GroupVerification\Model\Rule\Condition\IDmeSubgroupRules::class,
        ];
    }
}
