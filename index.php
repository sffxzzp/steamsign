<?php
require_once('config.php');
require_once('functions.php');

if($_GET["steamid"]) {
    $steamid = $_GET["steamid"];
    $oldTime = mysqli_fetch_array(sqlExec($sqlInfo, "SELECT * FROM time"));
    $oldTime = intval($oldTime["time"]);
    $newTime = time();
    if ($newTime-$oldTime>80000) {
        sqlExec($sqlInfo, "TRUNCATE TABLE time");
        sqlExec($sqlInfo, "INSERT INTO time (ID, time) VALUES (0, {$newTime})");
        sqlExec($sqlInfo, "TRUNCATE TABLE storage");
    }
    $oldData = getDataById($sqlInfo, $steamid);
    if ($oldData) {
        drawSign($oldData["pic"]);
    }
    else {
        if ($_GET["size"]) {
            $picData = makeSign(getUserData($key, $steamid), 0);
        }
        else {
            $picData = makeSign(getUserData($key, $steamid), 1);
        }
        sqlExec($sqlInfo, "INSERT INTO storage (ID, steamid, pic) VALUES (0, '{$steamid}', '{$picData}')");
        drawSign($picData);
    }
}
elseif ($_GET["delete"]) {
    $steamid = $_GET["delete"];
    sqlExec($sqlInfo, "DELETE FROM storage WHERE steamid = '{$steamid}'");
    echo "delete pic '{$steamid}' success!";
}
else {
    if ($_GET["install"]) {
        if ($_GET["install"]=='init') {
            sqlInit($sqlInfo);
        }
    }
}