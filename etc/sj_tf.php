<?php
$SITE = 'http://www.tfreeca22.com';
$m = $_GET["sj_mode"];
if ( $m == 'd' ) {
	download();
} else {
	global $SITE;
	$query = '';
	foreach($_GET as $key => $value) if (startsWith($key, 'sj_') == false) $query = $query.'&'.$key.'='.$value;
	$url = $SITE.'/board.php?mode=list'.$query;
	$xml = make_rss($url);
	echo str_replace("&","&amp;",$xml);
}

function make_rss($url){
	global $SITE;
	header("Content-Type: application/xml");
	$ret = "<rss xmlns:showrss=\"http://showrss.info/\" version=\"2.0\"><channel><title></title><link></link><description></description>";
	$headers = array('Cookie: uuoobe=on;');
	$sj_page = $_GET["sj_page"];
	if ($sj_page == '') $sj_page = 1;
	$sj_all_max = $_GET["sj_all_max"];
	if ($sj_all_max == '') $sj_all_max = 20;
	for($page = 1 ; $page <= $sj_page ; $page++) {
		$data = get_html($url, $headers);
		$data = str_replace("</span>","",str_replace("<span class='sc_font'>","",str_replace("stitle1","stitle",str_replace("stitle2","stitle",str_replace("stitle3","stitle",str_replace("stitle4","stitle",str_replace("stitle5","stitle",str_replace("<tr class=\"bgcolor\">","<tr >",$data))))))));
		$data = explode("<tr >", $data);
		$count = 0;
		for($i = 1; $i < count($data); $i++){
			if ( $_GET["sj_except_no_sub"] == 'on' && strpos($data[$i], '무자막')) continue;
			if ( strpos($data[$i], 'stitle') ) $title = explode(" <",explode("class=\"stitle\"> ",$data[$i])[1])[0];
			else $title = explode(" <",explode("class=\"\"> ",$data[$i])[1])[0]; 
			$view = explode("\"",explode("<a href=\"board.php?mode=view&",$data[$i])[1])[0];
			if ( $_GET["sj_all"] == 'on') {
				$url = $SITE.'/board.php?mode=view&'.$view;
				$data2 = get_html($url, $headers);
				$attachs = explode("http://www.filetender.com", $data2);
				if ( $_GET['sj_all_movie_only_1080p'] == 'on') {
					// TODO 이쁘게
					$flag_1080p = false;
					$index_720p = -1;
					for($x = 1 ; $x < count($attachs) ; $x++) {
						$filename = substr($attachs[$x], strpos($attachs[$x], '>')+1, strpos($attachs[$x] , '<')-strpos($attachs[$x], '>')-1);
						if ( endsWith($filename, '.torrent') ) {
							if (strpos($filename, '720p') > 0) $index_720p = $x;
							if (strpos($filename, '1080p') > 0) $flag_1080p = true;
						}
					}
					if ($flag_1080p && $index_720p != -1) $attachs[$index_720p] = '';
				}
				for($x = 1 ; $x < count($attachs) ; $x++) {
					$filename = substr($attachs[$x], strpos($attachs[$x], '>')+1, strpos($attachs[$x] , '<')-strpos($attachs[$x], '>')-1);
					$l = explode("\"",$attachs[$x])[0];
					if ( $filename != '' ) {
						$ret = $ret."<item><title>".$filename."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d&sj_filename=".$filename."&sj_filetender=".$l."</link><description></description><showrss:showid></showrss:showid><showrss:showname>".$title."</showrss:showname></item>";
					}
				}
				$count++;
				if ($sj_all_max != -1 && $count > $sj_all_max) break;
			} else if ( $_GET["sj_all"] == 'dummy') {
				for($idx = 0 ; $idx < 4 ; $idx++) {
					$ret = $ret."<item><title>".$title."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d&sj_idx=".$idx."</link><description></description><showrss:showid></showrss:showid><showrss:showname>".$title."</showrss:showname></item>";
				}
			}
			else {
				$ret = $ret."<item><title>".$title."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d"."</link><description></description><showrss:showid></showrss:showid><showrss:showname>".$title."</showrss:showname></item>";
			}
		}
		if ($sj_all_max != -1 && $count > $sj_all_max) break;
	}
	$ret = $ret."</channel></rss>";
	return $ret;
}

function download() {
	global $SITE;
	$b_id = $_GET["b_id"];
	$id = $_GET["id"];
	if ( $_GET["sj_filename"] == '') {
		$ret = get_torrent();
		if ($ret == null) return;
		$sj_filename = $ret[0];
		$sj_filetender = $ret[1];
	} else {
		$sj_filename = $_GET["sj_filename"];
		$sj_filetender= $_GET["sj_filetender"];
	}
	header("Connection: keep-alive");
	header("pragma: no-cache");
	header("expires: 0");
	header("Content-Disposition: attachment; filename=\"".$sj_filename."\"");
	header("content-description: php generated data");
	$url = $SITE.'/board.php?mode=view&b_id=' . $b_id . '&id=' . $id;
	$url2 = "http://www.filetender.com".$sj_filetender;
	$headers = array(
		'Referer: '.$url,
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
		'Accept-Language: ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
		'Connection: keep-alive',
		'Host: www.filetender.com',
		'Upgrade-Insecure-Requests: 1',
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
	);
	$data = get_html($url2,  $headers);

    $url3 = 'http://www.filetender.com'.explode('"', explode('<a href="', explode('download_area', $data)[1])[1])[0];
	$headers[0] = 'Referer: '.$url2;
	$data = get_html($url3,  $headers );
	echo $data;
}

function get_torrent() {
	global $SITE;
	$b_id = $_GET["b_id"];
	$id = $_GET["id"];
	$sj_idx = $_GET["sj_idx"];
	if ($sj_idx == '') $sj_idx = 0;
	$url = $SITE.'/board.php?mode=view&b_id='.$b_id.'&id='.$id;
	$headers[] = 'Cookie: uuoobe=on;';
	$data2 = get_html($url, $headers);
	$attachs = explode("http://www.filetender.com", $data2);
	$idx = -1;
	for($x = 1 ; $x < count($attachs) ; $x++) {
		$filename = substr($attachs[$x], strpos($attachs[$x], '>')+1, strpos($attachs[$x] , '<')-strpos($attachs[$x], '>')-1);
		$l = explode("\"",$attachs[$x])[0];
		if ( $filename != '' ) {
			$idx++;
			if ($idx == $sj_idx) return array($filename, $l);
		}
	}
	return null;
}

function get_html($url, $headers) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$data = curl_exec($ch);
	return $data;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}
?>
