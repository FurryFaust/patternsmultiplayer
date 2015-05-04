<?php

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$username = $_GET['username'];
$password = $_GET['password'];
$challenged = $_GET['challenged'];
$difficulty = $_GET['difficulty'];

function checkValidity($str){
    if (!empty($str)) {
        if (strlen($str) > 5 && strlen($str) < 16) {
            return true;
        }
    }
    return false;
}

if($difficulty == "0" || $difficulty == "1") {
    if (checkValidity($username) && checkValidity($password)) {
        $PDO = new PDO('mysql:host=localhost;dbname=patterns', 'root', 'password');
        $sql = "select * from users where username=:username and password=:password";
        $auth = $PDO->prepare($sql);
        $auth->bindParam(':username', $username);
        $auth->bindParam(':password', $password);
        $auth->execute();
        if ($result = $auth->fetch(PDO::FETCH_ASSOC)) {
            $sql = "select * from users where username=:challenged";
            $query = $PDO->prepare($sql);
            $query->bindParam(':challenged', $challenged);
            $query->execute();
            if ($result = $query->fetch(PDO::FETCH_ASSOC)) {
                for ($i = 0; $i != 4; $i++) {
                    for ($j = 0; $j != 4; $j++) {
                        $array[$i][$j] = 0;
                    }
                }

                $pointerX = 0;
                $pointerY = 0;
                $direction = 0;

                for ($count = 0; $count != 16; $count++) {
                    $set = false;
                    while($set == false) {
                        if($array[$pointerX][$pointerY] == 0) {
                            $array[$pointerX][$pointerY] = $count;
                            $set = true;
                        } else {
                            switch ($direction) {
                                case 0:
                                    if ($pointerX - 1 < 0 || $array[$pointerX - 1][$pointerY] != 0) {
                                        $direction++;
                                    } else {
                                        $pointerX--;
                                        break;
                                    }
                                case 1:
                                    if ($pointerX + 1 > 3 || $array[$pointerX + 1][$pointerY] != 0) {
                                        $direction++;
                                    } else {
                                        $pointerX++;
                                        break;
                                    }
                                case 2:
                                    if ($pointerY - 1 < 0 || $array[$pointerX][$pointerY - 1] != 0) {
                                        $direction++;
                                    } else {
                                        $pointerY--;
                                        break;
                                    }
                                case 3:
                                    if ($pointerY + 1 > 3 || $array[$pointerX][$pointerY + 1] != 0) {
                                        $direction = 0;
                                    } else {
                                        $pointerY++;
                                        break;
                                    }
                            }
                        }
                    }
                }

                for ($i = 0; $i != 4; $i++) {
                    for ($j = 0; $j != 4; $j++) {
                        print (string) $array[$i][$j] . " ";
                    }
                }
            } else {
                print 'false - invalid invitation';
            }
        } else {
            print 'false - invalid credentials';
        }
    } else {
        print 'false - invalid credential length';
    }
} else {
    print 'false - invalid difficulty';
}
