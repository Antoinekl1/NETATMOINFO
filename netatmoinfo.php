<?php

/* 
* Script by Antoine KLEIN à partir du travail de @Cyril Lopez 
* For display Netatmo informations
* From Netatmo API
*/ 

header('Content-type: text/html; charset=utf-8');    

// **************************** PARTIE A PERSONNALISER

// Afficghage du DEBUG
$DEBUG = false; //true or false

// Nombre de minutes pour raffraichir les informations affichées
$refresh_frequency = 30;

// Indiquez les informations après avoir créer une application sur http://dev.netatmo.com/dev/createapp
$app_id = '[APP_ID_NETATMO]';
$app_secret = '[APP_SECRET_NETATMO]';

// **************************** VERIFICATION IDENTIFICATION

if ($_GET['mail'] != "" & $_GET['pass'] != "") 
{	
	// RECUPERTATION IDENTIFICATION
	$username = $_GET['mail'];
	$password = $_GET['pass'];
	
} else {
	// DEMANDE IDENTIFICATION
	$username = '';
	$password = '';
	echo '<form action="http://www.histoires2familles.fr/netatmoinfo.php" method="get" accept-charset="utf-8" id="form-login">';
	echo '<div class="form-text">';
	echo '<label for="mail">Email : </label>';
	echo '<input type="text" name="mail" value="" class="focusable" autofocus="1"  />';
	echo '</div>';
	echo '<div class="form-text">';
	echo '<label for="pass">Mot de passe : </label>';
	echo '<input type="password" name="pass" value="" class="focusable"  />';
	echo '</div>';
	echo '<div class="submit-form">';
	echo '<input type="submit" name="log_submit" value="Envoyer" tabindex="5"  />';
	echo '</div>';
	echo '</form>';
	echo '<div class="more-form">';
	echo '<a href="/fr-FR/access/lostpassword">Mot de passe oublié ?</a>';
	echo '</br>';
	echo '<a href="/fr-FR/access/signup">Création de compte</a>';
	echo '</div>';
}

if  ($username != '' & $password != '') 
{
// **************************** DEBUT PAGE *****************************************

// **************************** AFFICHAGE INFORMATIONS PAGE
$url_site='http://www.histoires2familles.fr/netatmoinfo.php?mail='.$username.'&pass='.$password;
echo 'Vous pouvez vous reconnecter en automatique avec ce lien : '.$url_site;
echo '<br /><br />La page se rafraichie toutes les '.$refresh_frequency.' secondes';
echo '<META HTTP-EQUIV="Refresh" CONTENT="'.$refresh_frequency.'" URL="'.$url_site.'">';

// **************************** CONNECTION API
$token_url = "https://api.netatmo.net/oauth2/token";
$postdata = http_build_query(
        array(
            'grant_type' => "password",
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'username' => $username,
            'password' => $password,
            'scope' => 'read_station read_thermostat write_thermostat'
    )
);

$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => $postdata
	)
);

// Récupération des données via l'Api Netatmo
function getSSLPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLVERSION,3); 
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

getSSLPage($token_url);

$context  = stream_context_create($opts);
$response = file_get_contents($token_url, false, $context);
$params = null;
$params = json_decode($response, true);
$api_url = "https://api.netatmo.net/api/getuser?access_token=" . $params['access_token']."&app_type=app_thermostat";
$requete = @file_get_contents($api_url);

// Création de(s) l'url(s)
$api_url_stationmeteo = "https://api.netatmo.net/api/devicelist?access_token=" .$params['access_token'];
$api_url_user = "https://api.netatmo.net/api/getuser?access_token=" . $params['access_token']."&app_type=app_thermostat";
$api_url_thermostat = "https://api.netatmo.net/api/devicelist?access_token=" .  $params['access_token']."&app_type=app_thermostat";

$data_info = json_decode(file_get_contents($api_url_stationmeteo, false, $context), true);
$data_user = json_decode(file_get_contents($api_url_user, false, $context), true);
$data_therm = json_decode(file_get_contents($api_url_thermostat, false, $context), true);

// ***************************** DEBUG

if ($DEBUG == true) {
	echo "<hr />DEBUG :<br />";
	echo "<br />----------------------------<br />";
	echo "api_url_stationmeteo : <br />";
	echo $api_url_stationmeteo;
	echo "<br />";
	echo "api_url_thermostat : <br />";
	echo $api_url_thermostat;
	echo "<br />";
	echo "----------------------------<br />";
	echo "DATA_INFO : <br />";
	print_r($data_info);
	echo "<br />";
	echo "----------------------------<br />";
	echo "DATA_USER : <br />";
	print_r($data_user);
	echo "<br />";
	echo "----------------------------<br />";
	echo "DATA_THERM : <br />";
	print_r($data_therm);
	echo "<br />";
	echo "----------------------------<br />";
}
//***************************** FONCTIONS 

// Battery level INDORR
Function NABatteryLevelIndoorModule($data)
{
    if ( $data >= 5640 ) 
	{ 
		return "<span style=color:#006400>Pleine</span>";
	} else {
		if ( $data >= 5280 )
		{ 
			return "<span style=color:#228B22>Haute</span>"; 
		} else {
			if ( $data >= 4920 )
			{
				return "<span style=color:#FF8C00>Moyenne</span>";
			} else {
				if ( $data >= 4560 )
				{
					return "<span style=color:#FF0000>Bassse</span>";
				} else {
					return "<span style=color:#8B0000>Très bassse</span>";
				}
			}
		}
	}
}

// Battery level OUTDOOR
Function NABatteryLevelModule($data)
{
    if ( $data >= 5500 ) 
	{ 
		return "<span style=color:#006400>Pleine</span>";
	} else {
		if ( $data >= 5000 )
		{ 
			return "<span style=color:#228B22>Haute</span>"; 
		} else {
			if ( $data >= 4500 )
			{
				return "<span style=color:#FF8C00>Moyenne</span>";
			} else {
				if ( $data >= 4000 )
				{
					return "<span style=color:#FF0000>Basse</span>";
				} else {
					return "<span style=color:#8B0000>Très basse</span>";
				}
			}
		}
	}
}

// Battery level thermostat
Function NABatteryLevelThermostat($data)
{
    if ( $data >= 4100 ) 
	{ 
		return "<span style=color:#006400>Pleine</span>";
	} else {
		if ( $data >= 3600 )
		{ 
			return "<span style=color:#228B22>Haute</span>"; 
		} else {
			if ( $data >= 3300 )
			{
				return "<span style=color:#FF8C00>Moyenne</span>";
			} else {
				if ( $data >= 3000 )
				{
					return "<span style=color:#FF0000>Basse</span>";
				} else {
					return "<span style=color:#8B0000>Très basse</span>";
				}
			}
		}
	}
}

// rf_status
Function NARadioRssiTreshold($data)
{
    if ( $data >= 90 ) 
	{ 
		return "<span style=color:#FF0000>Signal mauvais</span>";
	} else {
		if ( $data >= 80 )
		{ 
			return "<span style=color:#FF8C00>Signal de qualité moyenne</span>"; 
		} else {
			if ( $data >= 70 )
			{
				return "<span style=color:#228B22>Signal bon</span>";
			} else {
				return "<span style=color:#006400>Signal fort</span>";
			}
		}
	}
}

// wifi_status
Function NAWifiRssiThreshold($data)
{
	if ( $data >= 86 )
	{ 
		return "<span style=color:#FF0000>Signal mauvais</span>"; 
	} else {
		if ( $data >= 71 )
		{
			return "<span style=color:#FF8C00>Signal de qualité moyenne</span>";
		} else {
			return "<span style=color:#006400>Signal bon</span>";
		}
	}
}

// Orentiation
Function NAorientation($data)
{
	if ( $data == 1 ) { return "Portait"; }
	if ( $data == 2 ) { return "Paysage"; }
}

// ETAT
Function NAetat($data)
{
	if ( $data == 0 ) { return "<span style=color:#006400>Eteint</span>"; }
	if ( $data == 100 ) { return "<span style=color:#FF0000>Allumé</span>"; }
}

// **************************** RECUPERATION DES DONNEES

//USER
$datecreation_user = $data_user['body']['date_creation']['sec'];
$email_user = $data_user['body']['mail'];

//INFO-INT
$name_int = $data_info['body']['devices'][0]['module_name'];
$mac_int = $data_info['body']['devices'][0]['_id'];
$type_int = $data_info['body']['devices'][0]['type'];
$temp_int = $data_info['body']['devices'][0]['dashboard_data']['Temperature'];
$hum_int = $data_info['body']['devices'][0]['dashboard_data']['Humidity'];
$noise_int = $data_info['body']['devices'][0]['dashboard_data']['Noise'];
$pres_int = $data_info['body']['devices'][0]['dashboard_data']['Pressure'];
$presabsolue_int = $data_info['body']['devices'][0]['dashboard_data']['AbsolutePressure'];
$co2_int = $data_info['body']['devices'][0]['dashboard_data']['CO2'];
$rain_int = $data_info['body']['devices'][0]['dashboard_data']['rain'];
$mintemp_int = $data_info['body']['devices'][0]['dashboard_data']['min_temp'];
$maxtemp_int = $data_info['body']['devices'][0]['dashboard_data']['max_temp'];
$datemintemp_int = $data_info['body']['devices'][0]['dashboard_data']['date_min_temp'];
$datemaxtemp_int = $data_info['body']['devices'][0]['dashboard_data']['date_max_temp'];
$firmware_int = $data_info['body']['devices'][0]['firmware'];
$wifi_int = $data_info['body']['devices'][0]['wifi_status'];
$refmod1_int = $data_info['body']['devices'][0]['modules'][1];
$refmod2_int = $data_info['body']['devices'][0]['modules'][2];
$refmod3_int = $data_info['body']['devices'][0]['modules'][3];

//INFO-EXT
$name_ext = $data_info['body']['modules'][0]['module_name'];
$mac_ext = $data_info['body']['modules'][0]['_id'];
$type_ext = $data_info['body']['modules'][0]['type'];
$temp_ext = $data_info['body']['modules'][0]['dashboard_data']['Temperature'];
$hum_ext = $data_info['body']['modules'][0]['dashboard_data']['Humidity'];
$mintemp_ext = $data_info['body']['modules'][0]['dashboard_data']['min_temp'];
$maxtemp_ext = $data_info['body']['modules'][0]['dashboard_data']['max_temp'];
$datemintemp_ext = $data_info['body']['modules'][0]['dashboard_data']['date_min_temp'];
$datemaxtemp_ext = $data_info['body']['modules'][0]['dashboard_data']['date_max_temp'];
$battery_ext = $data_info['body']['modules'][0]['battery_vp'];
$statusrf_ext = $data_info['body']['modules'][0]['rf_status'];
$firmware_ext = $data_info['body']['modules'][0]['firmware'];

//INFO_MOD1
if ( $refmod1_int <> "" ) {
	$name_mod1 = $data_info['body']['modules'][1]['module_name'];
	$mac_mod1 = $data_info['body']['modules'][1]['_id'];
	$type_mod1 = $data_info['body']['modules'][1]['type'];
	$temp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Temperature'];
	$hum_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Humidity'];
	$noise_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Noise'];
	$pres_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Pressure'];
	$co2_mod1 = $data_info['body']['modules'][1]['dashboard_data']['CO2'];
	$mintemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['min_temp'];
	$maxtemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['max_temp'];
	$datemintemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['date_min_temp'];
	$datemaxtemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['date_max_temp'];
	$battery_mod1 = $data_info['body']['modules'][1]['battery_vp'];
	$statusrf_mod1 = $data_info['body']['modules'][1]['rf_status'];
	$firmware_mod1 = $data_info['body']['modules'][1]['firmware'];
}

//INFO_MOD2
if ( $refmod2_int <> "" ) {
	$name_mod2 = $data_info['body']['modules'][2]['module_name'];
	$mac_mod2 = $data_info['body']['modules'][2]['_id'];
	$type_mod2 = $data_info['body']['modules'][2]['type'];
	$temp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Temperature'];
	$hum_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Humidity'];
	$noise_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Noise'];
	$pres_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Pressure'];
	$co2_mod2 = $data_info['body']['modules'][2]['dashboard_data']['CO2'];
	$mintemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['min_temp'];
	$maxtemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['max_temp'];
	$datemintemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['date_min_temp'];
	$datemaxtemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['date_max_temp'];
	$battery_mod2 = $data_info['body']['modules'][2]['battery_vp'];
	$statusrf_mod2 = $data_info['body']['modules'][2]['rf_status'];
	$firmware_mod2 = $data_info['body']['modules'][2]['firmware'];
}

//INFO_MOD3
if ( $refmod3_int <> "" ) {
	$name_mod3 = $data_info['body']['modules'][3]['module_name'];
	$mac_mod3 = $data_info['body']['modules'][3]['_id'];
	$type_mod3 = $data_info['body']['modules'][3]['type'];
	$temp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Temperature'];
	$hum_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Humidity'];
	$noise_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Noise'];
	$pres_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Pressure'];
	$co2_mod3 = $data_info['body']['modules'][3]['dashboard_data']['CO2'];
	$mintemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['min_temp'];
	$maxtemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['max_temp'];
	$datemintemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['date_min_temp'];
	$datemaxtemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['date_max_temp'];
	$battery_mod3 = $data_info['body']['modules'][3]['battery_vp'];
	$statusrf_mod3 = $data_info['body']['modules'][3]['rf_status'];
	$firmware_mod3 = $data_info['body']['modules'][3]['firmware'];
}

// Thermostat
$name_therm = $data_therm['body']['modules'][0]['module_name'];
$mac_therm = $data_therm['body']['modules'][0]['_id'];
$type_therm = $data_therm['body']['modules'][0]['type'];
$temp_therm = $data_therm['body']['modules'][0]['dashboard_data']['Temperature'];
$mintemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['min_temp'];
$maxtemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['max_temp'];
$datemintemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['date_min_temp'];
$datemaxtemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['date_max_temp'];
$battery_therm = $data_therm['body']['modules'][0]['battery_vp'];
$statusrf_therm = $data_therm['body']['modules'][0]['rf_status'];
$firmware_therm = $data_therm['body']['modules'][0]['firmware'];
$orientation_therm = $data_therm['body']['modules'][0]['therm_orientation'];
$etat_therm = $data_therm['body']['modules'][0]['therm_relay_cmd'];


// Relai
$name_relai = $data_therm['body']['devices'][0]['station_name'];
$mac_relai = $data_therm['body']['devices'][0]['_id'];
$type_relai = $data_therm['body']['devices'][0]['type'];
$firmware_relai = $data_therm['body']['devices'][0]['firmware'];
$wifi_relai = $data_therm['body']['devices'][0]['wifi_status'];
$refmod1_relai = $data_therm['body']['devices'][0]['modules'][0];
$refmod2_relai = $data_therm['body']['devices'][0]['modules'][1];
$refmod3_relai = $data_therm['body']['devices'][0]['modules'][2];
$refmac_relai = $data_therm['body']['devices'][0]['house_model']['link_station']['mac'];
$refext_relai = $data_therm['body']['devices'][0]['house_model']['link_station']['ext'];
$reftemp_relai = $data_therm['body']['devices'][0]['house_model']['link_station']['Temperature'];

// **************************** AFFICHAGE DES INFORMATIONS

//USER
echo '<h1>Compte '.$email_user.' créé le '.date('d/m/Y',$datecreation_user).' </h1><hr>';


//Module principale
echo '<h1>'.$name_int.' ('.$mac_int.') : </h1><br />';
echo '<table width=90%><tr><td width=10>';
echo '<img src="./'.$type_int.'.png" width=100 />';
echo '</td><td>';
echo '<ul>';
echo '<li>Température : <b>'.$temp_int.'</b> degrés</li>';
echo '<li>Humidité : <b>'.$hum_int.'</b> %</li>';
echo '<li>CO2 : <b>'.$co2_int.'</b> ppm</li>';
echo '<li>Pression athmosphérique : <b>'.$pres_int.'</b> mbar ('.$presabsolue_int.')</li>';
echo '<li>Niveau de bruit : <b>'.$noise_int.'</b> db</li>';
echo '<li>Niveau pluie : <b>'.$rain_int.'</b> mm</li>';
echo '<li>Température Min : <b>'.$mintemp_int.'</b> degrés à '.date('H:i',$datemintemp_int).' le '.date('d/m/Y',$datemintemp_int).'</li>';
echo '<li>Température Max : <b>'.$maxtemp_int.'</b> degrés à '.date('H:i',$datemaxtemp_int).' le '.date('d/m/Y',$datemaxtemp_int).'</li>';
echo '<li>Status Wifi : <b>'.NAWifiRssiThreshold($wifi_int).'</b> ('.$wifi_int.')</li>';
echo '<li>Firmware : <b>'.$firmware_int.'</b></li>';
echo '<li>Modules supplementaires  : </li>';
echo '<ul>';
echo '<li>Module 1 : <b>'.$refmod1_int.'</b></li>';
echo '<li>Module 2 : <b>'.$refmod2_int.'</b></li>';
echo '<li>Module 3 : <b>'.$refmod3_int.'</b></li>';
echo '</ul>';
echo '</ul>';
echo '</td></tr></table>';
echo '<br /></br>';

//Module Ext
echo '<h1>'.$name_ext.' ('.$mac_ext.') : </h1>';
echo '<table width=90%><tr><td width=10>';
echo '<img src="./'.$type_ext.'.png" width=100 />';
echo '</td><td>';
echo '<ul>';
echo '<li>Température : <b>'.$temp_ext.'</b> degrés</li>';
echo '<li>Humidité : <b>'.$hum_ext.'</b> %</li>';
echo '<li>Température Min : <b>'.$mintemp_ext.'</b> degrés à '.date('H:i',$datemintemp_ext).' le '.date('d/m/Y',$datemintemp_ext).'</li>';
echo '<li>Température Max : <b>'.$maxtemp_ext.'</b> degrés à '.date('H:i',$datemaxtemp_ext).' le '.date('d/m/Y',$datemaxtemp_ext).'</li>';
echo '<li>Niveau batterie : <b>'.NABatteryLevelModule($battery_ext).'</b> ('.$battery_ext.')</li>';
echo '<li>Status radio : <b>'.NARadioRssiTreshold($statusrf_ext).'</b> ('.$statusrf_ext.')</li>';
echo '<li>Firmware : <b>'.$firmware_ext.'</b></li>';
echo '</ul>';
echo '</td></tr></table>';
echo '<br /></br>';

//Module 1
if ( $refmod1_int <> "" ) {
	echo '<h1>'.$name_mod1.' ('.$mac_mod1.') : </h1>';
	echo '<table width=90%><tr><td width=10>';
	echo '<img src="./'.$type_mod1.'.png" width=100 />';
	echo '</td><td>';
	echo '<ul>';
	echo '<li>Température : <b>'.$temp_mod1.'</b> degrés</li>';
	echo '<li>Humidité : <b>'.$hum_mod1.'</b> %</li>';
	echo '<li>CO2 : <b>'.$co2_mod1.'</b> ppm</li>';
	echo '<li>Température Min : <b>'.$mintemp_mod1.'</b> degrés à '.date('H:i',$datemintemp_mod1).' le '.date('d/m/Y',$datemintemp_mod1).'</li>';
	echo '<li>Température Max : <b>'.$maxtemp_mod1.'</b> degrés à '.date('H:i',$datemaxtemp_mod1).' le '.date('d/m/Y',$datemaxtemp_mod1).'</li>';
	echo '<li>Niveau batterie : <b>'.NABatteryLevelIndoorModule($battery_mod1).'</b> ('.$battery_mod1.')</li>';
	echo '<li>Status radio : <b>'.NARadioRssiTreshold($statusrf_mod1).'</b> ('.$statusrf_mod1.')</li>';
	echo '<li>Firmware : <b>'.$firmware_mod1.'</b></li>';
	echo '</ul>';
	echo '</td></tr></table>';
	echo '<br /></br>';
}

//Module 2
if ( $refmod2_int <> "" ) {
	echo '<h1>'.$name_mod2.' ('.$mac_mod2.') : </h1>';
	echo '<table width=90%><tr><td width=10>';
	echo '<img src="./'.$type_mod2.'.png" width=100 />';
	echo '</td><td>';
	echo '<ul>';
	echo '<li>Température : <b>'.$temp_mod2.'</b> degrés</li>';
	echo '<li>Humidité : <b>'.$hum_mod2.'</b> %</li>';
	echo '<li>CO2 : <b>'.$co2_mod2.'</b> ppm</li>';
	echo '<li>Température Min : <b>'.$mintemp_mod2.'</b> degrés à '.date('H:i',$datemintemp_mod2).' le '.date('d/m/Y',$datemintemp_mod2).'</li>';;
	echo '<li>Température Max : <b>'.$maxtemp_mod2.'</b> degrés à '.date('H:i',$datemaxtemp_mod2).' le '.date('d/m/Y',$datemaxtemp_mod2).'</li>';
	echo '<li>Niveau batterie : <b>'.NABatteryLevelIndoorModule($battery_mod2).'</b> ('.$battery_mod2.')</li>';
	echo '<li>Status radio : <b>'.NARadioRssiTreshold($statusrf_mod2).'</b> ('.$statusrf_mod2.')</li>';
	echo '<li>Firmware : <b>'.$firmware_mod2.'</b></li>';
	echo '</ul>';
	echo '</td></tr></table>';
	echo '<br /></br>';
}

//Module 3
if ( $refmod3_int <> "" ) {
	echo '<h1>'.$name_mod3.' ('.$mac_mod3.') : </h1>';
	echo '<table width=90%><tr><td width=10>';
	echo '<img src="./'.$type_mod3.'.png" width=100 />';
	echo '</td><td>';
	echo '<ul>';
	echo '<li>Température : <b>'.$temp_mod3.'</b> degrés</li>';
	echo '<li>Humidité : <b>'.$hum_mod3.'</b> %</li>';
	echo '<li>CO2 : <b>'.$co2_mod3.'</b> ppm</li>';
	echo '<li>Température Min : <b>'.$mintemp_mod3.'</b> degrés à '.date('H:i',$datemintemp_mod3).' le '.date('d/m/Y',$datemintemp_mod3).'</li>';
	echo '<li>Température Max : <b>'.$maxtemp_mod3.'</b> degrés à '.date('H:i',$datemaxtemp_mod3).' le '.date('d/m/Y',$datemaxtemp_mod3).'</li>';
	echo '<li>Niveau batterie : <b>'.NABatteryLevelThermostat($battery_mod3).'</b> ('.$battery_mod3.')</li>';
	echo '<li>Status radio : <b>'.NARadioRssiTreshold($statusrf_mod3).'</b> ('.$statusrf_mod3.')</li>';
	echo '<li>Firmware : <b>'.$firmware_mod3.'</b></li>';
	echo '</ul>';
	echo '</td></tr></table>';
	echo '<br /></br>';
}

// Thermostat
echo '<h1>'.$name_therm.' ('.$mac_therm.') : </h1>';
echo '<table width=90%><tr><td width=100>';
echo '<img src="./'.$type_therm.'_'.$orientation_therm.'.png" width=100 />';
echo '</td><td>';
echo '<ul>';
echo '<li>Température : <b>'.$temp_therm.'</b> degrés</li>';
echo '<li>Température Min : <b>'.$mintemp_therm.'</b> degrés à '.date('H:i',$datemintemp_therm).' le '.date('d/m/Y',$datemintemp_therm).'</li>';
echo '<li>Température Max : <b>'.$maxtemp_therm.'</b> degrés à '.date('H:i',$datemaxtemp_therm).' le '.date('d/m/Y',$datemaxtemp_therm).'</li>';
echo '<li>Status radio : <b>'.NARadioRssiTreshold($statusrf_therm).'</b> ('.$statusrf_therm.')</li>';
echo '<li>Firmware : <b>'.$firmware_therm.'</b></li>';
echo '<li>Orientation : <b>'.NAorientation($orientation_therm).'</b></li>';
echo '<li>Etat de la demande de chauffage : <b>'.NAetat($etat_therm).'</b></li>';
echo '</ul>';
echo '</td></tr></table>';
echo '<br /></br>';

// Relai
echo '<h1>'.$name_relai.' ('.$mac_relai.') : </h1>';
echo '<table width=90%><tr><td width=100>';
echo '<img src=./'.$type_relai.' width=100 />';
echo '</td><td>';
echo '<ul>';
echo '<li>Status Wifi : <b>'.NAWifiRssiThreshold($wifi_relai).'</b> ('.$wifi_relai.')</li>';
echo '<li>Firmware : <b>'.$firmware_relai.'</b></li>';
echo '<li>Module MAC de référence : <b>'.$refmac_relai.'</b></li>';
echo '<li>Module EXT de référence : <b>'.$refext_relai.'</b></li>';
echo '<li>Température extérieure de référence : <b>'.$reftemp_relai.'</b> degrés</li>';
echo '<li>Thermostat affilié  : </li>';
echo '<ul>';
echo '<li>Thermostat 1 : <b>'.$refmod1_relai.'</b></li>';
echo '<li>Thermostat 2 : <b>'.$refmod2_relai.'</b></li>';
echo '<li>Thermostat 3 : <b>'.$refmod3_relai.'</b></li>';
echo '</ul>';
echo '</td></tr></table>';
echo '<br /></br>';

// **************************** FIN PAGE *****************************************
}
?>
