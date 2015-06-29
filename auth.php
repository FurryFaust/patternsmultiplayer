<?php

$username = $_GET['username'];
$password = $_GET['password'];

function checkValidity($str) {
    if (!empty($str)) {
        if (strlen($str) > 5 && strlen($str) < 16) {
            return true;
        }
    }
    return false;
}

if (checkValidity($username) && checkValidity($password)) {
    $PDO = new PDO('mysql:host=localhost;dbname=patterns', 'root', 'password');
    $sql = "select * from users where username=:username";
    $statement = $PDO->prepare($sql);
    $statement->bindParam(":username", $username);
    $statement->execute();

    if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
        $salt = $result['salt'];
        $encrypt = $result['password'];

        if (md5($password . $salt) == $encrypt) {
            print 'true';
        } else {
            print 'false - invalid credentials';
        }
    } else {
        print 'false - invalid credentials';
    }
} else {
    print 'false - invalid length';
}