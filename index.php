<?php
	require 'vendor/autoload.php';
	require 'includes/config.php';
	require 'includes/common.php';
	require 'includes/WakeOnLan.php';
	
	$log = new Katzgrau\KLogger\Logger('logs/');
	
	// Component Tests
	//$log->info('Returned a million search results'); echo 'ok!'; exit();
	//sendAlerts(); exit();
	
	if($_SESSION['admin']) {

	}
	
	$oui = array();
	if (file_exists($data."oui.json"))
		$oui = json_decode(file_get_contents($data."oui.json"), true);
	
	$devices = array();
	if (file_exists($data."devices.json"))
		$devices = json_decode(file_get_contents($data."devices.json"), true);
		
	if (isset($_POST['mac'])) {	
		if ($_POST['action'] == "Update") {
			$devices[$_POST['mac']]['group'] = $_POST['group'];
			$devices[$_POST['mac']]['name'] = $_POST['name'];
			if ($_POST['mobile']) {
				$devices[$_POST['mac']]['mobile'] = true;
			} else {
				$devices[$_POST['mac']]['mobile'] = false;
			}
			$log->info("[updated][$key][".$devices[$key]['ip']."], ".$devices[$key]['group'].':'.$devices[$key]['name']);
		} elseif ($_POST['action'] == "Delete") {
			unset($devices[$_POST['mac']]);
			$log->info("[deleted][$key][".$devices[$key]['ip']."], ".$devices[$key]['group'].':'.$devices[$key]['name']);
		}

		if ($_POST['action'] == "Wake") {
			//$cmd = 'sudo etherwake '.$_POST['mac'];
			//$output = shell_exec($cmd);
			WakeOnLAN::wakeUp($_POST['mac'], '192.168.1.255');
		} else {
			// Save Status
			file_put_contents($data."devices.json", json_encode($devices, JSON_PRETTY_PRINT));
		}	
	}

	$devices = natsort2d($devices);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
</head>
<body>

<div data-role="page" id="home">
  <div data-role="header">
    <h1>Who's Home?!?</h1>
    <div data-role="navbar">
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#away">Away</a></li>	
        <li><a href="#online">Online</a></li>
        <li><a href="#offline">Offline</a></li>
        <li><a href="#unknown">Unknown</a></li>
      </ul>
    </div>
  </div>

  <div data-role="main" class="ui-content">
	<?php
		// Display Home
		echo "<h1>Home</h1>";
		foreach ($devices as $key => $value) {
			if($value["status"] == 1 && $value["mobile"] && strlen($value["name"]) > 0) {
	?>
	<div data-role="collapsible">
      <h1><?php if(strlen($value["name"]) == 0) echo "unknown"; ?><?php echo $value["group"]; ?> <?php echo $value["name"]; ?></h1>
      <p>
		<div class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
			<form method="post">
				<div class="ui-field-contain">
					<input type="hidden" name="mac" value="<?php echo $key; ?>" />

					<label for="madd">MAC Address:</label>
					<input type="text" name="madd" value="<?php echo $key; ?>" />

					<label for="ipadd">IP Address:</label>
					<input type="text" name="ipadd" value="<?php echo $value["ip"]; ?>" />

					<label for="lastseen">Last Seen:</label>
					<input type="text" name="lastseen" value="<?php echo date("m-d-Y g:i a", $value['lasthome']); ?>" />

					<label for="vendor">Vendor:</label>
					<input type="text" name="vendor" value="<?php echo $value["vendor"]; ?>" />

					<label for="group">Group</label>		
					<input type="text" name="group" value="<?php echo $value["group"]; ?>" />

					<label for="name">Name</label>
					<input type="text" name="name" value="<?php echo $value["name"]; ?>" />

					<label for="mobile">
						<input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /> Mobile
					</label>

					<input type="submit" name="action" value="Update" />
					<input type="submit" name="action" value="Delete" />
					<input type="submit" name="action" value="Wake" />
				</div>
			</form>
		</div>
	  </p>
	</div>
	<?php 
				$home_count = $home_count + 1;
			}
		} 
	?>
  </div>
  <div data-role="footer">
    <h1></h1>
  </div>
</div> 

<div data-role="page" id="away">
  <div data-role="header">
    <h1>Who's Home?!?</h1>
    <div data-role="navbar">
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#away">Away</a></li>	
        <li><a href="#online">Online</a></li>
        <li><a href="#offline">Offline</a></li>
        <li><a href="#unknown">Unknown</a></li>
      </ul>
    </div>
  </div>

  <div data-role="main" class="ui-content">
	<?php
		// Display Away
		echo "<h1>Away</h1>";
		foreach ($devices as $key => $value) {
			if($value["status"] == 0 && $value["mobile"] && strlen($value["name"]) > 0) {
	?>
	<div data-role="collapsible">
      <h1><?php if(strlen($value["name"]) == 0) echo "unknown"; ?><?php echo $value["group"]; ?> <?php echo $value["name"]; ?></h1>
      <p>
		<div class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
			<form method="post">
				<div class="ui-field-contain">
					<input type="hidden" name="mac" value="<?php echo $key; ?>" />

					<label for="madd">MAC Address:</label>
					<input type="text" name="madd" value="<?php echo $key; ?>" />

					<label for="ipadd">IP Address:</label>
					<input type="text" name="ipadd" value="<?php echo $value["ip"]; ?>" />

					<label for="lastseen">Last Seen:</label>
					<input type="text" name="lastseen" value="<?php echo date("m-d-Y g:i a", $value['lasthome']); ?>" />

					<label for="vendor">Vendor:</label>
					<input type="text" name="vendor" value="<?php echo $value["vendor"]; ?>" />

					<label for="group">Group</label>		
					<input type="text" name="group" value="<?php echo $value["group"]; ?>" />

					<label for="name">Name</label>
					<input type="text" name="name" value="<?php echo $value["name"]; ?>" />

					<label for="mobile">
						<input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /> Mobile
					</label>

					<input type="submit" name="action" value="Update" />
					<input type="submit" name="action" value="Delete" />
					<input type="submit" name="action" value="Wake" />
				</div>
			</form>
		</div>
	  </p>
	</div>
	<?php 
				$home_count = $home_count + 1;
			}
		} 
	?>
  </div>
  <div data-role="footer">
    <h1></h1>
  </div>
</div> 


<div data-role="page" id="online">
  <div data-role="header">
    <h1>Who's Home?!?</h1>
    <div data-role="navbar">
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#away">Away</a></li>	
        <li><a href="#online">Online</a></li>
        <li><a href="#offline">Offline</a></li>
        <li><a href="#unknown">Unknown</a></li>
      </ul>
    </div>
  </div>

  <div data-role="main" class="ui-content">
	<?php
		// Display Online
		echo "<h1>Online</h1>";
		foreach ($devices as $key => $value) {
			if($value["status"] == 1 && !$value["mobile"] && strlen($value["name"]) > 0) {
	?>
	<div data-role="collapsible">
      <h1><?php if(strlen($value["name"]) == 0) echo "unknown"; ?><?php echo $value["group"]; ?> <?php echo $value["name"]; ?></h1>
      <p>
		<div class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
			<form method="post">
				<div class="ui-field-contain">
					<input type="hidden" name="mac" value="<?php echo $key; ?>" />

					<label for="madd">MAC Address:</label>
					<input type="text" name="madd" value="<?php echo $key; ?>" />

					<label for="ipadd">IP Address:</label>
					<input type="text" name="ipadd" value="<?php echo $value["ip"]; ?>" />

					<label for="lastseen">Last Seen:</label>
					<input type="text" name="lastseen" value="<?php echo date("m-d-Y g:i a", $value['lasthome']); ?>" />

					<label for="vendor">Vendor:</label>
					<input type="text" name="vendor" value="<?php echo $value["vendor"]; ?>" />

					<label for="group">Group</label>		
					<input type="text" name="group" value="<?php echo $value["group"]; ?>" />

					<label for="name">Name</label>
					<input type="text" name="name" value="<?php echo $value["name"]; ?>" />

					<label for="mobile">
						<input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /> Mobile
					</label>

					<input type="submit" name="action" value="Update" />
					<input type="submit" name="action" value="Delete" />
					<input type="submit" name="action" value="Wake" />
				</div>
			</form>
		</div>
	  </p>
	</div>
	<?php 
				$home_count = $home_count + 1;
			}
		} 
	?>
  </div>
  <div data-role="footer">
    <h1></h1>
  </div>
</div> 


<div data-role="page" id="offline">
  <div data-role="header">
    <h1>Who's Home?!?</h1>
    <div data-role="navbar">
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#away">Away</a></li>	
        <li><a href="#online">Online</a></li>
        <li><a href="#offline">Offline</a></li>
        <li><a href="#unknown">Unknown</a></li>
      </ul>
    </div>
  </div>

  <div data-role="main" class="ui-content">
	<?php
		// Display Offline
		echo "<h1>Offline</h1>";
		foreach ($devices as $key => $value) {
			if($value["status"] == 0 && !$value["mobile"] && strlen($value["name"]) > 0) {
	?>
	<div data-role="collapsible">
      <h1><?php if(strlen($value["name"]) == 0) echo "unknown"; ?><?php echo $value["group"]; ?> <?php echo $value["name"]; ?></h1>
      <p>
		<div class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
			<form method="post">
				<div class="ui-field-contain">
					<input type="hidden" name="mac" value="<?php echo $key; ?>" />

					<label for="madd">MAC Address:</label>
					<input type="text" name="madd" value="<?php echo $key; ?>" />

					<label for="ipadd">IP Address:</label>
					<input type="text" name="ipadd" value="<?php echo $value["ip"]; ?>" />

					<label for="lastseen">Last Seen:</label>
					<input type="text" name="lastseen" value="<?php echo date("m-d-Y g:i a", $value['lasthome']); ?>" />

					<label for="vendor">Vendor:</label>
					<input type="text" name="vendor" value="<?php echo $value["vendor"]; ?>" />

					<label for="group">Group</label>		
					<input type="text" name="group" value="<?php echo $value["group"]; ?>" />

					<label for="name">Name</label>
					<input type="text" name="name" value="<?php echo $value["name"]; ?>" />

					<label for="mobile">
						<input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /> Mobile
					</label>

					<input type="submit" name="action" value="Update" />
					<input type="submit" name="action" value="Delete" />
					<input type="submit" name="action" value="Wake" />
				</div>
			</form>
		</div>
	  </p>
	</div>
	<?php 
			}
		}
	?>
  </div>
  <div data-role="footer">
    <h1></h1>
  </div>
</div> 


<div data-role="page" id="unknown">
  <div data-role="header">
    <h1>Who's Home?!?</h1>
    <div data-role="navbar">
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#away">Away</a></li>	
        <li><a href="#online">Online</a></li>
        <li><a href="#offline">Offline</a></li>
        <li><a href="#unknown">Unknown</a></li>
      </ul>
    </div>
  </div>

  <div data-role="main" class="ui-content">
	<?php
		// Display Unknown
		echo "<h1>Unknown</h1>";
		foreach ($devices as $key => $value) {
			if(strlen($value["name"]) == 0) {
	?>
	<div data-role="collapsible">
      <h1><?php if(strlen($value["name"]) == 0) echo "unknown"; ?><?php echo $value["group"]; ?> <?php echo $value["name"]; ?></h1>
      <p>
		<div class="device <?php if(strlen($value["name"]) == 0) echo "unknown"; ?>">
			<form method="post">
				<div class="ui-field-contain">
					<input type="hidden" name="mac" value="<?php echo $key; ?>" />

					<label for="madd">MAC Address:</label>
					<input type="text" name="madd" value="<?php echo $key; ?>" />

					<label for="ipadd">IP Address:</label>
					<input type="text" name="ipadd" value="<?php echo $value["ip"]; ?>" />

					<label for="lastseen">Last Seen:</label>
					<input type="text" name="lastseen" value="<?php echo date("m-d-Y g:i a", $value['lasthome']); ?>" />

					<label for="vendor">Vendor:</label>
					<input type="text" name="vendor" value="<?php echo $value["vendor"]; ?>" />

					<label for="group">Group</label>		
					<input type="text" name="group" value="<?php echo $value["group"]; ?>" />

					<label for="name">Name</label>
					<input type="text" name="name" value="<?php echo $value["name"]; ?>" />

					<label for="mobile">
						<input type="checkbox" name="mobile" <?php if($value["mobile"]) echo "checked"; ?> /> Mobile
					</label>

					<input type="submit" name="action" value="Update" />
					<input type="submit" name="action" value="Delete" />
					<input type="submit" name="action" value="Wake" />
				</div>
			</form>
		</div>
	  </p>
	</div>
	<?php 
			}
		}
	?>
  </div>
  <div data-role="footer">
    <h1></h1>
  </div>
</div> 



<div data-role="page" id="details">
  <div data-role="main" class="ui-content">
    <a href="#fullList">Go to Page One</a>
  </div>
</div>



</body>
</html>
