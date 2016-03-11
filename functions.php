<?php
date_default_timezone_set('Asia/Shanghai');
function curl($url, $referer="", $useragent="", $header=array(), $post=0, $post_data="") {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 3);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($curl, CURLOPT_REFERER, $referer);
    curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
    if ($post==1) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }
    $src = curl_exec($curl);
    curl_close($curl);
    return $src;
}
function sqlInit($sqlInfo) {
    $conn = mysqli_connect($sqlInfo["host"], $sqlInfo["user"], $sqlInfo["pwd"], $sqlInfo["db"]);
    if (mysqli_connect_errno($conn)) {
        echo "连接到 MySQL 服务器失败：" . mysqli_connect_error();
        return False;
    }
    mysqli_query($conn, "CREATE TABLE time(ID INT Unique NOT NULL AUTO_INCREMENT, time INT)");
    mysqli_query($conn, "CREATE TABLE storage(ID INT Unique NOT NULL AUTO_INCREMENT, steamid TEXT, pic TEXT)");
    mysqli_close($conn);
    return True;
}
function sqlExec($sqlInfo, $command) {
    $conn = mysqli_connect($sqlInfo["host"], $sqlInfo["user"], $sqlInfo["pwd"], $sqlInfo["db"]);
    if (mysqli_connect_errno($conn)) {
        echo "连接到 MySQL 服务器失败：" . mysqli_connect_error();
        return False;
    }
    $result = mysqli_query($conn, $command);
    mysqli_close($conn);
    return $result;
}
function getDataById($sqlInfo, $steamid) {
    $Data = sqlExec($sqlInfo, "SELECT * FROM storage WHERE steamid='{$steamid}';");
    if ($Data == False) {return False;}
    $oldData = array();
    while ($row = mysqli_fetch_array($Data)) {
        return $row;
    }
}
function getUserBase($key, $steamid) {
    $baseurl = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$key}&steamids={$steamid}";
    $userInfo = json_decode(curl($baseurl), 'ignore');
    $userInfo = $userInfo["response"]["players"][0];
    $name = $userInfo["personaname"];
    $head = $userInfo["avatarfull"];
    return array($name, $head);
}
function getRecentGame($key, $steamid) {
    $baseurl = "http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key={$key}&steamid={$steamid}&format=json";
    $rGameInfo = json_decode(curl($baseurl), 'ignore');
    $rGameInfo = $rGameInfo["response"]["games"];
    $rGameIds = array();
    for($i=0;$i<3;$i++) {
        $rGameIds[] = $rGameInfo[$i]["appid"];
    }
    return $rGameIds;
}
function getMoreData($key, $steamid) {
    $baseurl = "http://api.steampowered.com/IPlayerService/GetBadges/v0001/?key={$key}&steamid={$steamid}&format=json";
    $dataInfo = json_decode(curl($baseurl), 'ignore');
    $dataInfo = $dataInfo["response"];
    $level = $dataInfo["player_level"];
    $dataInfo = $dataInfo["badges"];
    foreach ($dataInfo as &$badge) {
        if ($badge["badgeid"]==13) {
            $gameCount = $badge["level"];
        }
    }
    return array($level, $gameCount);
}
function getUserData($key, $steamid) {
    $userBase = getUserBase($key, $steamid);
    $moreData = getMoreData($key, $steamid);
    $recentGame = getRecentGame($key, $steamid);
    return array($userBase[0], $moreData[0], $moreData[1], $userBase[1], $recentGame[0], $recentGame[1], $recentGame[2]);
}
function getTxtSize($ttf, $text, $size) {
    $tmpSize = imagettfbbox($size, 0, $ttf, $text);
    return array($tmpSize[4] - $tmpSize[6], $tmpSize[1] - $tmpSize[7]);
}
function makeSign($userInfo, $imgBig=1) {
    $username = $userInfo[0];
    $level = $userInfo[1];
    $num = $userInfo[2];
    $headurl = $userInfo[3];
    $rGame1 = $userInfo[4];
    $rGame2 = $userInfo[5];
    $rGame3 = $userInfo[6];

    //init image size.
    if ($imgBig==1) {
        $imgWidth = 675;
        $imgHeight = 200;
    }
    else {
        $imgWidth = 635;
        $imgHeight = 150;
    }
    
    //create background image.
    $bgImage = @imagecreatetruecolor($imgWidth, $imgHeight) or die("Can't initialize new GD image stream.");
    $bgColor = imagecolorallocate($bgImage, 51, 66, 90);
    imagefill($bgImage, 0, 0, $bgColor);
    
    //draw username.
    $ttf = './SIMYOU.TTF';
    $white = imagecolorallocate($bgImage, 255, 255, 255);
    $nameTop = 30;
    imagettftext($bgImage, 18, 0, $imgHeight, $nameTop, $white, $ttf, $username);
    
    //get level and calculate size.
    $lvlSize = getTxtSize($ttf, $level, 12);
    $numSize = getTxtSize($ttf, $num, 12);
    
    //draw some color parts on background image.
    $deepblue = imagecolorallocate($bgImage, 36, 46, 63);    
    if ($imgBig == 1) {
        $p1Left = $imgHeight;
        $p1Top = $nameTop + 15;
    }
    else {
        $p1Left = $imgHeight + 218;
        $p1Top = $nameTop - 10;
    }
    $p1sizex = $p1Left + $lvlSize[0] + 95;
    $p1sizey = $p1Top + 30;
    imagefilledrectangle($bgImage, $p1Left, $p1Top, $p1sizex, $p1sizey, $deepblue);
    $p2Left = $p1sizex + 10;
    $p2Top = $p1Top;
    $p2sizex = $p2Left + $numSize[0] + 95;
    $p2sizey = $p2Top + 30;
    imagefilledrectangle($bgImage, $p2Left, $p2Top, $p2sizex, $p2sizey, $deepblue);
    
    //draw some text.
    $leveltxt = '社区等级';
    $numtxt = '游戏数量';
    $recenttxt = '最近常玩的游戏';
    $grey = imagecolorallocate($bgImage, 140, 155, 132);
    $t1Left = $p1Left + 5;
    $t1Top = $p1Top + 20;
    imagettftext($bgImage, 12, 0, $t1Left, $t1Top, $grey, $ttf, $leveltxt);
    $t2Left = $p2Left + 5;
    $t2Top = $t1Top;
    imagettftext($bgImage, 12, 0, $t2Left, $t2Top, $grey, $ttf, $numtxt);
    if ($imgBig == 1) {
        $t3Left = $p1Left;
        $t3Top = $p1sizey + 30;
    }
    else {
        $t3Left = $imgHeight;
        $t3Top = $p1sizey + 10;
    }
    imagettftext($bgImage, 14, 0, $t3Left, $t3Top, $white, $ttf, $recenttxt);
    $lvlLeft = $p1Left + 85;
    $lvlTop = $t1Top + 1;
    imagettftext($bgImage, 14, 0, $lvlLeft, $lvlTop, $white, $ttf, $level);
    $numLeft = $p2Left + 85;
    $numTop = $t2Top + 1;
    imagettftext($bgImage, 14, 0, $numLeft, $numTop, $white, $ttf, $num);
    
    //copy and merge head image into bgImage.
    $head = imagecreatefromjpeg($headurl);
    if ($imgBig == 1) {
        $headSize = 184;
    }
    else {
        $headSize = 130;
    }
    $headTop = ($imgHeight-$headSize)/2;
    $headLeft = $headTop;
    imagecopyresampled($bgImage, $head, $headLeft, $headTop, 0, 0, $headSize, $headSize, imagesx($head), imagesy($head));
    //copy and merge some game image into bgImage.
    $game1Left = $headLeft * 2 + $headSize;
    $gameTop = $headTop + $headSize - 72;
    if ($rGame1!=null) {
        $game1 = imagecreatefromjpeg("http://cdn.akamai.steamstatic.com/steam/apps/{$rGame1}/header.jpg");
        imagecopyresampled($bgImage, $game1, $game1Left, $gameTop, 0, 0, 153, 72, imagesx($game1), imagesy($game1));
    }
    $game2Left = $game1Left + 153 + $headLeft/2;
    if ($rGame2!=null) {
        $game2 = imagecreatefromjpeg("http://cdn.akamai.steamstatic.com/steam/apps/{$rGame2}/header.jpg");
        imagecopyresampled($bgImage, $game2, $game2Left, $gameTop, 0, 0, 153, 72, imagesx($game2), imagesy($game2));
    }
    $game3Left = $game2Left + 153 + $headLeft/2;
    if ($rGame3!=null) {
        $game3 = imagecreatefromjpeg("http://cdn.akamai.steamstatic.com/steam/apps/{$rGame3}/header.jpg");
        imagecopyresampled($bgImage, $game3, $game3Left, $gameTop, 0, 0, 153, 72, imagesx($game3), imagesy($game3));
    }
    
    //show pic.
    ob_start();
    imagejpeg($bgImage);
    $picData = ob_get_clean();
    imagedestroy($bgImage);
    $picData = base64_encode($picData);
    return $picData;
}
function drawSign($picData) {
    header("Content-type: image/jpeg");
    echo base64_decode($picData);
}