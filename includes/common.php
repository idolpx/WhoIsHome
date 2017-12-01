<?php

function pingScan($subnet) {
	global $debug, $log, $bin, $isWindows;

	if($isWindows) {
		$cmd = $bin."nmap.exe -sP -n -PT80 $subnet";
	} else {
		//$cmd = "ping -b -c 3 255.255.255.255 >/dev/null 2>&1; arp -an | awk '{print $2.$4}'";
		//$cmd = "sudo nmap -sP -n -PT80 $subnet";
		//$cmd = "nmap -sP -n -PT80 $subnet";
		//$cmd = "sudo arp-scan -lI eth0";
		$cmd = "sudo nmap -sn $subnet";
	}
	$output = shell_exec($cmd);
	if($debug) echo "Command: $cmd<br />\n"; //echo $isWindows; exit();
	if($debug) echo "<pre>$output</pre>\n";

//	$cmd = "arp -an";
//	echo "Command: $cmd<br />\n";
//	$output = shell_exec($cmd);
//	echo "<pre>$output</pre>\n";

	//nmap pattern
	$pattern = '/scan.*?((?:[0-9]{1,3}\.){3}[0-9]{1,3}).*?((?:[0-9A-Fa-f]{2}[:-]){5}(?:[0-9A-Fa-f]{2}))/s';

	//arp pattern
//	$pattern = '/\? \(((?:[0-9]{1,3}\.){3}[0-9]{1,3})\) at ((?:[0-9A-Fa-f]{2}[:-]){5}(?:[0-9A-Fa-f]{2}))/s';
	preg_match_all($pattern, $output, $matches);

	$array = array();
	for($i=0; $i<count($matches[0]); $i++) {
		$mac = strtoupper($matches[2][$i]);
		$array[$mac] = $matches[1][$i];
		//echo "ip:".$matches[1][$i].", mac: ".$matches[2][$i]."<br />\n";
	}
	//echo "<br />\n";

	//var_dump($array);
	if($debug) echo json_encode($matches);
	//echo $array["00:BB:3A:CE:E1:E6"]['ip'];

	$pattern = '/(Nmap.done.*?seconds) /s';
	preg_match($pattern, $output, $matches);

	return $array;
}

function sendAlerts() {
	global $log, $alerts, $devices;
	global $smtp_host, $smtp_user, $smtp_pass, $alert_from, $alert_email;

	$mail = new PHPMailer\PHPMailer\PHPMailer;

	//$mail->SMTPDebug = 3;                               // Enable verbose debug output

	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = $smtp_host;  // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = $smtp_user;                 // SMTP username
	$mail->Password = $smtp_pass;                           // SMTP password
	//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	//$mail->Port = 587;                                    // TCP port to connect to

	$mail->setFrom($alert_from, "WhoIsHome!");
	$mail->addAddress($alert_email, $alert_email);     // Add a recipient
	//$mail->addAddress('ellen@example.com');               // Name is optional
	//$mail->addReplyTo('info@example.com', 'Information');
	//$mail->addCC('cc@example.com');
	//$mail->addBCC('bcc@example.com');

	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	//$mail->isHTML(true);                                  // Set email format to HTML

	//$mail->Subject = "Who's Home Alert!";
	//$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
	//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	//var_dump($alerts); //exit();
	if($debug) {
		echo "Away: ".isset($alerts['away'])."<br />\n";
		echo "Home: ".isset($alerts['home'])."<br />\n";
	}

	$message = "(WhoIsHome?)\n";
	if(isset($alerts['home'])) {
		$message .= "[Home]\n";
		foreach ($alerts['home'] as $key => $value) {
			$message .= $devices[$key]['group'].':'.$devices[$key]['name']."\n";
		}
	}
	if(isset($alerts['away'])) {
		$message .= "[Away]\n";
		foreach ($alerts['away'] as $key => $value) {
			$message .= $devices[$key]['group'].':'.$devices[$key]['name']."\n";
		}
	}
	if(isset($alerts['unknown'])) {
		$message .= "[Unknown]\n";
		foreach ($alerts['unknown'] as $key => $value) {
			$message .= $devices[$key]['ip'].':'.$devices[$key]['vendor']."\n";
		}
	}
	//echo $message; exit();
	if(strlen($message) == 0) $message = 'testing!';

	$mail->Body = $message;

	if(!$mail->send()) {
		echo "Notifications could not be sent.<br />\n";
		echo "Mailer Error: " . $mail->ErrorInfo."<br />\n";
		$log->error("Mailer Error: " . $mail->ErrorInfo);
	} else {
		echo "Notifications sent!<br />\n";
		$log->info("Notifications sent!");
	}
}

/**
 * @param array $array
 * @param string $value
 * @param bool $asc - ASC (true) or DESC (false) sorting
 * @param bool $preserveKeys
 * @return array
 * */
function sortBySubValue($array, $value, $asc = true, $preserveKeys = false)
{
    if (is_object(reset($array))) {
        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} - $b->{$value}) * ($asc ? 1 : -1);
        }) : usort($array, function ($a, $b) use ($value, $asc) {
            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} - $b->{$value}) * ($asc ? 1 : -1);
        });
    } else {
        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
            return $a[$value] == $b[$value] ? 0 : ($a[$value] - $b[$value]) * ($asc ? 1 : -1);
        }) : usort($array, function ($a, $b) use ($value, $asc) {
            return $a[$value] == $b[$value] ? 0 : ($a[$value] - $b[$value]) * ($asc ? 1 : -1);
        });
    }
    return $array;
}

function sortBySubValueStr($array, $value, $asc = true, $preserveKeys = false)
{
    if (is_object(reset($array))) {
        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} > $b->{$value}) * ($asc ? 1 : -1);
        }) : usort($array, function ($a, $b) use ($value, $asc) {
            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} > $b->{$value}) * ($asc ? 1 : -1);
        });
    } else {
        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
            return $a[$value] == $b[$value] ? 0 : ($a[$value] > $b[$value]) * ($asc ? 1 : -1);
        }) : usort($array, function ($a, $b) use ($value, $asc) {
            return $a[$value] == $b[$value] ? 0 : ($a[$value] > $b[$value]) * ($asc ? 1 : -1);
        });
    }
    return $array;
}

function natsort2d($aryInput) {
	$aryTemp = $aryOut = array();
	foreach ($aryInput as $key=>$value) {
		reset($value);
		$aryTemp[$key]=current($value);
	}
	natsort($aryTemp);
	foreach ($aryTemp as $key=>$value) {
		$aryOut[$key] = $aryInput[$key];
	}
	return $aryOut;
}

function s_datediff( $str_interval, $dt_menor, $dt_maior, $relative=false){

   if( is_string( $dt_menor)) $dt_menor = date_create( $dt_menor);
   if( is_string( $dt_maior)) $dt_maior = date_create( $dt_maior);

   $diff = date_diff( $dt_menor, $dt_maior, ! $relative);

	switch( $str_interval){
		case "y":
			$total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
			break;
		case "m":
			$total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
			break;
		case "d":
			$total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
			break;
		case "h":
			$total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
			break;
		case "i":
			$total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
			break;
		case "s":
			$total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
			break;
	  }

	if( $diff->invert)
	    return -1 * $total;
	else
		return $total;
}

?>
