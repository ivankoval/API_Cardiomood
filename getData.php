<?php
/**
 * Created by PhpStorm.
 * User: IvanK
 * Date: 2/18/2015
 * Time: 2:24 PM
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');


require 'php/autoload.php';

use Parse\ParseQuery;
use Parse\ParseClient;


class Error {
    var $code;
    var $message;
};


$token = $_GET["token"];
$sessionId = $_GET["sessionId"];

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

$query = new ParseQuery("CardioSession");
$query->limit(1000);
$query->equalTo("objectId", $sessionId);
$results = $query->find();

if(count($results) === 0) {
    $error = new Error();
    $error->code = 203;
    $error->message = "Invalid sessionId";
    echo "{\"error\":".json_encode($error)."}";
    return;
}

class DataChunk {
    var $t;
    var $r;
}

$query = new ParseQuery("CardioDataChunk");
$query->limit(1000);
$query->ascending("number");
$query->equalTo("sessionId", $sessionId);
$results = $query->find();

echo "[";

for($i = 0; $i < count($results); $i++) {
    $object = $results[$i];
    for($t = 0; $t < count($object->get("times")); $t++) {
        $dataChunk = new DataChunk();
        $dataChunk->t = $object->get("times")[$t];
        if($dataChunk->t === null) {
            $dataChunk->t = 0;
        }
        $dataChunk->r = $object->get("rrs")[$t];
        echo json_encode(get_object_vars($dataChunk));
        if (($i + 1 != count($results))||($t + 1 != count($object->get("times")))) {
            echo ",";
        }

    }
}
echo "]";

?>