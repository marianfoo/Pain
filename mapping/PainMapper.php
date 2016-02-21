<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
final class Painmapper {
    
    private function __construct() {
        ;
    }
    public static function map(Pain $pain, array $properties){
        if (array_key_exists('id', $properties)){
            $pain->setId($properties['id']);
        }
        if (array_key_exists('created_on', $properties)) {
            $createdOn = self::createDateTime($properties['created_on']);
            if ($createdOn) {
                $pain->setCreatedOn($createdOn);
            }
        }
        if (array_key_exists('last_modified_on', $properties)) {
            $lastModifiedOn = self::createDateTime($properties['last_modified_on']);
            if ($lastModifiedOn) {
                $pain->setLastModifiedOn($lastModifiedOn);
            }
        }
        if (array_key_exists('comment', $properties)) {
            $pain->setComment(($properties['comment']));
        }
        if (array_key_exists('trigger', $properties)) {
            $pain->setTrigger(($properties['trigger']));
        }
        if (array_key_exists('amount', $properties)) {
            $pain->setAmount($properties['amount']);
        }
        if (array_key_exists('quantity', $properties)) {
            $pain->setQuantity($properties['quantity']);
        }
        
    }
    private static function createDateTime($input) {
        return DateTime::createFromFormat('Y-n-j H:i:s', $input);
    }
    
}

