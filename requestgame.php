<?php

$username = $_GET['username'];
$password = $_GET['password'];
$challenged = $_GET['challenged'];
$difficulty = $_GET['difficulty'];

function checkValidity($str) {
    if (!empty($str)) {
        if (strlen($str) > 5 && strlen($str) < 16) {
            return true;
        }
    }
    return false;
}

if ($difficulty == "0" || $difficulty == "1") {
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
            if ($result = $query->fetch(PDO::FETCH_ASSOC) || $username == $challenged) {
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
                    while ($set == false) {
                        if ($array[$pointerX][$pointerY] == 0) {
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

                $scrambler = ($difficulty == "0" ? 50 : 10000);

                function getEmptySlotX($array) {
                    for ($i = 0; $i != 4; $i++) {
                        for ($j = 0; $j != 4; $j++) {
                            if ($array[$i][$j] == 0) {
                                return $i;
                            }
                        }
                    }
                    return 0;
                }

                function getEmptySlotY($array) {
                    for ($i = 0; $i != 4; $i++) {
                        for ($j = 0; $j != 4; $j++) {
                            if ($array[$i][$j] == 0) {
                                return $j;
                            }
                        }
                    }
                    return 0;
                }

                for ($i = 0; $i != $scrambler; $i++) {
                    $rand = rand(0, 3);
                    $emptyX = getEmptySlotX($array);
                    $emptyY = getEmptySlotY($array);
                    switch ($rand) {
                        case 0:
                            if ($emptyY != 3) {
                                $array[$emptyX][$emptyY] = $array[$emptyX][$emptyY + 1];
                                $array[$emptyX][$emptyY + 1] = 0;
                            }
                            break;
                        case 1:
                            if ($emptyY != 0) {
                                $array[$emptyX][$emptyY] = $array[$emptyX][$emptyY - 1];
                                $array[$emptyX][$emptyY - 1] = 0;
                            }
                            break;
                        case 2:
                            if ($emptyX != 0) {
                                $array[$emptyX][$emptyY] = $array[$emptyX - 1][$emptyY];
                                $array[$emptyX - 1][$emptyY] = 0;
                            }
                            break;
                        case 3:
                            if ($emptyX != 3) {
                                $array[$emptyX][$emptyY] = $array[$emptyX + 1][$emptyY];
                                $array[$emptyX + 1][$emptyY] = 0;
                            }
                            break;
                    }
                }

                $str = "";
                for ($i = 0; $i != 4; $i++) {
                    for ($j = 0; $j != 4; $j++) {
                        $str .= ($array[$j][$i] . " ");
                    }
                }

                $gameID = intval($PDO->query("select count(*) from games")->fetch(PDO::FETCH_ASSOC)['count(*)']) + 1;

                $sql = "insert into games(id, board, players, expiry) values (:id, :board, :players, :expiry)";
                $create = $PDO->prepare($sql);
                $create->bindParam(':id', $gameID);
                $create->bindParam(':board', $str);
                $players = $username . "-" . strtotime("now"). "-{end}-{moves} "
                            . $challenged . "-{start}-{end}-{moves}";
                $create->bindParam(':players', $players);
                $expiry = strtotime("+ 1 day");
                $create->bindParam(':expiry', $expiry);
                $create->execute();

                $sql = "select games from users where username=:username";
                $hostQuery = $PDO->prepare($sql);
                $hostQuery->bindParam(':username', $username);
                $hostQuery->execute();
                $hostGames = $hostQuery->fetch(PDO::FETCH_ASSOC)['games'];

                $hostGames = $hostGames == "" ? $gameID : $hostGames . "-" . $gameID;

                $sql = "update users set games=:games where username=:username";
                $updateHost = $PDO->prepare($sql);
                $updateHost->bindParam(':games', $hostGames);
                $updateHost->bindParam(':username', $username);
                $updateHost->execute();

                $sql = "select games from users where username=:challenged";
                $challengedQuery = $PDO->prepare($sql);
                $challengedQuery->bindParam(':challenged', $challenged);
                $challengedQuery->execute();
                $challengedGames = $challengedQuery->fetch(PDO::FETCH_ASSOC)['games'];

                $challengedGames = $challengedGames == "" ? $gameID : $challengedGames . "-" . $gameID;

                $sql = "update users set games=:games where username=:challenged";
                $updateChallenged = $PDO->prepare($sql);
                $updateChallenged->bindParam(':games', $challengedGames);
                $updateChallenged->bindParam(':challenged', $challenged);
                $updateChallenged->execute();

                print $str;
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
