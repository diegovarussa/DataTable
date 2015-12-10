<?php

namespace DataTable\Controller;

use DataTable\Model\DataTableModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('layout/bootstrap');
        $result  = [];
        return new ViewModel(['date' => $result]);
    }

    public function ajaxAction()
    {
        /** @var DataTableModel $dataTable */
        $dataTable = $this->getServiceLocator()->get('dataTableModel');
        $result    = $dataTable->init($this->params()->fromQuery(), 'card', "`effect` IS NULL")->getJson();

        echo $result;
        exit;
    }
}