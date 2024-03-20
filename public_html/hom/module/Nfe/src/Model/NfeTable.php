<?php


namespace Nfe\Model;

use Zend\Db\TableGateway\Exception\RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class NfeTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select("id > 400");
    }

    public function save(Nfe $nfe)
    {
        $data = [
            'cfop'          => $nfe->cfop,
            'naturezaop'    => $nfe->naturezaop
        ];

        $id = (int)$nfe->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return;
        }

        if (!$this->find($id)) {
            throw new RuntimeException(sprintf(
                'Could not retrieve the row %d', $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
    }

    public function find($id)
    {
        $id = (int)$id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not retrieve the row %d', $id
            ));
        }

        return $row;
    }

    public function delete($id)
    {
        $this->tableGateway->delete(['id' => (int)$id]);
    }

}