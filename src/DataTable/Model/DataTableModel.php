<?php

namespace DataTable\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceManager;

class DataTableModel
{
    /** @var ServiceManager */
    private $serviceManager = null;
    /** @var Adapter $adapter */
    private $adapterDb = null;
    private $data = [];

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->adapterDb      = $this->serviceManager->get('AdapterDb');
    }

    public function init($params, $table, $whereSql = '')
    {
        $result['draw'] = (int)$params['draw'];

        $columns = $this->_extractColumns($params);

        $sql   = new Sql($this->adapterDb);
        $where = new Where();

        $select = $sql->select()->from($table)->columns(array('total' => new Expression('COUNT(*)')));
        if ($whereSql) {
            $where->literal($whereSql);
            $select->where($where);
        }
        $statement              = $sql->prepareStatementForSqlObject($select);
        $resultsSql             = $statement->execute();
        $result['recordsTotal'] = (int)$resultsSql->current()['total'];

        $select = $sql->select()->from($table)->columns($columns);

        $globalSearch = [];
        if ($search = $params['search']['value']) {
            foreach ($columns as $col) {
//                $select->where->or->like($col, "%{$search}%");
                if ($params['search']['regex']) {
                    $globalSearch[] = "`{$col}` '{$search}'";
                } else {
                    $globalSearch[] = "`{$col}` LIKE '%{$search}%'";
                }
            }
        }
        if (!empty($globalSearch)) {
            $globalSearchSql = implode(' OR ', $globalSearch);
            $where->literal("({$globalSearchSql})");
        }

        $columnSearch = [];
        foreach ($params['columns'] as $index => $column) {
            if ($column['search']['value']) {
//                $select->where->like($columns[$index], "%{$column['search']['value']}%");
                $columnSearch[] = "`{$columns[$index]}` LIKE '%{$column['search']['value']}%'";
            }
        }
        if (!empty($columnSearch)) {
            $columnSearchSql = implode(' AND ', $columnSearch);
            $where->literal("({$columnSearchSql})");
        }

        if ($whereSql) {
            $where->literal($whereSql);
        }
        $select->where($where);

        if (isset($params['order'])) {
            $arrayOrder = [];
            foreach ($params['order'] as $order) {
                $arrayOrder[$columns[$order['column']]] = $order['dir'];
            }
            $select->order($arrayOrder);

        }

//        die($select->getSqlString());
        $statement  = $sql->prepareStatementForSqlObject($select);
        $resultsSql = $statement->execute();
        $resultSet  = new ResultSet();
        $resultSet->initialize($resultsSql);
        $result['recordsFiltered'] = (int)$resultSet->count();

        $dbSelect  = new DbSelect($select, $this->adapterDb);
        $paginator = new Paginator($dbSelect);

        $paginator->setItemCountPerPage($params['length']);
        $paginator->setCurrentPageNumber(($params['start'] / $params['length']) + 1);

        $array = [];
        foreach ($paginator as $index => $data) {
            foreach ($data as $value) {
                $array[$index][] = $value;
            }

        }
        $result['data'] = $array;

        $this->data = $result;

        return $this;
    }

    public function getJson()
    {
        return json_encode($this->data);
    }

    public function getArray()
    {
        return $this->data;
    }

    public function getDistinctColumns($columns, $table, $optionHtml = false)
    {
        $array = [];
        $sql   = new Sql($this->adapterDb);
        foreach ($columns as $column) {
            $select = $sql->select()->from($table);
            $select->columns([$column => new Expression('DISTINCT(' . $column . ')')]);
            $statement  = $sql->prepareStatementForSqlObject($select);
            $resultsSql = $statement->execute();
            $resultSet  = new ResultSet();
            $resultSet->initialize($resultsSql);
            foreach ($resultSet as $value) {
                if ($value->$column !== null) {
                    $array[$column][] = $value->$column;
                }
            }

            sort($array[$column]);

            $array[$column] = $this->_generateSelectOptions($array[$column]);
        }

        return $array;
    }

    private function _generateSelectOptions($values)
    {
        $result = '';
        foreach ($values as $value) {
            $result .= "<option value='{$value}'>{$value}</option>";
        }

        return $result;
    }

    private function _extractColumns($params)
    {
        $result = [];
        foreach ($params['columns'] as $column) {
            if ($column['name']) {
                $result[] = $column['name'];
            }
        }

        return $result;
    }

}