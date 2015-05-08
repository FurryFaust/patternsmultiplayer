<?php

$username = $_GET['username'];
$password = $_GET['password'];
$gameID = $_GET['gameid'];

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

    if ($results = $auth->fetch(PDO::FETCH_ASSOC)) {
        $sql = "select * from games where id=:gameID";
        $queryGame = $PDO->prepare($sql);
        $queryGame->bindParam(':gameID', $gameID);
        $queryGame->execute();

        if ($results = $queryGame->fetch(PDO::FETCH_ASSOC)) {
            if (intval(strtotime("now")) < intval($results['expiry'])) {
                $player = explode(" ", $results['players'])[1];
                if (substr($player, 0, strlen($username)) == $username) {
                    if (strpos($player, "{start}") !== false) {
                        $players = str_replace("{start}", strtotime("now"), $results['players']);
                        $PDO->query("update games set players='" . $players . "' where id=" . $gameID);
                        print 'true - {' . $results['board'] . "}";
                    } else {
                        print 'false - game already started';
                    }
                } else {
                    print 'false - invalid game id';
                }
            } else {
                print 'false - expired game';
            }
        } else {
            print 'false - invalid game id';
        }
    } else {
        print 'false - invalid credential';
    }
} else {
    print 'false - invalid credential length';
}
