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

if(checkValidity($username) && checkValidity($password)) {
    $PDO = new PDO('mysql:host=localhost;dbname=patterns', 'root', 'password');
    $sql = "select * from users where username=:username";
    $check = $PDO->prepare($sql);
    $check->bindParam(':username', $username);
    $check->execute();

    if ($result = $check->fetch(PDO::FETCH_ASSOC)) {
        print 'false - username unavailable';
    } else {
        $sql = "insert into users(username, password) values (:username, :password)";
        $create = $PDO->prepare($sql);
        $create->bindParam(':username', $username);
        $create->bindParam(':password', $password);
        $create->execute();
        print 'true';
    }

} else {
    print 'false - invalid length';
}