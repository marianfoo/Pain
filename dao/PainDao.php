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
            PainMapper::map($pain, $row);
            $result[$pain->getId()] = $pain;
        }
        return $result;
    }
    public function findPainById($id) {
        $row = $this->query('SELECT * FROM pain WHERE deleted = 0 and id = ' . (int) $id)->fetch();
        if (!$row) {
            return null;
        }
        $pain = new Pain();
        PainMapper::map($pain, $row);
        return $pain;
    }
    public function findSubstanceById($id) {
        $row = $this->query('SELECT * FROM substance WHERE id = ' . (int) $id)->fetch();
        if (!$row) {
            return null;
        }
        $substance = new Substance();
        SubstanceMapper::map($substance, $row);
        return $substance;
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
    public function findPainsPerWeekday() {
        $sql = 'SELECT dayname(happen),count(id) from pain where substance = 1 group by dayname(happen) order by weekday(happen) ';
        $result = $this->query($sql)->fetchAll();
        return $result;
    }
    public function findPainsPerWeek() {
        $sql = 'SELECT week(happen,3) week,substr(yearweek(happen,3),1,4) year,count(id) count,sum(quantity*amount) as intake,
                truncate(sum(quantity*amount)/count(id),0) as averageintake
                from pain
                where substance = 1
                group by week,year
                order by year desc,week desc';
        $result = $this->query($sql)->fetchAll();
        return $result;
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
        $orderBy = ' substance, happen';
        if ($search !== null) {
            if ($search->getSubstance() !== null) {
                $sql .= 'AND substance = ' . $this->getDb()->quote($search->getSubstance());
                switch ($search->getSubstance()) {
                    case Pain::IBUPROPHEN:
                        $orderBy = 'happen DESC';
                        break;
                    case Pain::MIRTAZAPIN:
                        $orderBy = 'happen DESC';
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
        $pain->setLastModifiedOn($now);
        $sql = "\n"
            . "INSERT INTO `pain` (`id`, `created_on`, `last_modified_on`, `comment`, `trigger`, `amount`, `pain`, `substance`, `quantity`, `deleted`,`happen`)\n"
            . "VALUES (:id, :created_on, :last_modified_on, :comment, :trigger, :amount, :pain, :substance, :quantity, :deleted, :happen)";
        return $this->execute($sql, $pain);
    }
    private function update(Pain $pain){
        $pain->setLastModifiedOn(new DateTime());
        $sql = "
            UPDATE pain SET
                `last_modified_on` = :last_modified_on,
                `comment` = :comment,
                `trigger` = :trigger,
                `amount` = :amount,
                `pain` = :pain,
                `substance` = :substance,
                `quantity` = :quantity,
                `deleted` = :deleted,
                `happen` = :happen
            WHERE
                id = :id";
        return $this->execute($sql, $pain);
    }
    private function execute($sql, Pain $pain) {
        $statement = $this->getDb()->prepare($sql);
        $this->executeStatement($statement, $this->getParams($pain));
        if (!$pain->getId()) {
            return $this->findPainById($this->getDb()->lastInsertId());
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
            ':deleted' => $pain->getDeleted(),
            ":happen" => self::formatDateTime($pain->getHappen())
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

