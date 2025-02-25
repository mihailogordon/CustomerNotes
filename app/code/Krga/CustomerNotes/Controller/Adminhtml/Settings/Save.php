<?php

namespace Krga\CustomerNotes\Controller\Adminhtml\Settings;

use Krga\CustomerNotes\Model\SettingsFactory;
use Krga\CustomerNotes\Model\ResourceModel\Settings as SettingsResourceModel;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;

class Save extends Action
{
    private $settingsFactory;
    private $settingsResourceModel;
    private $unwatedFields = array(
        'form_key'
    );

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        SettingsFactory $settingsFactory,
        SettingsResourceModel $settingsResourceModel
    ) {
        $this->settingsFactory = $settingsFactory;
        $this->settingsResourceModel = $settingsResourceModel;
        parent::__construct($context);
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

            $this->messageManager->addSuccessMessage(__('Settings saved successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error saving settings: ' . $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
