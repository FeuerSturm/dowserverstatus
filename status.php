<?php
//////////////////////////////////////////////////////////////////////////////////////////////////
// Days of War Live Gameserver Status Banner
//
// created by FeuerSturm - https://feuersturm.info
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
	require_once __DIR__ . '/config/config.php';
	require_once __DIR__ . '/SourceQuery/bootstrap.php';
	use xPaw\SourceQuery\SourceQuery;
	
	$cachefolder = __DIR__ . '/cache';
	if(!file_exists($cachefolder))
	{
		mkdir($cachefolder, 0755, true);
	}

	function StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $errormsg, $error_fontsize, $error_textcolor, $error_shadowcolor)
	{
		$baseimgfile = $resdir . "frame.png";
		$baseimg = imagecreatefrompng($baseimgfile);
		imagesavealpha($baseimg, true);
		$errorpic = $resdir . $error_bg;
		BuildImage($baseimg, $errorpic, 2, 2, 0, 0, 464, 96);
		$errorimg = $resdir . $error_logo;
		BuildImage($baseimg, $errorimg, 10, 10, 0, 0, 80, 80);
		$font = $resdir . $font_ttf;
		$error_textcolor_alloc = imagecolorallocate($baseimg, $error_textcolor[0], $error_textcolor[1], $error_textcolor[2]);
		$error_shadowcolor_alloc = imagecolorallocate($baseimg, $error_shadowcolor[0], $error_shadowcolor[1], $error_shadowcolor[2]);
		AddShadowedText($baseimg, $font, $error_fontsize, 110, 60, $errormsg, $error_textcolor_alloc, $error_shadowcolor_alloc, false, 2);
		imagepng($baseimg, $cachefile, 1);
		imagedestroy($baseimg);
		$contentdisp = 'Content-Disposition: inline; filename="banner.png"';
		header('content-type: image/png');
		header($contentdisp);
		readfile($cachefile);
	}
	
	function BuildImage($baseimg, $imagefile, $coord_x, $coord_y, $src_x, $src_y, $imgwidth, $imgheight)
	{
		$image = imagecreatefrompng($imagefile);
		imagesavealpha($image, true);
		imagecopy($baseimg, $image, $coord_x, $coord_y, $src_x, $src_y, $imgwidth, $imgheight);
		imagedestroy($image);
	}
	
	function AddShadowedText($baseimg, $font, $font_size, $coord_x, $coord_y, $desc_text, $textcolor, $textshadow, $aligntext = false, $offset = 1)
	{
		if($aligntext)
		{
			$dims = imagettfbbox($font_size, 0, $font, $desc_text);
			$textWidth = abs($dims[4] - $dims[0]);
			$coord_x = $coord_x - $textWidth;
		}
		imagettftext($baseimg, $font_size, 0, $coord_x+$offset, $coord_y+$offset, $textshadow, $font, $desc_text);
		imagettftext($baseimg, $font_size, 0, $coord_x, $coord_y, $textcolor, $font, $desc_text);
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
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_ipport, $error_fontsize, $error_textcolor, $error_shadowcolor);
		exit;
	}
	if($ips_filter AND !in_array($ip, $ips_allowed))
	{
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_ipfilter, $error_fontsize, $error_textcolor, $error_shadowcolor);
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
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_offline, $error_fontsize, $error_textcolor, $error_shadowcolor);
		exit;
	}
	finally
	{
		$Query->Disconnect();
	}
	
	if($data['GameID'] != '454350')
	{
		StatusError($resdir, $error_logo, $error_bg, $font_ttf, $cachefile, $error_unsupported, $error_fontsize, $error_textcolor, $error_shadowcolor);
		exit;
	}

	$baseimgfile = $resdir . "frame.png";
	$baseimg = imagecreatefrompng($baseimgfile);
	imagesavealpha($baseimg, true);
	
	$map = strtolower($rules['MPN_s']);
	$mappic = $resdir . "mapimages/" . $map . ".png";
	if(!file_exists($mappic))
	{
		$mappic = $resdir . $default_bg;
	}
	BuildImage($baseimg, $mappic, 2, 2, 0, 0, 464, 96);
		
	$gameimg = $resdir . $game_logo;
	BuildImage($baseimg, $gameimg, 10, 10, 0, 0, 80, 80);

	if($darken_databg)
	{
		$status = $resdir . "status.png";
		BuildImage($baseimg, $status, 2, 2, 0, 0, 464, 96);
	}
	
	if($data['Password'] == 1)
	{
		$locked = $resdir . "lock.png";
		BuildImage($baseimg, $locked, 74, 70, 0, 0, 20, 24);
	}
	
	$desc_textcolor_alloc = imagecolorallocate($baseimg,$desc_textcolor[0],$desc_textcolor[1],$desc_textcolor[2]);
	$desc_shadowcolor_alloc = imagecolorallocate($baseimg,$desc_shadowcolor[0],$desc_shadowcolor[1],$desc_shadowcolor[2]);
	
	AddShadowedText($baseimg, $font, $font_size, 162, 30, $desc_server . ":", $desc_textcolor_alloc, $desc_shadowcolor_alloc, true);
	AddShadowedText($baseimg, $font, $font_size, 162, 47, $desc_ipport . ":", $desc_textcolor_alloc, $desc_shadowcolor_alloc, true);
	AddShadowedText($baseimg, $font, $font_size, 162, 64, $desc_map . ":", $desc_textcolor_alloc, $desc_shadowcolor_alloc, true);
	AddShadowedText($baseimg, $font, $font_size, 162, 81, $desc_players . ":", $desc_textcolor_alloc, $desc_shadowcolor_alloc, true);
	
	if(strlen($rules['ONM_s']) > $servername_maxchars)
	{
		$hostname = substr($rules['ONM_s'], 0, $servername_maxchars) . "...";
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
	
	$data_textcolor_alloc = imagecolorallocate($baseimg,$data_textcolor[0],$data_textcolor[1],$data_textcolor[2]);
	$data_shadowcolor_alloc = imagecolorallocate($baseimg,$data_shadowcolor[0],$data_shadowcolor[1],$data_shadowcolor[2]);

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
		BuildImage($baseimg, $flag, 170, 20, 0, 0, 16, 11);
		AddShadowedText($baseimg, $font, $font_size , 190, 30, $hostname, $data_textcolor_alloc, $data_shadowcolor_alloc);
	}
	else
	{
		AddShadowedText($baseimg, $font, $font_size , 170, 30, $hostname, $data_textcolor_alloc, $data_shadowcolor_alloc);
	}
	AddShadowedText($baseimg, $font, $font_size , 170, 47, $ipinfo, $data_textcolor_alloc, $data_shadowcolor_alloc);
	AddShadowedText($baseimg, $font, $font_size , 170, 64, $mapinfo, $data_textcolor_alloc, $data_shadowcolor_alloc);
	AddShadowedText($baseimg, $font, $font_size , 170, 81, $playerinfo, $data_textcolor_alloc, $data_shadowcolor_alloc);
	
	imagepng($baseimg, $cachefile, 1);
	imagedestroy($baseimg);
	$contentdisp = 'Content-Disposition: inline; filename="banner.png"';
	header('content-type: image/png');
	header($contentdisp);
	readfile($cachefile);
	exit;
?>