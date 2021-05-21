<?php

namespace IDme\GroupVerification\Block\Adminhtml\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Instructions
 * @package IDme\GroupVerification\Block\Adminhtml\Config
 */
class Instructions extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    const LEARN_MORE_URL = 'https://business.id.me';
    const DEVELOPER_URL = 'https://developer.id.me';
    const DOCS_URL = 'https://developer.id.me/documentation';
    const APPS_URL = 'https://developer.id.me/organizations';

    /**
     * @var \IDme\GroupVerification\Helper\Data
     */
    protected $helper;

    /**
     * Instructions constructor.
     * @param \IDme\GroupVerification\Helper\Data $helper
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     */
    public function __construct(
        \IDme\GroupVerification\Helper\Data $helper,
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<span>In order to test the integration, follow the steps below:</span>';
        $html .= '<ul class="steps">';
        $html .= '<li>' . __('Create a developer account at') . ' <a href="' . $this->escapeUrl(self::DEVELOPER_URL) . '" target="_blank">' . $this->escapeUrl(self::DEVELOPER_URL) . '</a></li>';
        $html .= '<li>' . __('Register an application at') . ' <a href="' . self::APPS_URL . '" target="_blank">' . self::APPS_URL . '</a></li>';
        $html .= '<li>' . __('Fill in <strong>Redirect URI</strong> with') . ' ' . $this->escapeUrl($this->helper->getRedirectUri()) . '</li>';
        $html .= '<li>' . __('Copy and paste your <strong>Client ID</strong> and <strong>Client Secret</strong> values from your application settings on ID.me') . '</li>';
        $html .= '<li>' . __('That\'s it! You are ready to go.') . '</li>';
        $html .= '</ul>';
        $html .= '<div class="heading"><span class="heading-intro"><a href="' . $this->escapeUrl(self::LEARN_MORE_URL) . '" target="_blank">' . __('Learn more about ID.me') . '</a></span></div>';
        $html .= '<div class="heading"><span class="heading-intro"><a href="' . $this->escapeUrl(self::DOCS_URL) . '" target="_blank">' . __('Read developer documentation') . '</a></span></div>';

        return $html;
    }
}
