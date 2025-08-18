<?php
/**
 * Controller AJAX do obsługi przenoszenia klientów
 */

class CustomerTransferAjaxModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        
        // Ustaw że nie ma potrzeby SSL
        $this->ssl = false;
    }

    public function postProcess()
    {
        // Sprawdzenie uprawnień administratora
        if (!$this->context->employee || !$this->context->employee->isLoggedBack()) {
            $this->ajaxDie(json_encode(array('success' => false, 'message' => 'Brak uprawnień')));
        }

        $action = Tools::getValue('action');
        
        switch ($action) {
            case 'transfer_customer':
                $this->transferCustomer();
                break;
            case 'get_shops':
                $this->getShops();
                break;
            default:
                $this->ajaxDie(json_encode(array('success' => false, 'message' => 'Nieznana akcja')));
        }
    }

    private function transferCustomer()
    {
        $id_customer = (int)Tools::getValue('id_customer');
        $id_shop_new = (int)Tools::getValue('id_shop_new');

        if (!$id_customer || !$id_shop_new) {
            $this->ajaxDie(json_encode(array('success' => false, 'message' => 'Brakujące parametry')));
        }

        $result = $this->module->transferCustomer($id_customer, $id_shop_new);
        $this->ajaxDie(json_encode($result));
    }

    private function getShops()
    {
        $shops = array();
        
        if (Shop::isFeatureActive()) {
            $shop_list = Shop::getShops(true);
            foreach ($shop_list as $shop) {
                $shops[] = array(
                    'id_shop' => $shop['id_shop'],
                    'name' => $shop['name']
                );
            }
        }
        
        $this->ajaxDie(json_encode(array('success' => true, 'shops' => $shops)));
    }
}
