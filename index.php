<?php
$site = file_get_contents('https://www.kinopoisk.ru/lists/m_act[year]/2016');
$site = iconv('windows-1251', 'UTF-8',$site);
$site = str_replace("lists/m_act[year]/'+this.value+'/", "one.php?a='+this.value+'", $site );
echo $site;





?>

