<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class pricehistory extends Module
{
    public $runUpdate = true;
    public function __construct()
    {
        $this->name = 'pricehistory';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Sirbu Theodora';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Price History');
        $this->description = $this->l('Track price history of your products.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('actionObjectProductDeleteAfter')
            || !$this->installTab(false, 'AdminPriceHistory', 'Price History', 'sync')
        ) {
            return false;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'price_history` (
            `id_price_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_product` int(10) unsigned NOT NULL,
            `price` decimal(20,2) NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id_price_history`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
           && !$this->unregisterHook('actionProductUpdate')
           && !$this->unregisterHook('actionObjectProductDeleteAfter')
           && !$this->removeTab('AdminPriceHistory')
        ) {
            return false;
        }

        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'price_history`';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function installTab($parent_class, $class_name, $title, $icon = false)
    {
        $tab = new Tab();
        if (!$parent_class) {
            $main_id = Tab::getIdFromClassName('SELL');
            $tab->id_parent = $main_id;
        } else {
            $tab->id_parent = (int)Tab::getIdFromClassName($parent_class);
        }
        $tab->name = [];
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $title;
        }
        $tab->icon = $icon;
        $tab->class_name = $class_name;
        $tab->module = $this->name;
        if (!$tab->add()) {
            return false;
        }

        return true;
    }

    private function removeTab($class)
    {
        $id_tab = Tab::getIdFromClassName($class);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                return $tab->delete();
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function hookActionProductUpdate($params)
    {
        if ($this->runUpdate) {
            $productId = (int)Tools::getValue('id_product');
            $price = $params['product']->price;

            $priceHistory = "";
            $priceHistory = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'price_history` WHERE `id_product` = ' . (int) $productId);

            $productIdCurr = (int) $params['id_product'];
            $product = new Product($productIdCurr);
            $query = 'SELECT `price`
            FROM `' . _DB_PREFIX_ . 'price_history`
            WHERE `id_product` = ' . (int)$productId . ' ORDER BY `id_price_history` DESC';
            $price_db = Db::getInstance()->getValue($query);

            if (!Validate::isLoadedObject($product)) {
                return;
            }

            if($price != $price_db) {
                Db::getInstance()->insert('price_history', array(
                    'id_product' => (int)$productId,
                    'price' => pSQL($price),
                    'date_modified' => date('Y-m-d H:i:s'),),
                    1, true);
                $priceHistory = "";
                $this->runUpdate = false;
            }
        }
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        $productId = (int) $params['object']->id;
        Db::getInstance()->delete('price_history', '`id_product` = ' . (int) $productId);
    }
}