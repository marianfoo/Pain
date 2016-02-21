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
}

