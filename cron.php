<?php
	require 'vendor/autoload.php';
	require 'includes/config.php';
	require 'includes/common.php';
	
	$log = new Katzgrau\KLogger\Logger('logs/');
	
	// Component Tests
	//$log->info('Returned a million search results'); echo 'ok!'; exit();
	//sendAlerts(); exit();

	$log->info('Ping sweep started');
	
	if($_SESSION['admin']) {

	}
	
	$oui = array();
	if (file_exists($data."oui.json"))
		$oui = json_decode(file_get_contents($data."oui.json"), true);
	
	$devices = array();
	if (file_exists($data."devices.json"))
		$devices = json_decode(file_get_contents($data."devices.json"), true);
		
	if (isset($_POST['mac'])) {
		echo "<pre>";
		var_dump($_POST);
		echo "</pre>";		
		if ($_POST['action'] == "Update") {
			$devices[$_POST['mac']]['group'] = $_POST['group'];
			$devices[$_POST['mac']]['name'] = $_POST['name'];
			if ($_POST['mobile']) {
				$devices[$_POST['mac']]['mobile'] = true;
			} else {
				$devices[$_POST['mac']]['mobile'] = false;
			}
			echo "Updated: ".$_POST['mac']."<br />\n";
			$log->info("[updated][$key][".$devices[$key]['ip']."], ".$devices[$key]['group'].':'.$devices[$key]['name']);
		} elseif ($_POST['action'] == "Delete") {
			unset($devices[$_POST['mac']]);
			echo "Deleted: ".$_POST['mac']."<br />\n";
			$log->info("[deleted][$key][".$devices[$key]['ip']."], ".$devices[$key]['group'].':'.$devices[$key]['name']);
		}
		$current_scan = json_decode(file_get_contents($data."current_scan.json"), true);

		if ($_POST['action'] == "Wake") {
			$cmd = 'sudo etherwake '.$_POST['mac'];
			$output = shell_exec($cmd);
			if($debug) echo "Command: $cmd<br />\n";
			if($debug) echo "<pre>$output</pre>\n";
		}
	} else {
		$current_scan = pingScan("192.168.1.0/24");
		file_put_contents($data."current_scan.json", json_encode($current_scan, JSON_PRETTY_PRINT));		
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Who's Home? Network Monitor</title>
		<script src="src/script.js"></script>
		<style>
			div.device {
				margin: -12px 0;
				clear: both;
			}
			div.mac {
				/*font-family: "Courier New", "Lucida Console", Monaco, monospace;*/
				font-family: Monaco;
				font-size: 10pt;
			}
			div.unknown {
				color: red;
			}
			div.field {
				float: left;
				width: 150px;
				font: 10pt;
				margin: 0;
			}
			div.input, div.input input {
				width: 175px;
				margin: 0 10px 0 0;
			}
			div.fleft {
				float: left;
				margin: 0 10px 0 0;
			}
		</style>
	</head>
	<body>
	<?php
		foreach ($current_scan as $key => $value) {		
			if(!isset($devices[$key])) {
				$devices[$key]['ip'] = $value["ip"];
				$devices[$key]['status'] = 1;
				$devices[$key]['group'] = "";
				$devices[$key]['name'] = "";
				$devices[$key]['mobile'] = 0;
				$devices[$key]['lasthome'] = time();
				
				$vendor = str_replace(':', '', $key);
				$vendor = substr($vendor, 0, 6);
				$vendor = $oui[$vendor];
				$devices[$key]['vendor'] = $vendor;
			}
		}
		$devices = natsort2d($devices);

		$alerts = array();
		foreach ($devices as $key => $value) {

			$diff = (time() - intval($devices[$key]['lasthome'])) / 60;
			//echo $devices[$key]['name'].'->'.$devices[$key]['lasthome'].'->'.$diff.'<br />';
			
			if(isset($current_scan[$key])) {
				$devices[$key]['ip'] = $current_scan[$key];

				if($debug)
					echo "Found: "."[".$devices[$key]['status']."][$key][".$devices[$key]['ip']."][$diff], ".$devices[$key]['group'].':'.$devices[$key]['name']."<br/>";

				// Set Alert for Home Devices
				if($devices[$key]['mobile'] && $devices[$key]['status'] == 0) {
					echo "Home Alert! : $key, ".$devices[$key]['group'].':'.$devices[$key]['name']."<br />\n";
					$log->info("[home][$key][".$devices[$key]['ip']."], ".$devices[$key]['group'].':'.$devices[$key]['name']);
					$alerts['home'][$key] = time();
				} elseif (!$devices[$key]['mobile'] && $devices[$key]['status'] == 0) {
					// Set Stationary Devices that are not found to 'online'
					$log->info("[online][$key][".$devices[$key]['ip']."][$diff], ".$devices[$key]['group'].':'.$devices[$key]['name']);
					$devices[$key]['status'] = 0;
				}
                $devices[$key]['status'] = 1;
				$devices[$key]['lasthome'] = time();
			} else {
				if($debug)
					echo "Missing: "."[".$devices[$key]['status']."][$key][".$devices[$key]['ip']."][$diff], ".$devices[$key]['group'].':'.$devices[$key]['name']."<br/>";

				// Set Alert for Away Devices
				if($devices[$key]['mobile'] && $devices[$key]['status'] == 1 && $diff > $away_timeout) {
					echo "Away Alert! : $key, ".$devices[$key]['group'].':'.$devices[$key]['name']."<br />\n";
					$log->info("[away][$key][".$devices[$key]['ip']."][$diff], ".$devices[$key]['group'].':'.$devices[$key]['name']);
					$alerts['away'][$key] = time();
                    $devices[$key]['status'] = 0;
                } elseif ($devices[$key]['mobile'] && $devices[$key]['status'] == 1) {
                	$log->info("[away_delay][$key][".$devices[$key]['ip']."][$diff], ".$devices[$key]['group'].':'.$devices[$key]['name']);
				} elseif (!$devices[$key]['mobile'] && $devices[$key]['status'] == 1) {
					// Set Stationary Devices that are not found to 'offline'
					$log->info("[offline][$key][".$devices[$key]['ip']."][$diff], ".$devices[$key]['group'].':'.$devices[$key]['name']);
					$devices[$key]['status'] = 0;
				}
				$devices[$key]['lastaway'] = time();
			}
			// Set Alert for Unknown Devices
			if(strlen($devices[$key]['group']) == 0 && strlen($devices[$key]['name']) == 0) {
				echo "Unknown Alert! : $key, ".$devices[$key]['ip'].':'.$devices[$key]['vendor']."<br />\n";
				$log->info("[unknown][$key][".$devices[$key]['ip']."], ".$devices[$key]['vendor']);
				$alerts['unknown'][$key] = time();
			}
		}
		
		// Save Status
		file_put_contents($data."devices.json", json_encode($devices, JSON_PRETTY_PRINT));
		
		// Send Notifications
		if(strlen($_POST['action']) == 0)
		if(isset($alerts['home']) || isset($alerts['away']) || isset($alerts['unknown']))
			sendAlerts();
		
		// Display Online
		echo "<h1>Online</h1>";
		foreach ($devices as $key => $value) {
			if($value["status"] == 1) {
	?>
	<div id='online' class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
		<form method="post">
			<input type="hidden" name="mac" value="<?php echo $key; ?>" />
			<div class="fleft"><?php echo $value["status"]; ?></div>
			<div class="field mac"><?php echo $key; ?></div>
			<div class="field"><?php echo $value["ip"]; ?></div>
			<div class="field input"><input type="text" name="group" value="<?php echo $value["group"]; ?>" /></div>
			<div class="field input"><input type="text" name="name" value="<?php echo $value["name"]; ?>" /></div>
			<div class="fleft"><input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /></div>
			<div class="field"><?php echo date("m-d-Y g:i a", $value['lasthome']); ?></div>
			<div class="fleft"><input type="submit" name="action" value="Update" /></div>
			<div class="fleft"><input type="submit" name="action" value="Delete" /></div>
			<div class="fleft"><input type="submit" name="action" value="Wake" /></div>
			<div class="fleft"><?php echo $value["vendor"]; ?></div>
		</form>
	</div>
	<br />
	<?php 
				$home_count = $home_count + 1;
			}
		} 
		
		// Display Offline
		echo "<h1>Offline</h1>";
		foreach ($devices as $key => $value) {
			if($value["status"] == 0) {
	?>
	<div id='offline' class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
		<form method="post">
			<input type="hidden" name="mac" value="<?php echo $key; ?>" />
			<div class="fleft"><?php echo $value["status"]; ?></div>
			<div class="field mac"><?php echo $key; ?></div>
			<div class="field"><?php echo $value["ip"]; ?></div>
			<div class="field input"><input type="text" name="group" value="<?php echo $value["group"]; ?>" /></div>
			<div class="field input"><input type="text" name="name" value="<?php echo $value["name"]; ?>" /></div>
			<div class="fleft"><input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /></div>
			<div class="field"><?php echo date("m-d-Y g:i a", $value['lasthome']); ?></div>
			<div class="fleft"><input type="submit" name="action" value="Update" /></div>
			<div class="fleft"><input type="submit" name="action" value="Delete" /></div>
			<div class="fleft"><input type="submit" name="action" value="Wake" /></div>
			<div class="fleft"><?php echo $value["vendor"]; ?></div>
		</form>
	</div>
	<br />
	<?php 
			}
		}

		$log->info('Ping sweep complete ('.$home_count.' Home)');

		if($debug) {
			echo '<pre>';
			var_dump($devices);
			echo '</pre>';
		}
	?>
	</body>
</html>