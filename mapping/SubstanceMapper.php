<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
final class SubstanceMapper {
    
    private function __construct() {
        ;
    }
    public static function map(Substance $substance, array $properties){
        if (array_key_exists('id', $properties)){
            $substance->setId($properties['id']);
        }
        if (array_key_exists('description', $properties)){
            $substance->setDescription($properties['description']);
        }
        if (array_key_exists('shortdescr', $properties)){
            $substance->setShortdescr($properties['shortdescr']);
        }

        
    }
    private static function createDateTime($input) {
        return DateTime::createFromFormat('Y-n-j H:i:s', $input);
    }
    
}

