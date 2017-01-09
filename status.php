<?php
//////////////////////////////////////////////////////////////////////////////////////////////////
// Days of War Live Gameserver Status Banner
//
// created by Sturm [91te LLID] - https://91te.de
//
// Credits:
// - based on PHP-Source-Query Library by xPaw - https://github.com/xPaw/
// - included DoW game logo created by Switz [3rd MAR] - http://3rdmarines.org/
// - included error & lock images by FreeIconPNG - http://www.freeiconspng.com
// - included country flag icons by Mark James - http://www.famfamfam.com
// - included font "SpecialElite" by Astigmatic - http://www.astigmatic.com/
// - GeoIP features by ARTIA INTERNATIONAL S.R.L. - http://ip-api.com
// - Days of War by Driven Arts - http://drivenarts.github.io/
//////////////////////////////////////////////////////////////////////////////////////////////////
//
// GENERAL SETTINGS
// ================
//
// Bind to single gameserver?
// If you only have one gameserver, you can bind the status banner to
// it exclusively so you don't have to add its IP and port to the URL!
// i.e.: "123.456.789.123:27015" or "domain.com:27015" would be valid entries.
// Leave empty to require IP/port input!
$bind_gameserver = "";
// Game logo to use
// (has to be 80x80px PNG in "resources" folder!):
$game_logo = "default_logo.png";
// Default background image for unknown maps
// (has to be 464x96px PNG in "resources" folder!):
$default_bg = "default_bg.png";
// Logo to use for errors
// (has to be 80x80px PNG in "resources" folder!):
$error_logo = "error_logo.png";
// Background image for errors
// (has to be 464x96px PNG in "resources" folder!):
$error_bg = "error_bg.png";
// Font to use
// (has to be TrueType-Font in "resources" folder!):
$font_ttf = "SpecialElite.ttf";
// Font size for server information:
$font_size = 10;
// Font size to use for error messages:
$error_fontsize = 20;
// Show country flag corresponding to server's location?
// false = don't show country flag | true = show country flag
$countryflag_show = true;
// Override country flag?
// Instead of automatic detection you can set the desired flag manually using the
// ISO 3166-1 alpha-2 country code (i.e.: de = Germany, us = United States of America etc.)
// additionally you can use "eu" for the European Union flag!
// See "resources/flags" folder for available flags.
// Only works if $countryflag_show is set to true! Leave empty for automatic country detection!
$countryflag_set = "";
// Show simplified map name (i.e. simply "Thunder" instead of "dow_thunder_dayrain")
// true = show simplified map name | false = show default map name
$simplify_mapname = true;
// Show gamemode after map name (i.e. "Domination", "Detonation")
// true = show gamemode | false = hide gamemode
$show_gamemode = true;
// Show the query port instead of the connection port?
// false = show connection port | true = show query port
$show_queryport = true;
// Cache time in seconds to save some resources:
// Minimum is 10 seconds, maximum is 300 seconds!
$cache_time = 60;
// Filter IP addresses?
// false = don't filter IPs | true = only allow specified IPs
$ips_filter = false;
// Array of allowed IPs if $ips_filter is set to true:
$ips_allowed = array("31.186.250.10", "199.60.101.90");
//
// LANGUAGE SETTINGS
// =================
//
// Translation for "Server"
$desc_server = "Server";
// Translation for "IP/Port"
$desc_ipport = "IP/Port";
// Translation for "Map"
$desc_map = "Map";
// Translation for "Players"
$desc_players = "Players";
// Error message to display when IP and/or port
// is not specified:
$error_ipport = "IP and/or port missing!";
// Error message to display when IP address filter is enabled and an IP is used that is
// not white listed:
$error_ipfilter = "IP address denied!";
// Error message to display when the gameserver that is being queried is not a
// Days of War gameserver:
$error_unsupported = "Unsupported Game!";
// Error message to diplay when the Days of War gameserver is offline or the query
// in general failed:
$error_offline = "Gameserver OFFLINE!";
//
// DO NOT EDIT BELOW THIS LINE UNLESS YOU REALLY KNOW WHAT YOU ARE DOING!
//////////////////////////////////////////////////////////////////////////////////////////////////
	require __DIR__ . '/SourceQuery/bootstrap.php';
	use xPaw\SourceQuery\SourceQuery;
	
	$cachefolder = __DIR__ . '/cache';
	if(!file_exists($cachefolder))
	{
		mkdir($cachefolder, 0755, true);
	}

	function StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $errormsg, $error_fontsize)
	{
		$backgroundimg = $resdir . "frame.png";
		$background = imagecreatefrompng($backgroundimg);
		imagesavealpha($background, true);
		$errorpic = $resdir . $error_bg;
		$errorpicpng = imagecreatefrompng($errorpic);
		imagesavealpha($errorpicpng, true);
		imagecopy($background, $errorpicpng, 2, 2, 0, 0, 464, 96);
		imagedestroy($errorpicpng);
		$errorimg = $resdir . $error_logo;
		$errorpng = imagecreatefrompng($errorimg);
		imagecopy($background, $errorpng, 10, 10, 0, 0, 80, 80);
		imagedestroy($errorpng);
		$font = $resdir . $font_ttf;
		$whitetext = imagecolorallocate($background,255,255,255);
		$blacktext = imagecolorallocate($background,0,0,0);
		imagettftext($background, $error_fontsize, 0, 112, 62, $blacktext, $font, $errormsg);
		imagettftext($background, $error_fontsize, 0, 110, 60, $whitetext, $font, $errormsg);
		imagepng($background, $cachefile, 1);
		imagedestroy($background);
		$contentdisp = 'Content-Disposition: inline; filename="banner.png"';
		header('content-type: image/png');
		header($contentdisp);
		readfile($cachefile);
	}
	
	function AddShadowedText($background, $font, $font_size, $coord_x, $coord_y, $desc_text, $whitetext, $blacktext, $aligntext = false)
	{
		$dims = imagettfbbox($font_size, 0, $font, $desc_text);
		$textWidth = abs($dims[4] - $dims[0]);
		$coord_x = $aligntext ? $coord_x - $textWidth : $coord_x;
		imagettftext($background, $font_size, 0, $coord_x+1, $coord_y+1, $blacktext, $font, $desc_text);
		imagettftext($background, $font_size, 0, $coord_x, $coord_y, $whitetext, $font, $desc_text);
	}
	
	if(!empty($bind_gameserver))
	{
		$server = explode(":", $bind_gameserver);
		$ip = $server[0];
		$port_str = $server[1];
	}
	else
	{
		$ip = $_GET['ip'];
		$port_str = $_GET['port'];
	}
	$resdir = __DIR__ . "/resources/";
	$cachefile = $cachefolder . "/" . basename(__FILE__, '.php') . "-" . $ip . "-" . $port_str . ".png";
	if($cache_time < 10)
	{
		$cache_time = 10;
	}
	if($cache_time > 300)
	{
		$cache_time = 300;
	}
	if(file_exists($cachefile) AND (time() - filemtime($cachefile) <= $cache_time))
	{
		$contentdisp = 'Content-Disposition: inline; filename="banner.png"';
		header('content-type: image/png');
		header($contentdisp);
		readfile($cachefile);
		exit;
	}
	if(empty($ip) OR empty($port_str))
	{
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_ipport, $error_fontsize);
		exit;
	}
	if($ips_filter AND !in_array($ip, $ips_allowed))
	{
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_ipfilter, $error_fontsize);
		exit;
	}
	$font = $resdir . $font_ttf;
	$port = (int)$port_str;
	
	define('SQ_TIMEOUT', 1);
	define('SQ_ENGINE', SourceQuery::SOURCE);
		
	$Query = new SourceQuery();
	
	try
	{
		$Query->Connect($ip, $port, SQ_TIMEOUT, SQ_ENGINE);
		
		$data = $Query->GetInfo();
		$rules = $Query->GetRules();
	}
	catch(Exception $e)
	{
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_offline, $error_fontsize);
		exit;
	}
	finally
	{
		$Query->Disconnect();
	}
	
	if($data['GameID'] != '454350')
	{
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_unsupported, $error_fontsize);
		exit;
	}

	$backgroundimg = $resdir . "frame.png";
	$background = imagecreatefrompng($backgroundimg);
	imagesavealpha($background, true);
	
	$map = strtolower($rules['MPN_s']);
	$mappic = $resdir . "mapimages/" . $map . ".png";
	if(!file_exists($mappic))
	{
		$mappic = $resdir . $default_bg;
	}
	$mappicpng = imagecreatefrompng($mappic);
	imagesavealpha($mappicpng, true);
	imagecopy($background, $mappicpng, 2, 2, 0, 0, 464, 96);
	imagedestroy($mappicpng);
	
	$gameimg = $resdir . $game_logo;
	$gamepng = imagecreatefrompng($gameimg);
	imagecopy($background, $gamepng, 10, 10, 0, 0, 80, 80);
	imagedestroy($gamepng);

	if($darken_databg)
	{
		$status = $resdir . "status.png";
		$statuspng = imagecreatefrompng($status);
		imagesavealpha($statuspng, true);
		imagecopy($background, $statuspng, 2, 2, 0, 0, 464, 96);
		imagedestroy($statuspng);
	}
	
	if($data['Password'] == 1)
	{
		$locked = $resdir . "lock.png";
		$lockedpng = imagecreatefrompng($locked);
		imagesavealpha($lockedpng, true);
		imagecopy($background, $lockedpng, 74, 70, 0, 0, 20, 24);
		imagedestroy($lockedpng);
	}
	
	$whitetext = imagecolorallocate($background,255,255,255);
	$blacktext = imagecolorallocate($background,0,0,0);
	
	AddShadowedText($background, $font, $font_size, 162, 30, $desc_server . ":", $whitetext, $blacktext, true);
	AddShadowedText($background, $font, $font_size, 162, 47, $desc_ipport . ":", $whitetext, $blacktext, true);
	AddShadowedText($background, $font, $font_size, 162, 64, $desc_map . ":", $whitetext, $blacktext, true);
	AddShadowedText($background, $font, $font_size, 162, 81, $desc_players . ":", $whitetext, $blacktext, true);
	
	if(strlen($rules['ONM_s']) > 35)
	{
		$hostname = substr($rules['ONM_s'], 0, 35) . "...";
	}
	else
	{
		$hostname = $rules['ONM_s'];
	}
	$ipinfo = $show_queryport ? $ip . ":" . $port_str : $ip . ":" . $rules['P2PPORT'];
	if($simplify_mapname)
	{
		$mapname = explode("_", $map);
		$map = ucfirst($mapname[1]);
	}
	$mapinfo = $show_gamemode ? $map . " (" . $rules['G_s'] . ")" : $map;
	$playerinfo = $rules['N_i'] . " / " . $rules['P_i'];

	if($countryflag_show)
	{
		$countrycode = "unknown";
		if(empty($countryflag_set))
		{
			$query = @unserialize(file_get_contents('http://ip-api.com/php/' .$ip . '?fields=status,countryCode'));
			if($query && $query['status'] == 'success')
			{
				$countrycode = $query['countryCode'];
			}
		}
		else
		{
			$countrycode = $countryflag_set;
		}
		$flag = $resdir . "flags/" . strtolower($countrycode) . ".png";
		$flagpng = imagecreatefrompng($flag);
		imagesavealpha($flagpng, true);
		imagecopy($background, $flagpng, 170, 20, 0, 0, 16, 11);
		imagedestroy($flagpng);
		AddShadowedText($background, $font, $font_size , 190, 30, $hostname, $whitetext, $blacktext);
	}
	else
	{
		AddShadowedText($background, $font, $font_size , 170, 30, $hostname, $whitetext, $blacktext);
	}
	AddShadowedText($background, $font, $font_size , 170, 47, $ipinfo, $whitetext, $blacktext);
	AddShadowedText($background, $font, $font_size , 170, 64, $mapinfo, $whitetext, $blacktext);
	AddShadowedText($background, $font, $font_size , 170, 81, $playerinfo, $whitetext, $blacktext);
	
	imagepng($background, $cachefile, 1);
	imagedestroy($background);
	$contentdisp = 'Content-Disposition: inline; filename="banner.png"';
	header('content-type: image/png');
	header($contentdisp);
	readfile($cachefile);
	exit;
?>