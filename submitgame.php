<?php

$username = $_GET['username'];
$password = $_GET['password'];
$gameID = $_GET['gameid'];
$moves = $_GET['moves'];

function checkValidity($str)
{
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
        $sql = "select * from games where id=:id";
        $gameQuery = $PDO->prepare($sql);
        $gameQuery->bindParam(':id', $gameID);
        $gameQuery->execute();
        if ($result = $gameQuery->fetch(PDO::FETCH_ASSOC)) {
            if (intval(strtotime("now")) < intval($result['expiry'])) {
                if (strpos($result['players'], $username) !== false) {
                    preg_match('#' . $username . '-\d+-{end}-{moves}#', $result['players'], $index, PREG_OFFSET_CAPTURE);
                    if (!empty($index)) {
                        $boardStr = explode(" ", $result['board']);
                        $board = [
                            [$boardStr[0], $boardStr[1], $boardStr[2], $boardStr[3]],
                            [$boardStr[4], $boardStr[5], $boardStr[6], $boardStr[7]],
                            [$boardStr[8], $boardStr[9], $boardStr[10], $boardStr[11]],
                            [$boardStr[12], $boardStr[13], $boardStr[14], $boardStr[15]]
                        ];

                        function getEmptySlotX($array)
                        {
                            for ($i = 0; $i != 4; $i++) {
                                for ($j = 0; $j != 4; $j++) {
                                    if ($array[$i][$j] == 0) {
                                        return $i;
                                    }
                                }
                            }
                            return 0;
                        }

                        function getEmptySlotY($array)
                        {
                            for ($i = 0; $i != 4; $i++) {
                                for ($j = 0; $j != 4; $j++) {
                                    if ($array[$i][$j] == 0) {
                                        return $j;
                                    }
                                }
                            }
                            return 0;
                        }

                        if ($moves !== "") {
                            $moves = explode("|", $moves);
                            for ($i = 0; $i != sizeof($moves); $i++) {
                                $emptyX = getEmptySlotX($board);
                                $emptyY = getEmptySlotY($board);
                                switch (intval($moves[$i])) {
                                    case 3:
                                        if ($emptyY != 3) {
                                            $board[$emptyX][$emptyY] = $board[$emptyX][$emptyY + 1];
                                            $board[$emptyX][$emptyY + 1] = 0;
                                        }
                                        break;
                                    case 2:
                                        if ($emptyY != 0) {
                                            $board[$emptyX][$emptyY] = $board[$emptyX][$emptyY - 1];
                                            $board[$emptyX][$emptyY - 1] = 0;
                                        }
                                        break;
                                    case 1:
                                        if ($emptyX != 0) {
                                            $board[$emptyX][$emptyY] = $board[$emptyX - 1][$emptyY];
                                            $board[$emptyX - 1][$emptyY] = 0;
                                        }
                                        break;
                                    case 0:
                                        if ($emptyX != 3) {
                                            $board[$emptyX][$emptyY] = $board[$emptyX + 1][$emptyY];
                                            $board[$emptyX + 1][$emptyY] = 0;
                                        }
                                        break;
                                }
                            }
                        }

                        $winningBoard = [
                            [1, 2, 3, 4],
                            [12, 13, 14, 5],
                            [11, 0, 15, 6],
                            [10, 9, 8, 7]
                        ];

                        print "<br>";

                        for ($i = 0; $i != 4; $i++) {
                            for ($j = 0; $j != 4; $j++) {
                                print (string)$winningBoard[$i][$j] . ($winningBoard[$i][$j] > 9 ? " " : "  ");
                            }
                            print "<br>";
                        }

                        print "<br>";

                        for ($i = 0; $i != 4; $i++) {
                            for ($j = 0; $j != 4; $j++) {
                                print (string)$board[$i][$j] . ($board[$i][$j] > 9 ? " " : "  ");
                            }
                            print "<br>";
                        }

                        function check($board, $winningBoard)
                        {
                            for ($i = 0; $i != 4; $i++) {
                                for ($j = 0; $j != 4; $j++) {
                                    if ($board[$i][$j] != $winningBoard[$i][$j]) {
                                        return false;
                                    }
                                }
                            }
                            return true;
                        }

                        if (check($board, $winningBoard)) {
                            $players = explode(" ", $result['players']);
                            $player = $index[0][1] == 0 ? $players[0] : $players[1];
                            $player = str_replace("{end}", strtotime("now"), $player);
                            $player = str_replace("{moves}", count($moves), $player);
                            $players = str_replace($index[0][0], $player, $result['players']);
                            $sql = "update games set players='" . $players . "' where id=:id";
                            $update = $PDO->prepare($sql);
                            $update->bindParam(':id', $gameID);
                            $update->execute();
                            print 'true';
                        } else {
                            print 'false - fake request';
                        }
                    } else {
                        print 'false - game already submitted';
                    }
                } else {
                    print 'false - invalid game id';
                }
            } else {
                print 'false - game expired';
            }
        } else {
            print 'false - invalid game id';
        }
    } else {
        print 'false - invalid credentials';
    }

} else {
    print 'false - invalid credentials length';
}