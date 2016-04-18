<?php

namespace C3\ConfigSetupHelper\Block\System\Config;

/**
 * Plugin for Magento\Config\Block\System\Config\Form
 */
class FormPlugin
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Config\Block\System\Config\Form               $form
     * @param \Closure                                               $proceed
     * @param \Magento\Framework\Data\Form\Element\Fieldset          $fieldset
     * @param \Magento\Config\Model\Config\Structure\Element\Group   $group
     * @param \Magento\Config\Model\Config\Structure\Element\Section $section
     * @param string                                                 $fieldPrefix
     * @param string                                                 $labelPrefix
     *
     * Add input before current label. Has to wrap function as we need access to
     * the fieldset but only after it's been processed
     */
    public function aroundInitFields(
        \Magento\Config\Block\System\Config\Form $form,
        \Closure $proceed,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        \Magento\Config\Model\Config\Structure\Element\Group $group,
        \Magento\Config\Model\Config\Structure\Element\Section $section,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        $proceed($fieldset, $group, $section, $fieldPrefix, $labelPrefix);

        // If disabled then do nothing
        if (!$this->_scopeConfig->getValue('dev/c3_configsetuphelper/enabled')) {
            return;
        }

        foreach ($fieldset->getChildren() as $element) {
            $data = $element->getData();
            $path = $data['html_id'];
            $prefix = "<input type=\"checkbox\" name=\"configsave[{$path}]\" value=\"1\" class=\"configsave\" />&nbsp;";
            if (isset($data['label'])) {
                $data['label'] = $prefix . $data['label'];
            }
            $element->setData($data, $form->getScope());
        }
    }
}
