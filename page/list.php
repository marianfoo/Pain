<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$substance = Utils::getUrlParam('substance');

$dao = new PainDao();
$search = new PainSearchCriteria();
$search->setSubstance($substance);

// data for template

$pains = $dao->find($search);
$title = Utils::getSubstanceByGetId($substance);
$title = $title->getDescription();

