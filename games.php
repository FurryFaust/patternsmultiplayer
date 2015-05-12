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
    $sql = "select * from users where username=:username and password=:password";
    $auth = $PDO->prepare($sql);
    $auth->bindParam(':username', $username);
    $auth->bindParam(':password', $password);
    $auth->execute();

    if ($result = $auth->fetch(PDO::FETCH_ASSOC)) {
        print $result['games'];
    } else {
        print 'false - invalid credentials';
    }
} else {
    print 'false - invalid credentials length';
}