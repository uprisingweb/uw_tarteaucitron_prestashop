<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class uw_tarteaucitron extends Module
{

    public function __construct() {
        
        
        $this->name = 'uw_tarteaucitron';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'uprising-web';
        $this->need_instance = 0;
        
        
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->trans('Tarte Au Citron',[],'Modules.uw_tarteaucitron');
        $this->description = $this->trans('GDPR friendly cookie manager',[],'Modules.uw_tarteaucitron');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

    }

   
    public function install() {
        return ( parent::install() && $this->registerHook('displayFooter') && $this->registerHook('actionFrontControllerSetMedia'));
    }

    public function uninstall() { 
        return parent::uninstall();
    }

    public function getContent(){      
        if (((bool)Tools::isSubmit('uwtac_submit')) == true) { // save form data if POSTED
            $this->postProcess();
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

   
    protected function renderForm() {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;        
        $helper->submit_action = 'uwtac_submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm() {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    
                    array(
                        'type' => 'text',
                        'name' => 'UWTAC_UUID',
                        'label' => $this->trans('API ID',[],'Modules.uw_tarteaucitron'),
                        'hint' => $this->trans(''),

                        'class' => '',

                    ),
                    array(
                        'type' => 'textarea',
                        'size' => 3,
                        'name' => 'UWTAC_JSCODE',
                        'label' => $this->trans('Add services (JS Code)',[],'Modules.uw_tarteaucitron'),
                        'hint' => $this->trans('If you use free installation, read "Step 3: Add services" on https://tarteaucitron.io/en/free-installation-open-source/',[],'Modules.uw_tarteaucitron'),
                        'class' => ''
                    ),                   
                 
                ),
                'submit' => array(
                    'title' => $this->trans('Save',[],'Modules.uw_tarteaucitron'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues() {
        return array(
            'UWTAC_UUID'    => Configuration::get('UWTAC_UUID', true),
            'UWTAC_JSCODE'  => Configuration::get('UWTAC_JSCODE', true),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess(){
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {Configuration::updateValue($key, trim(Tools::getValue($key)));}
    }


    /**
     * Add Tac JS script on Front
     */
    public function hookActionFrontControllerSetMedia() {  
        $this->context->controller->registerJavascript($this->name . '-loader', 'https://tarteaucitron.io/load.js?uuid='.Configuration::get('UWTAC_UUID'), ['server' => 'remote']);    
    }
    /**
     * Write JS Custom for Services
    */
    public function hookFooter() {
        $jscode = Configuration::get('UWTAC_JSCODE');  
        if(strlen($jscode) > 0 ) {
            $this->context->smarty->assign('tac_jscode', html_entity_decode($jscode));
            return $this->context->smarty->fetch($this->local_path . 'views/templates/hook/footer-javascript.tpl');            
        }
    }
    


}
