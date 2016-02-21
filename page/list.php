<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$substance = Utils::getUrlParam('substance');

$dao = new PainDao();
$search = new PainSearchCriteria();
$search->setStatus($substance);

// data for template
$title = Utils::capitalize($status) . ' Pains';
$todos = $dao->find($search);

