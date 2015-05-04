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
    $sql = "select * from users where username=:username and password=:password";
    $statement = $PDO->prepare($sql);
    $statement->bindParam(":username", $username);
    $statement->bindParam(":password", $password);

    $statement->execute();

    if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
        print 'true';
    } else {
        print 'false - invalid credentials';
    }
} else {
    print 'false - invalid length';
}