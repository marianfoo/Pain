<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
final class PainDao {
    
    private $db = null;
    
    public function __destruct() {
        // close db connection
        $this->db = null;
    }
    public function find(PainSearchCriteria $search = null) {
        $result = array();
        foreach ($this->query($this->getFindSql($search)) as $row) {
            $pain = new Pain();
            Painmapper::map($pain, $row);
            $result[$pain->getId()] = $pain;
        }
        return $result;
    }
    public function findById($id) {
        $row = $this->query('SELECT * FROM pain WHERE deleted = 0 and id = ' . (int) $id)->fetch();
        if (!$row) {
            return null;
        }
        $pain = new Pain();
        Painmapper::map($pain, $row);
        return $pain;
    }
    public function save(Pain $pain) {
        if ($pain->getId() === null) {
            return $this->insert($pain);
        }
        return $this->update($pain);
    }
    public function delete($id) {
        $sql = '
            UPDATE pain SET
                last_modified_on = :last_modified_on,
                deleted = :deleted
            WHERE
                id = :id';
        $statement = $this->getDb()->prepare($sql);
        $this->executeStatement($statement, array(
            ':last_modified_on' => self::formatDateTime(new DateTime()),
            ':deleted' => true,
            ':id' => $id,
        ));
        return $statement->rowCount() == 1;
    }
    private function getDb() {
        if ($this->db !== null) {
            return $this->db;
        }
        $config = Config::getConfig("db");
        try {
            $this->db = new PDO($config['dsn'], $config['username'], $config['password']);
        } catch (Exception $ex) {
            throw new Exception('DB connection error: ' . $ex->getMessage());
        }
        return $this->db;
    }
    private function getFindSql(PainSearchCriteria $search = null) {
        $sql = 'SELECT * FROM pain WHERE deleted = 0 ';
        $orderBy = ' substance, created_on';
        if ($search !== null) {
            if ($search->getSubstance() !== null) {
                $sql .= 'AND substance = ' . $this->getDb()->quote($search->getSubstance());
                switch ($search->getSubstance()) {
                    case Pain::IBUPROPHEN:
                        $orderBy = 'created_on DESC';
                        break;
                    case Pain::MIRTAZAPIN:
                        $orderBy = 'created_on DESC';
                        break;
                    default:
                        throw new Exception('No order for substance: ' . $search->getSubstance());
                }
            }
        }
        $sql .= ' ORDER BY ' . $orderBy;
        return $sql;
    }
        /**
     * @return Pain
     * @throws Exception
     */
    private function insert(Pain $pain) {
        $now = new DateTime();
        $pain->setId(null);
        $pain->setCreatedOn($now);
        $pain->setLastModifiedOn($now);
        $sql = '
            INSERT INTO pain (id, created_on, last_modified_on, comment, trigger, amount, pain, substance, quantity, deleted)
                VALUES (:id, :created_on, :last_modified_on, :comment, :trigger, :amount, :pain, :substance, :quantity, :deleted)';
        return $this->execute($sql, $pain);
    }
    private function update(Pain $pain){
        $pain->setLastModifiedOn(new DateTime());
        $sql = '
            UPDATE pain SET
                last_modified_on = :last_modified_on,
                comment = :comment,
                trigger = :trigger,
                amount = :amount,
                pain = :pain,
                substance = :substance,
                quantity = :quantity,
                deleted = :deleted
            WHERE
                id = :id';
        return $this->execute($sql, $pain);
    }
    private function execute($sql, Pain $pain) {
        $statement = $this->getDb()->prepare($sql);
        $this->executeStatement($statement, $this->getParams($pain));
        if (!$pain->getId()) {
            return $this->findById($this->getDb()->lastInsertId());
        }
        if (!$statement->rowCount()) {
            throw new NotFoundException('PAIN with ID "' . $pain->getId() . '" does not exist.');
        }
        return $pain;
    }
    private function getParams(Pain $pain) {
        $params = array(
            ':id' => $pain->getId(),
            ':created_on' => self::formatDateTime($pain->getCreatedOn()),
            ':last_modified_on' => self::formatDateTime($pain->getLastModifiedOn()),
            ':comment' => $pain->getComment(),
            ':trigger' => $pain->getTrigger(),
            ':amount' => $pain->getAmount(),
            ':pain' => $pain->getPain(),
            ':substance' => $pain->getSubstance(),
            ':quantity' => $pain->getQuantity(),
            ':deleted' => $pain->getDeleted()
        );
        if ($pain->getId()) {
            // unset created date, this one is never updated
            unset($params[':created_on']);
        }
        return $params;
    }
    private function executeStatement(PDOStatement $statement, array $params) {
        if (!$statement->execute($params)) {
            self::throwDbError($this->getDb()->errorInfo());
        }
    }
    private function query($sql) {
        $statement = $this->getDb()->query($sql, PDO::FETCH_ASSOC);
        if ($statement === false) {
            self::throwDbError($this->getDb()->errorInfo());
        }
        return $statement;
    }
    private static function throwDbError(array $errorInfo) {
        // Pain log error, send email, etc.
        throw new Exception('DB error [' . $errorInfo[0] . ', ' . $errorInfo[1] . ']: ' . $errorInfo[2]);
    }

    private static function formatDateTime(DateTime $date) {
        return $date->format(DateTime::ISO8601);
    }
}

