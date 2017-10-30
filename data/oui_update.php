<?php

$result = array();
$fp = fopen("http://linuxnet.ca/ieee/oui.txt",'r');
while (($line = fgets($fp, 4096)) !== FALSE) 
{
    if ($line)
    {
	preg_match('/^(\w{6})\s*\(base 16\)\t\t(.*?)$/', $line, $matches);
	if ($matches)
	{
	    print_r($matches);
	    $result[$matches[1]] = $matches[2];
	}
    }
}
fclose($fp);
file_put_contents(dirname(__FILE__) . '/oui.json', json_encode($result, JSON_PRETTY_PRINT));

?>
