<?php
/**
 * Customer Transfer Module
 * Umożliwia przenoszenie klientów między sklepami w środowisku multishop
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerTransfer extends Module
{
    public function __construct()
    {
        $this->name = 'customertransfer';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Admin';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Customer Transfer');
        $this->description = $this->l('Umożliwia przenoszenie klientów między sklepami w środowisku multishop');
        $this->confirmUninstall = $this->l('Czy na pewno chcesz odinstalować ten moduł?');
        
        // Obsługa AJAX
        if (Tools::isSubmit('ajax') && Tools::getValue('action')) {
            $this->processAjax();
        }
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayAdminCustomers') &&
            $this->registerHook('actionCustomerFormBuilderModifier') &&
            $this->registerHook('actionCustomerGridDefinitionModifier') &&
            $this->registerHook('displayAdminCustomersButtons');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Hook do dodania przycisku w widoku edycji klienta
     */
    public function hookDisplayAdminCustomers($params)
    {
        // Debug - sprawdźmy co otrzymujemy
        $id_customer = null;
        
        // Sprawdzamy różne sposoby otrzymania ID klienta
        if (isset($params['id_customer'])) {
            $id_customer = (int)$params['id_customer'];
        } elseif (Tools::getValue('id_customer')) {
            $id_customer = (int)Tools::getValue('id_customer');
        }
        
        // Sprawdzamy czy jesteśmy w kontekście klienta
        if ($id_customer) {
            $customer = new Customer($id_customer);
            
            if (Validate::isLoadedObject($customer)) {
                $this->context->smarty->assign(array(
                    'customer_id' => $id_customer,
                    'customer_name' => $customer->firstname . ' ' . $customer->lastname,
                    'current_shop' => $customer->id_shop,
                    'available_shops' => $this->getAvailableShops(),
                    'module_dir' => $this->_path,
                    'ajax_url' => $this->context->link->getAdminLink('AdminCustomers'),
                    'admin_token' => Tools::getAdminTokenLite('AdminCustomers')
                ));
                
                return $this->display(__FILE__, 'customer_transfer_button.tpl');
            }
        }
        
        return '';
    }

    /**
     * Hook do dodania przycisku w nagłówku formularza klienta
     */
    public function hookDisplayAdminCustomersButtons($params)
    {
        if (isset($params['id_customer']) && $params['id_customer']) {
            $id_customer = (int)$params['id_customer'];
            $customer = new Customer($id_customer);
            
            if (Validate::isLoadedObject($customer)) {
                $this->context->smarty->assign(array(
                    'customer_id' => $id_customer,
                    'customer_name' => $customer->firstname . ' ' . $customer->lastname,
                    'current_shop' => $customer->id_shop,
                    'available_shops' => $this->getAvailableShops(),
                    'module_dir' => $this->_path
                ));
                
                return $this->display(__FILE__, 'customer_transfer_button.tpl');
            }
        }
        
        return '';
    }

    /**
     * Pobiera listę dostępnych sklepów
     */
    private function getAvailableShops()
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
        
        return $shops;
    }

    /**
     * Przenosi klienta do innego sklepu
     */
    public function transferCustomer($id_customer, $id_shop_new)
    {
        $customer = new Customer($id_customer);
        
        if (!Validate::isLoadedObject($customer)) {
            return array('success' => false, 'message' => 'Nie znaleziono klienta');
        }

        $shop = new Shop($id_shop_new);
        if (!Validate::isLoadedObject($shop)) {
            return array('success' => false, 'message' => 'Nie znaleziono sklepu docelowego');
        }

        try {
            // Zapisujemy poprzedni sklep
            $old_shop = $customer->id_shop;
            
            // Ustawiamy nowy sklep
            $customer->id_shop = $id_shop_new;
            
            // Zapisujemy zmiany
            if ($customer->save()) {
                // Opcjonalnie: przenieś również adresy klienta
                $this->transferCustomerAddresses($id_customer, $id_shop_new);
                
                // Opcjonalnie: przenieś zamówienia klienta (jeśli potrzebne)
                // $this->transferCustomerOrders($id_customer, $id_shop_new);
                
                // Pobierz nazwy sklepów
                $old_shop_obj = new Shop($old_shop);
                $old_shop_name = Validate::isLoadedObject($old_shop_obj) ? $old_shop_obj->name : 'ID: ' . $old_shop;
                
                return array(
                    'success' => true, 
                    'message' => sprintf(
                        'Klient został przeniesiony ze sklepu %s do sklepu %s',
                        $old_shop_name,
                        $shop->name
                    )
                );
            } else {
                return array('success' => false, 'message' => 'Błąd podczas zapisywania klienta');
            }
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Błąd: ' . $e->getMessage());
        }
    }

    /**
     * Przenosi adresy klienta do nowego sklepu
     */
    private function transferCustomerAddresses($id_customer, $id_shop_new)
    {
        $addresses = Db::getInstance()->executeS('
            SELECT id_address 
            FROM ' . _DB_PREFIX_ . 'address 
            WHERE id_customer = ' . (int)$id_customer
        );

        foreach ($addresses as $addr) {
            $address = new Address($addr['id_address']);
            if (Validate::isLoadedObject($address)) {
                // Dla adresów może nie być bezpośrednio pola id_shop,
                // ale można to rozszerzyć w razie potrzeby
            }
        }
    }

    /**
     * Opcjonalnie: Przenosi zamówienia klienta do nowego sklepu
     */
    private function transferCustomerOrders($id_customer, $id_shop_new)
    {
        // Ta funkcja może być niebezpieczna - zamówienia mają zazwyczaj
        // pozostać w oryginalnym sklepie ze względów księgowych
        // Implementuj tylko jeśli jest to absolutnie konieczne
    }

    /**
     * Obsługa żądań AJAX
     */
    public function getContent()
    {
        $output = '';
        
        // Obsługa AJAX
        if (Tools::isSubmit('ajax') && Tools::getValue('action')) {
            $this->processAjax();
            exit; // Ważne: zakończ wykonanie po AJAX
        }
        
        $output .= $this->displayConfirmation($this->l('Moduł został pomyślnie zainstalowany!'));
        $output .= '<p>' . $this->l('Moduł automatycznie dodaje przycisk przenoszenia klientów w widoku edycji klienta.') . '</p>';
        
        return $output;
    }

    /**
     * Przetwarzanie żądań AJAX
     */
    private function processAjax()
    {
        // Sprawdzenie uprawnień
        if (!$this->context->employee || !$this->context->employee->isLoggedBack()) {
            die(json_encode(array('success' => false, 'message' => 'Brak uprawnień')));
        }

        $action = Tools::getValue('action');
        
        try {
            switch ($action) {
                case 'transfer_customer':
                    $id_customer = (int)Tools::getValue('id_customer');
                    $id_shop_new = (int)Tools::getValue('id_shop_new');

                    if (!$id_customer || !$id_shop_new) {
                        die(json_encode(array('success' => false, 'message' => 'Brakujące parametry: id_customer=' . $id_customer . ', id_shop_new=' . $id_shop_new)));
                    }

                    $result = $this->transferCustomer($id_customer, $id_shop_new);
                    die(json_encode($result));
                    break;
                    
                case 'get_shops':
                    $shops = $this->getAvailableShops();
                    die(json_encode(array('success' => true, 'shops' => $shops)));
                    break;
                    
                default:
                    die(json_encode(array('success' => false, 'message' => 'Nieznana akcja: ' . $action)));
            }
        } catch (Exception $e) {
            die(json_encode(array('success' => false, 'message' => 'Błąd: ' . $e->getMessage())));
        }
    }
}
