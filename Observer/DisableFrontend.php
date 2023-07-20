<?php

namespace Abelbm\DisableFrontend\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;

class DisableFrontend implements ObserverInterface
{

    protected $_actionFlag;
    protected $redirect;
    private $helperBackend;
    private $scopeConfig;


    /**
     *
     * @param ActionFlag $actionFlag
     * @param RedirectInterface $redirect
     * @param Data $helperBackend
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ActionFlag           $actionFlag,
        RedirectInterface    $redirect,
        Data                 $helperBackend,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->helperBackend = $helperBackend;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Stores > Configuration > Advanced > Admin
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getRequest();
        if ($request->getFullActionName() == '__') {
            return;
        }
        $controller = $observer->getControllerAction();
        $configValue = $this->scopeConfig->getValue('admin/disable_frontend/show_as_frontend', ScopeInterface::SCOPE_STORES);
        if ($configValue == 1) {
            $controller = $observer->getControllerAction();
            $this->_actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($controller->getResponse(), $this->helperBackend->getHomePageUrl());
        }

        if ($configValue == 2) {
            $allowedActrions = $this->scopeConfig->getValue('admin/disable_frontend/allowed_actions', ScopeInterface::SCOPE_STORES);
            $allowedActrions = explode(',', $allowedActrions);
            foreach ($allowedActrions as $action) {
                if ($request->getFullActionName() != $action) {
                    continue;
                }
                $redirectUrl = $this->scopeConfig->getValue('admin/disable_frontend/url_for_redirect', ScopeInterface::SCOPE_STORES);
                if ($redirectUrl) {
                    $this->redirect->redirect($controller->getResponse(), $redirectUrl);
                }
                return;
            }
        }
    }
}
