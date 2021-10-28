<?php
require_once('config.php');
require_once('functions.php');

if(isset($_GET["steamid"])) {
    $steamid = $_GET["steamid"];
    $oldTime = mysqli_fetch_array(sqlExec($sqlInfo, "SELECT * FROM {$tabletime}"));
    $oldTime = intval($oldTime["time"]);
    $newTime = time();
    if ($newTime-$oldTime>80000) {
        sqlExec($sqlInfo, "TRUNCATE TABLE {$tabletime}");
        sqlExec($sqlInfo, "INSERT INTO {$tabletime} (ID, time) VALUES (0, {$newTime})");
        sqlExec($sqlInfo, "TRUNCATE TABLE {$tablestorage}");
    }
    $oldData = getDataById($sqlInfo, $steamid, $tablestorage);
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
        sqlExec($sqlInfo, "INSERT INTO {$tablestorage} (ID, steamid, pic) VALUES (0, '{$steamid}', '{$picData}')");
        drawSign($picData);
    }
}
elseif (isset($_GET["delete"])) {
    $steamid = $_GET["delete"];
    sqlExec($sqlInfo, "DELETE FROM {$tablestorage} WHERE steamid = '{$steamid}'");
    echo "delete pic '{$steamid}' success!";
}
else {
    if (isset($_GET["install"])) {
        if ($_GET["install"]=='init') {
            sqlInit($sqlInfo, $tabletime, $tablestorage);
        }
    }
}