<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminPriceHistoryController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->bootstrap = true;
        $this->table = 'price_history';
        $this->identifier = 'id_price_history';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->_defaultOrderBy = 'date_modified';
        $this->_defaultOrderWay = 'DESC';

        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->allow_export = true;
        
        $this->fields_list = array(
            'id_price_history' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 'auto',
            ),
            'id_product' => array(
                'title' => $this->l('Product ID'),
                'width' => 'auto',
            ),
            'price' => array(
                'title' => $this->l('Price'),
                'width' => 'auto',
                'type' => 'price',
            ),
            'date_modified' => array(
                'title' => $this->l('Date'),
                'width' => 'auto',
                'type' => 'datetime',
            ),
        );
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete Selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected item(s)?')
            )
        );
    }

    public function renderList()
    {
        return parent::renderList();
    }
}