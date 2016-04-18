<?php

namespace C3\ConfigSetupHelper\Model;

use \Magento\Config\Model\Config;

/**
 * Plugin for Magento\Config\Model\Config
 * TODO: Option to hide/unhide checkboxes via js
 */
class ConfigPlugin
{
    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $_messageManager;
    /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection */
    protected $_configCollection;
    /** @var \Magento\Framework\App\RequestInterface */
    protected $_request;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Config\Model\ResourceModel\Config\Data\Collection $dataCollection,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_messageManager = $messageManager;
        $this->_configCollection = $dataCollection;;
        $this->_request = $request;
        $this->_scopeConfig = $scopeConfig;
    }

    public function afterSave(Config $config)
    {
        // If not enabled, or if no checkboxes ticked, skip
        if (!$this->_scopeConfig->getValue('dev/c3_configsetuphelper/enabled') ||
            $this->_request->getParam('configsave', null) === null)
        {
            return;
        }

        // Get website and store codes and ids
        $website = $config->getWebsite() ?: null;
        $store = $config->getStore() ?: null;
        $section = $config->getSection();

        // Set scope based on what has been set
        if ($store !== null) {
            $scope = 'stores';
            $scopeId = $store;
        } elseif ($website !== null) {
            $scope = 'websites';
            $scopeId = $website;
        } else {
            $scope = 'default';
        }

        // Get fully qualified field names from form
        $paths = array();
        foreach (array_keys($this->_request->getParam('configsave',array())) as $underscoreName) {
            // Split name via underscore
            $splitName = explode('_',$underscoreName);
            $section = array_shift($splitName);
            $groupname = array_shift($splitName);
            $fieldname = implode('_',$splitName);
            // Rejoin as full path
            $fullName = "{$section}/{$groupname}/{$fieldname}";
            $paths[] = $fullName;
        }

        // Get path values with correct scope
        $pathValues = $this->_getPathValues($paths, $website, $store);

        // Output values in string per group

        $string = '<pre>' . $this->_getHeader() . '// ' . __('Config for') . ' ' .$section . "\n";
        foreach ($this->_request->getParam('groups') as $groupname => $group) {
            // Check if any paths are selected in this group
            $anyOutput = false;
            foreach (array_keys($group['fields']) as $fieldname) {
                $fullName = "{$section}/{$groupname}/{$fieldname}";
                // Skip if name was not retrieved
                if (!isset($pathValues[$fullName])) {
                    continue;
                }
                $anyOutput = true;
                break;
            }
            if (!$anyOutput)
                continue;
            $string .= "\n// " . __('Settings for %s group', $groupname) . "\n";
            foreach (array_keys($group['fields']) as $fieldname) {
                $fullName = "{$section}/{$groupname}/{$fieldname}";
                // Skip if name was not retrieved
                if (!isset($pathValues[$fullName])) {
                    continue;
                }
                $showVal = "'" . addslashes($pathValues[$fullName]) . "'";
                if ($pathValues[$fullName] === null) {
                    $showVal = 'null';
                }
                // If non-default scope used, add options to method call
                if ($scope == 'default') {
                    $scopeArgs = ", 'default', 0";
                } else {
                    $scopeArgs = ", '{$scope}', {$scopeId}";
                }
                $string .= "\$this->_resourceConfig->saveConfig('" . addslashes($fullName) .
                    "', {$showVal}{$scopeArgs});\n";
            }
        }
        $string .=  $this->_getFooter() . '</pre>';

        $this->_messageManager->addNotice(__("<strong>" .
            __('Setup script segment for these settings') .
            ":</strong><br /><pre>{$string}</pre>"));
    }

    protected function _getHeader()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function _getFooter()
    {
        return "";
    }

    /**
     * Get config values for given paths from database
     *
     * @param array $paths
     * @param null|string $website
     * @param null|string $store
     * @return object
     */
    protected function _getPathValues($paths, $website=null, $store=null)
    {
        // Get collection with correct scope and paths
        $collection = $this->_configCollection;
        if ($store !== null) {
            $collection->addFieldToFilter('scope','stores');
            $collection->addFieldToFilter('scope_id',$store);
        } elseif ($website !== null) {
            $collection->addFieldToFilter('scope','websites');
            $collection->addFieldToFilter('scope_id',$website);
        } else {
            $collection->addFieldToFilter('scope','default');
        }
        $collection->addFieldToFilter('path', ['in' => $paths]);

        // Get value per path
        $pathValues = array();
        foreach ($collection as $configrow) {
            $pathValues[$configrow['path']] = $configrow['value'];
        }

        return $pathValues;
    }
}
