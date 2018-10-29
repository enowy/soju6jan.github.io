<?php
/*
고기상자님 코드를 바탕으로 자막 파일만 받는 것만 했었다.
영화만 이 파일로 받고, 영화 이외에는 다른 분들이 업데이트해주시는 것으로 사용했으나
사이트가 자꾸 변경되어 여러 개를 같이 고쳐야 하기에 이 파일로 티프리카 모두 적용하는 것으로 변경한다.

<사용법>
1. 일반적인 사용법 
 티프리카 파라미터 
	티프리카를 브라우저로 열어서 주소창에 있는 b_id와 sc 또는 ca 값을 그대로 사용한다. mode=list, x, y 생략
	- b_id : 게시판 이름
	- sc : 검색어
	- ca : 카테고리

전용 파라미터
	- sj_page	값 : 숫자	생략시 : 1
		탐색할 최대 페이지 값. 1이면 첫 페이지만, 이외는 지정된 페이지까지.

	- sj_download_mode  값: magnet / torrent		생략시: torrent
		filetender를 거치지 않고 마그넷을 반환
		
사용예) sj_page를 넣지 않을 경우 쿼리가 동일하다
드라마 720-next 검색시 
브라우저 주소창 : http://www.tfreeca22.com/board.php?b_id=tdrama&mode=list&sc=720p-next&x=40&y=22
=> http://자신의서버주소/sj_tf.php?b_id=tdrama&sc=720p-next

드라마 미드 탐색시 주소창
브라우저 주소창 : http://www.tfreeca22.com/board.php?mode=list&b_id=tdrama&ca=미드
=> http://자신의서버주소/sj_tf.php?b_id=tdrama&&ca=미드


2. 모든파일 다운로드(자막)
전용 파라미터
	- sj_all	값 : off / on / dummy	생략시 off
		off이면 하나의 파일만, on이면 게시물에 있는 모든 파일을 받는다.
		영화나 애니에서 자막파일까지 받을 때 사용한다.
		on이면 파일 개수만큼 링크를 만들어야 하기에 오래 걸린다.

* sj_all=on 일때
	- sj_all_movie_only_1080p : on / off	생략시 off
		내가 필요해서 만들었다. 영화 탐색 시 1080p, 720p 토렌트 파일이 모두 존재할 때 720p 파일을 목록에서 제외한다.

	- sj_all_max : 생략시 20, 전체 받을경우 -1
		티프리카는 한페이지에 35개의 목록이 있다. 자막을 받기 위해 리스트를 생성하려면, 
		매 게시물마다 request를 보내서 토렌트와 자막을 리스트화한다.
		브라우저에 실행하면 2~3분 정도 걸리며 모두 갱신되나, synology download station 에서는 timeout이 발생한다.
		즉 한 페이지 모두를 생성하는데 걸리는 시간을 기다리지 못하여 에러가 발생한다. 이를 막고자 이 값을 주면 주어진 값 게시물만 리스트화한다.
		예) sj_all_max=20 일 경우 최근 게시물중 20개만 리턴
		갱신이 안 되는 것 같을 때 이 값을 변경.

	- sj_except_no_sub : on / off  생략시 off
		on일경우 무자막으로 나와있는 게시물은 받지 않는다

*sj_all=dummy
	- sj_all=dummy
		위의 경우 갱신 시 타임아웃 걸릴 가능성도 있고, 한꺼번에 게시물이 올라올 경우 놓칠 수도 있다. 
		이를 피하기 위해 목록을 넘길 때는 dummy로 한 게시물당 4개씩 고정적으로 넘긴다. 다운로드 시에는 각 인덱스별로 파일을 받게된다.
		이 방법으로 할 경우에는 sj_all_movie_only_1080p 이 사용할 수가 없고, 한국영화 같이 한 게시물당 파일이 하나만 있을 경우 3개는 필요가 없어서
		0byte 짜리 sj_tf.php 파일이 계속 쌓이게 된다.

각 방식의 차이가 있으니 선택적으로 사용하기 바라며, 이를 회피하기 위해서는 미리 주기적으로 xml 파일을 만들어 놓고 이 고정 파일을 등록해서 
사용하는 방법이 가장 좋겠으나, 스케줄러 세팅하는 것도 또 귀찮은 일이다.

사용예)
영화 액션 카테고리 자막까지 받기
브라우저 주소창 : http://www.tfreeca22.com/board.php?mode=list&b_id=tmovie&ca=액션
=> http://자신의서버주소/sj_tf.php?b_id=tdrama&ca=액션&sj_all=on

영화 자막까지
http://자신의서버주소/tfreeca/sj_tf.php?b_id=tmovie&sj_all=on&sj_all_movie_only_1080p=on
http://자신의서버주소/tfreeca/sj_tf.php?b_id=tmovie&sj_all=on&sj_all_movie_only_1080p=on&sj_all_max=20
http://자신의서버주소/tfreeca/sj_tf.php?b_id=tmovie&sj_all=dummy
*/
$SITE = 'http://www.tfreeca22.com';
$m = $_GET["sj_mode"];
if ( $m == 'd' ) {
	if ($_GET["sj_download_mode"] == 'magnet') download_magnet();
	else download();
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
	$sj_download_mode = $_GET["sj_download_mode"];
	if ($sj_download_mode == '') $sj_download_mode = 'torrent';

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
						$ret = $ret."<item><title>".$filename."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d&sj_download_mode=".$sj_download_mode."&sj_filename=".$filename."&sj_filetender=".$l."</link><description></description><showrss:showid></showrss:showid><showrss:showname>".$title."</showrss:showname></item>";
					}
				}
				$count++;
				if ($sj_all_max != -1 && $count > $sj_all_max) break;
			} else if ( $_GET["sj_all"] == 'dummy') {
				for($idx = 0 ; $idx < 4 ; $idx++) {
					$ret = $ret."<item><title>".$title."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d&sj_download_mode=".$sj_download_mode."&sj_idx=".$idx."</link><description></description><showrss:showid></showrss:showid><showrss:showname>".$title."</showrss:showname></item>";
				}
			}
			else {
				$ret = $ret."<item><title>".$title."</title><link>http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?" . $view . "&sj_mode=d&sj_download_mode=".$sj_download_mode."</link><description></description><showrss:showid></showrss:showid><showrss:showname>".$title."</showrss:showname></item>";
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
	header("Content-Disposition: attachment; filename=\"".$sj_filename."\"");
	header("Content-Type: application/octet-stream");
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

function download_magnet() {
	global $SITE;
	$url = $SITE.'/torrent_info.php?bo_table=' . $_GET["b_id"] . '&wr_id=' . $_GET["id"];
	$data = get_html($url,  array());
	$tmp = explode('a href="magnet', $data);
	$tmp = explode('"', $tmp[1]);
	$ret = 'magnet'.$tmp[0];
	header('Location: '.$ret);
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
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
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
