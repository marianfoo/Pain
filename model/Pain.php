<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

final class Pain {
    const PAINARRAY = array(1,2,3,4,5,6,7,8,9,10);
    
    const IBUPROPHEN = "1";
    const MIRTAZAPIN = "2";

    const UNI = "Uni";
    const SCHLAF = "schlaf";

    
    private $id;
    private $createdOn;
    private $lastModifiedOn;
    private $comment;
    private $trigger;
    private $amount;
    private $quantity;
    private $substance;
    private $pain;
    private $happen;

    /**
     * @return mixed
     */
    public function getHappen()
    {
        return $this->happen;
    }

    /**
     * @param mixed $happen
     */
    public function setHappen($happen)
    {
        $this->happen = $happen;
    }


    
    
    public function __construct() {
        $now = new DateTime();
        $this->setCreatedOn($now);
        $this->setLastModifiedOn($now);
        $this->setDeleted(false);
    }
    
    public static function allSubstances() {
        return array(
            self::IBUPROPHEN,
            self::MIRTAZAPIN,
        );
    }
    
    public function setId($id) {
        if ($this->id !== null && $this->id != $id) {
            throw new Exception('Cannot change identifier to ' . $id . ', already set to ' . $this->id);
        }
        $this->id = (int) $id;
    }

    public function getCreatedOn() {
        return $this->createdOn;
    }

    public function getLastModifiedOn() {
        return $this->lastModifiedOn;
    }

    public function getComment() {
        return $this->comment;
    }

    public function getTrigger() {
        return $this->trigger;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getId() {
        return $this->id;
    }
    public function getPain(){
        return $this->pain;
    }
    public function getSubstance(){
        return $this->substance;
    }

    public function setCreatedOn($createdOn) {
        $this->createdOn = $createdOn;
    }

    public function setLastModifiedOn($lastModifiedOn) {
        $this->lastModifiedOn = $lastModifiedOn;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

    public function setTrigger($trigger) {
        $this->trigger = $trigger;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
    }
    public function setPain($pain){
        $this->pain = $pain;
    }
    public function setSubstance($substance){
        $this->substance = $substance;
    }
    public function getDeleted() {
        return $this->deleted;
    }

    public function setDeleted($deleted) {
        $this->deleted = (bool) $deleted;
    }



        
    
}

