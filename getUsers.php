<?php
/**
 * Created by PhpStorm.
 * User: IvanK
 * Date: 2/13/2015
 * Time: 4:33 PM
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

require 'php/autoload.php';

use Parse\ParseQuery;
use Parse\ParseClient;
use Parse\ParseUser;


class Error {
    var $code;
    var $message;
};

$token = $_GET["token"];

ParseClient::initialize( "m1bq6zc6M8nX626y45QfUaHuPuCjBTNHKWoo12u8", "z9IxAbrhXGneMsSeBTQGeqcauUgR9KekqznMyqtp", "6dUHrLiNOLQz4J1Uovul366LVdJnMWuUAYgqrwOI" );

$query = new ParseQuery("RequestForm");
$query->equalTo("token", $token);
$results = $query->find();
if(count($results) == 0) {
    $error = new Error();
    $error->code = 201;
    $error->message = "Invalid token";
    echo "{\"error\":".json_encode($error)."}";
    return;
}

ParseClient::initialize("SSzU4YxI6Z6SwvfNc2vkZhYQYl86CvBpd3P2wHF1", "pKDap5jqe7lyBG5vTRgvTz7t8AiRWXpMYbuS2oak", "80ah2J605oswPn4WFb8OriGaHywjZ46wpoa9rHzQ");

$resultsAll = array();
$query = ParseUser::query();
$query->limit(1000);
$query->ascending("createdAt");

class User {
    var $id;
    var $email;
}

$page = 0;

echo "[";
do {

    if($page !== 0) {
        echo ",";
    }
    $page++;

    $results = $query->find();
    for($i = 0; $i < count($results); $i++) {
        $object = $results[$i];
        $user = new User();
        $user->id = $object->getObjectId();
        $user->email = $object->get('username');
        echo json_encode(get_object_vars($user));
        if($i+1 != count($results)) {
            echo ",";
        }
    }

    if($page === 10) {
        break;
    }
    $query->skip(1000*$page);
} while(count($results) === 1000);
echo "]";

?>