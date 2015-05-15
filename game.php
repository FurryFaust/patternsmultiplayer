<?php

$username = $_GET['username'];
$password = $_GET['password'];
$gameid = $_GET['gameid'];

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
    $auth = $PDO->prepare($sql);
    $auth->bindParam(':username', $username);
    $auth->bindParam(':password', $password);
    $auth->execute();
    if ($result = $auth->fetch(PDO::FETCH_ASSOC)) {
        $ids = explode(",", $gameid);

        foreach ($ids as $id) {
            if (strpos($result['games'], $id) !== false) {
                $sql = "select * from games where id=:gameid";
                $query = $PDO->prepare($sql);
                $query->bindParam(':gameid', $id);
                $query->execute();

                if ($games = $query->fetch(PDO::FETCH_ASSOC)) {
                    print $id . "|" . $games['players'] . "|" . $games['expiry'];
                    print '<br>';
                } else {
                    print 'false - invalid game id';
                    break;
                }
            } else {
                print 'false - invalid game authority';
                break;
            }
        }
    } else {
        print 'false - invalid credentials';
    }
} else {
    print 'false - invalid credentials length';
}