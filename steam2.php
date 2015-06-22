<?php
echo "<br><br>";
error_reporting(0);

function contains($substring, $string) {
  return (strpos($string, $substring) === false ? false : true);
}
function ping($ip) {
    $status = "";
    if($fp = fsockopen($ip,80,$errCode,$errStr,0.5)) {   
       $status = "Alive";
    } else {
       $status = "Dead";
    } 
    fclose($fp);
    return $status;
}
$steamid = strlen($_GET['steamid']) != 0 ? $_GET['steamid'] : die("Must put a steamid");
$steamid = contains("STEAM", $_GET['steamid']) == false ? die("Must put a steamid") : $_GET['steamid']; 

echo "Searching for IP's Related to:(".$steamid.")<br><br>";

function get_data($URL) {
    $user_agent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $URL); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); 
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/'); 
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$getIDInfo = get_data("http://steamidconverter.com/".$steamid);
$getIDInfoArray = split(" ",$getIDInfo, -1); 
$getIDInfoCount = count($getIDInfoArray);

for($x = 0; x < $getIDInfoCount; ++$x) {
    if(contains("name=\"keywords\"", $getIDInfoArray[$x])) {
        $t = 0;
        while(strlen($profileID) != 17) {
            $profileID = str_replace(",","",$getIDInfoArray[$x + $t]);
            $t++;
        }
        $steamURL = "http://steamcommunity.com/profiles/";
        echo "Click Image to go to profile page!<br>";
        echo "<a href=\"".$steamURL.$profileID."\" title=\"ProfileLink\"><img src=\"http://steamsignature.com/classic-".$profileID.".png\"/></a>";
        break;
    }

}

$searchPage = get_data("http://www.google.com/cse?q=".$steamid."&hl=en&num=2000&client=google-csbe");
$searchArray = split(" ",$searchPage, -1); 
$searchCount = count($searchArray);
$uniqueArray = array();
$n = 0;

for($i = 0; $i < $searchCount; ++$i) {
    if(contains("address",$searchArray[$i])) {
        if(strlen($searchArray[$i + 1]) == 2 && strlen($searchArray[$i + 2]) > 3 && strlen($searchArray[$i + 2]) < 20 && strlen($searchArray[i + 5]) < 20) {
            if(!in_array($searchArray[$i + 2], $uniqueArray) && !contains("<br>",$searchArray[$i]) && strlen($searchArray[$i + 5]) > 3) {
                if(contains("Invoked", $searchArray[$i + 5]) === false) {
        $Date = contains("-", $searchArray[$i + 5]) == true ? str_replace("-", "/", $searchArray[$i + 5]) : $searchArray[$i + 5];
        $DateSplit = split("/",$Date, -1);
        $DateLen = strlen($DateSplit[2]) > 2 ? str_replace("20", "", $DateSplit[2]) : $DateSplit[2];
        if($DateSplit[0] > 12) { 
        $Date = $DateSplit[1]."/".$DateSplit[0]."/".$DateLen;
        } else {
        $Date = $DateSplit[0]."/".$DateSplit[1]."/".$DateLen;
        }

                } else {
        $Date = "Unknown";
                }
                $Date = contains(",", $Date) == true ? str_replace(",","",$Date) : $Date;
                $IP = explode(".",$searchArray[$i + 2]);
                $IP = $IP[0].".".$IP[1].".".$IP[2].".".$IP[3];
                $Country = $searchArray[$i + 1];
                $Info = "Date: ".$Date." || (".$Country.") ".$IP.", Status: ".ping($IP);
                if(contains("...",$Info) === false) {                
        echo "<br>$Info";
        array_push($uniqueArray, $searchArray[$i + 2]);
        $n++;
                }
            } else {
                $dupeCount++;
            }    
        }
    }

}
if($n == 0) {
    print("<br>Sorry, was not able to find a record of this SteamID ($steamid)</br>");
} else {
    print("<br><br>Found $n unique IP's and ".$dupeCount." duplicates!");
}

?>
