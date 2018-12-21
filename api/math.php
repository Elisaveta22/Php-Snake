<?php

header('Content-type: application/json'); // we will be communicating using json
require_once '../application/modules/db/db.php';

$request_object = (object)$_GET;

$method = $request_object->method;
$db = new DB();

if ($method == 'sum') {

    echo $request_object->num1 + $request_object->num2;
}
else if ($method == 'getUserById'){

    $id = $request_object->id;
    echo json_encode($db->getUserById($id));
}
else if($method == 'getAllUsers'){

    echo json_encode($db->getAllUsers());
}

