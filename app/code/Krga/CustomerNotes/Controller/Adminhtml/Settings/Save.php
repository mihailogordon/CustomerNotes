<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\Settings;

use Krga\CustomerNotes\Model\SettingsFactory;
use Krga\CustomerNotes\Model\ResourceModel\Settings as SettingsResourceModel;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class Save extends Action
{
    private $settingsFactory;
    private $settingsResourceModel;
    private $cacheTypeList;
    private $cacheFrontendPool;
    
    private $unwatedFields = ['form_key'];

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        SettingsFactory $settingsFactory,
        SettingsResourceModel $settingsResourceModel,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
        $this->settingsFactory = $settingsFactory;
        $this->settingsResourceModel = $settingsResourceModel;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->messageManager->addErrorMessage(__('No data to save.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        try {
            foreach ($data as $optionName => $optionValue) {
                if (in_array($optionName, $this->unwatedFields)) {
                    continue;
                }
            
                $setting = $this->settingsFactory->create();
                $this->settingsResourceModel->load($setting, $optionName, 'option_name');
            
                $setting->setOptionName($optionName);
                $setting->setOptionValue($optionValue);
        
                $this->settingsResourceModel->save($setting);
            }            

            $this->flushCache();

            $this->messageManager->addSuccessMessage(__('Settings saved successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error saving settings: ' . $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    private function flushCache()
    {
        $types = ['block_html', 'full_page', 'layout', 'translate'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}