<?php
/**
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
$userId = $_GET["userId"];
$start = $_GET["start"];
$end = $_GET["end"];

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

if(empty($userId) && empty($start) && empty($end)) {
    $error = new Error();
    $error->code = 204;
    $error->message = "Empty userId and start,end-Timestamp";
    echo "{\"error\":".json_encode($error)."}";
    return;
}

if(!empty($userId)) {

    $page = 0;
    $resultsAll = array();
    $query = ParseUser::query();
    $query->limit(1000);
    $query->ascending("createdAt");

    do {
        $results = $query->find();
        $resultsAll = array_merge($resultsAll, $results);
        $query->skip(1000*$page);
        $page++;
    } while(count($results) === 1000);

    $isExists = false;

    for($i = 0; $i < count($resultsAll); $i++) {
        $object = $resultsAll[$i];
        if($object->getObjectId() === $userId) {
            $isExists = true;
            break;
        }
    }

    if(!$isExists) {
        $error = new Error();
        $error->code = 202;
        $error->message = "Invalid userId";
        echo "{\"error\":".json_encode($error)."}";
        return;
    }
}

class Session {
    var $id;
    var $userId;
    var $description;
    var $startTimestamp;
    var $endTimestamp;
    var $name;
}

$query = new ParseQuery("CardioSession");
$query->limit(1000);
$query->ascending("createdAt");
if(!empty($userId)) {
    $query->equalTo("userId", $userId);
}
if(!empty($start)) {
    $query->greaterThan("startTimestamp", (int) $start - 1);
}
if(!empty($end)) {
    $query->lessThan("endTimestamp", (int) $end + 1);
}

$timestamp = strtotime('1970-03-17T14:49:54.741Z');
$createdAt = new DateTime('@'. $timestamp);

if(count($query->find()) == 0) {
    echo "Current user does not have any sessions";
    return;
}


echo "[";

do {

    if($number == 10000) {
        echo ",";
    }

    $number = 0;
    $page = 0;

    do {
        if ($page !== 0) {
            echo ",";
        }
        $query->skip(1000 * $page);
        $page++;

        $results = $query->find();
        for ($i = 0; $i < count($results); $i++) {
            $number++;
            $object = $results[$i];
            $session = new Session();
            $session->id = $object->getObjectId();
            $session->userId = $object->get("userId");
            $session->description = $object->get("description");
            $session->startTimestamp = $object->get("startTimestamp");
            $session->endTimestamp = $object->get("endTimestamp");
            $session->name = $object->get("name");
            $createdAt = $object->getCreatedAt();

            echo json_encode(get_object_vars($session));
            if ($i + 1 != count($results)) {
                echo ",";
            }
        }

        if ($page == 10) {
            break;
        }

    } while (count($results) === 1000);
    $query->greaterThan('createdAt', $createdAt);

} while($number == 10000);


echo "]";

?>