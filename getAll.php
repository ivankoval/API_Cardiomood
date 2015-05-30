<?php
/**
 * Created by PhpStorm.
 * User: IvanK
 * Date: 3/17/2015
 * Time: 8:33 PM
 */

require 'php/autoload.php';

use Parse\ParseQuery;
use Parse\ParseClient;


ParseClient::initialize("SSzU4YxI6Z6SwvfNc2vkZhYQYl86CvBpd3P2wHF1", "pKDap5jqe7lyBG5vTRgvTz7t8AiRWXpMYbuS2oak", "80ah2J605oswPn4WFb8OriGaHywjZ46wpoa9rHzQ");

$query = new ParseQuery("CardioSession");
$query->descending("createdAt");
$query->limit(1000);
for($i = 0; $i < 5; $i++) {
    $skip = 0;
    while($skip < 10) {
        $query->skip(1000 * $skip);
        $skip++;
        $results = $query->find();
        echo count($results)." ";
    }
    $obj = end($results);
    $createdAt = $obj->getCreatedAt();
    $query->lessThan("createdAt", $createdAt);
}

?>

