<?php
/*
게시물의 첫번째 토렌트 마그넷으로 받기
사용법 : 티프리카 b_id, sc, ca 그대로 사용. 2페이지 이상 할 때만 sj_page 사용
*/

$SITE = 'http://www.tfreeca22.com';

if ( $_GET["sj_mode"] == 'd' ) {
	download_magnet();
} else {
	make_rss();
}

function make_rss(){
	global $SITE;
	$query = '';
	foreach($_GET as $key => $value) $query = $query.'&'.$key.'='.$value;
	$url = $SITE.'/board.php?mode=list'.$query;
	$ret = "<rss xmlns:showrss=\"http://showrss.info/\" version=\"2.0\"><channel><title></title><link></link><description></description>";
	$headers = array('Cookie: uuoobe=on;');
	$sj_page = $_GET["sj_page"];
	if ($sj_page == '') $sj_page = 1;
	for($page = 1 ; $page <= $sj_page ; $page++) {
		$data = get_html($url, $headers);
		$data = str_replace("</span>","",str_replace("<span class='sc_font'>","",str_replace("stitle1","stitle",str_replace("stitle2","stitle",str_replace("stitle3","stitle",str_replace("stitle4","stitle",str_replace("stitle5","stitle",str_replace("<tr class=\"bgcolor\">","<tr >",$data))))))));
		$data = explode("<tr >", $data);
		$count = 0;
		for($i = 1; $i < count($data); $i++){
			if ( strpos($data[$i], 'stitle') ) {
				$tmp = explode("class=\"stitle\"> ",$data[$i]);
				$tmp = explode(" <",$tmp[1]);
				$title = $tmp[0];
			}
			else {
				$tmp = explode("class=\"\"> ",$data[$i]);
				$tmp = explode(" <",$tmp[1]);
				$titme = $tmp[0]; 
			}
			$tmp = explode("<a href=\"board.php?mode=view&",$data[$i]);
			$tmp = explode("\"",$tmp[1]);
			$view = $tmp[0];
			$ret = $ret."<item><title>".$title."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d"."</link><description></description></item>";
		}
	}
	$ret = $ret."</channel></rss>";
	header("Content-Type: application/xml");
	echo str_replace("&","&amp;",$ret);
}

function download_magnet() {
	global $SITE;
	$url = $SITE.'/torrent_info.php?bo_table=' . $_GET["b_id"] . '&wr_id=' . $_GET["id"];
	$data = get_html($url,  array());
	$tmp = explode('a href="magnet', $data);
	$tmp = explode('"', $tmp[1]);
	$ret = 'magnet'.$tmp[0];
	header('Location: '.$ret);
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
?>
