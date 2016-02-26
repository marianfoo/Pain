<?php
/**
 * Created by PhpStorm.
 * User: marianbauersachs
 * Date: 26/02/16
 * Time: 12:08
 */
$dao = new PainDao();
$rows = $dao->findPainsPerWeekday();
$weekrows = $dao->findPainsPerWeek();