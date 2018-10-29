<?php
/*
trss.php: RSS generator for TF and TH
version 4.2

Code Origin: Clien 나스당

LICENSE:	Never distribute, share, or copy the code, part of the code, 
			modified version of the code, or any kind of reproduced material
			to any public domain except NAS party of clien.net
			( https://www.clien.net/service/board/cm_nas ).

Requirements:
1. PHP 7.0
2. 'curl'와 'openssl' 모듈이 PHP 설정에서 활성화되어있어야 함. (시놀로지는 Web Station - PHP 설정 - 확장에서 curl과 openssl 체크)

Usage:
http://youraddress.to/trss.php?k=검색단어&maxpage=최대검색페이지수&s=검색할사이트&bf=TF사이트의분류&bh=TH사이트의분류&trav=파일다운방법&prefer=선호하는문자열

Parameters:
k 			검색할 단어
maxpage 	최대로 검색할 페이지 수 e.g. maxpage=2는 1,2페이지에서 모두 생성
page 		특정 페이지 검색 e.g. page=2면 maxpage는 무시되고 두번째 페이지에서만 생성
s 			검색할 사이트. tf 혹은 th
			th는 all에 포함되지 않음. th는 모든 페이지에 들어가서 마그넷 주소를 가져오므로 느리고
			차단 가능성이 큼. 입력하지 않으면 tf로 검색
bf 			TF사이트에서만 사용됨. 게시판 id이며, all이거나 비어있으면 모든 게시판 검색. 모든 게시판 검색시 
			인터넷 속도와 서버에 따라 시간이 오래 걸릴 수도 있음. all에 해당하는 게시판은 아래 
			있는 $tfcategory에 포함되어 있는 게시판들.
bh 			TH사이트에서만 사용됨. 게시판 id이며, all이거나 비어있으면 통합검색.
			TF와 다르게 통합검색이 지원되므로 시간에 차이가 없음.
			카테고리는 아래 $thcategory 참고
trav		trav는 TF에서만 사용 가능.
			0 혹은 빈칸 - 첫 번째 토렌트 파일만 다운로드 (추천)
			1 - 모든 토렌트 파일과 자막 파일을 tar.gz형식으로 압축하여 다운로드 (비추천)
			2 - 첫 번째 토렌트 파일과 모든 자막 파일을 압축하여 다운로드 (비추천)
			3 - 토렌트 파일만 모두 압축하여 다운로드 (비추천)
			4 - 자막 파일만 모두 압축하여 다운로드 (추천)
prefer		prefer는 TF에서만 사용 가능.
			torrent파일에 들어가있으면 하는 문자열. 예를 들면 480p, 720p, 1080p가 한 게시글에 올라온
			글이 있어 720p라는 문자가 들어가 있는 torrent파일만 받고 싶다면 prefer=720p 사용.
			자막파일에는 해당없음. 제작자는 자막파일을 소중하게 생각합니다.

*/

// Hyperparameters
date_default_timezone_set('Asia/Seoul');
$tfaddress = 'http://www.tfreeca22.com';
$thaddress = ''; // 원하는 주소 입력!
$search_params = array('searchmaxpage' => 2,
					   'trav' => 0,
					   'prefer' => '');
						// 시놀로지 다운로드 스테이션 검색 플러그인용 옵션들
						// searchmaxpage는 최대 검색 페이지 수, trav와 prefer는 최상단 설명 참조.
$tfcategory = array('tmovie' => '영화',
					'tdrama' => '드라마',
					'tent' => '예능',
					'tv' => 'TV',
					'tani' => '애니'//,
					// 'tmusic' => '음악',
					// 'util' => '유틸' 
					); // tf의 경우 필요없는 카테고리는 //으로 주석처리하면 제외가능 (마지막 항목 제외시 그 전 항목에서 쉼표도 제거)
$thcategory = array('torrent_movie' => '외국영화',
					'torrent_kmovie' => '한국영화',
					'torrent_drama' => '드라마',
					'torrent_ent' => '예능/오락',
					'torrent_docu' => '다큐/교양',
					'torrent_video' => '뮤비/공연',
					'torrent_sports' => '스포츠',
					'torrent_ani' => '애니',
					'torrent_music' => '음악',
					'torrent_game' => '게임',
					'torrent_mobile' => '모바일',
					'torrent_util' => '유틸리티',
					'torrent_toon' => '만화',
					'torrent_book' => '강좌/E북',
					'torrent_kids' => '유아/어린이'
					);


// *******************
// Download Mode
// *******************
if (isset($_GET["down"]) and $_GET["down"] == 'y') {
	$site = $_GET["s"];
	$bo_table = $_GET["bo_table"];
	$article_id = $_GET["wr_id"];
	$trav = $_GET["trav"];
	$prefer = $_GET["prefer"];

	$files_to_download = array();

	$extensions = array('.torrent', '.smi', '.srt', '.sub', '.ass', '.ssa', '.vtt', '.zip', '.rar', '.7z');
	if ($trav == 3 or $trav == 0) {
		$extensions = array('.torrent');
	}
	elseif ($trav == 4) {
		$extensions = array('.smi', '.srt', '.sub', '.ass', '.ssa', '.vtt', '.zip', '.rar', '.7z');
	}

	if ($site == 'tf') {		
		$pageurl = $tfaddress . '/board.php?mode=view&b_id=' . $bo_table . '&id=' . $article_id . '&page=1';
		$ch = curl_init();
		$doc = getWebSource($ch, 'tf', $pageurl, true);
		curl_close($ch);
		
		$outputfilename = innerHTML($doc->getElementsByTagName('title')[0]); // assumes there must be a title tag
		$outputfilename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $outputfilename); // sanitizer for filename
		$outputfilename = $outputfilename . '.trss';

		$filecount = 0;
		$view_t4s = getElementsByClass($doc, 'td', 'view_t4');
		foreach ($view_t4s as $view_t4) {
			if ($view_t4->getAttribute('align') == 'left') {
				foreach ($view_t4->getElementsByTagName('a') as $a_elem) {
					
					if (stripos($a_elem->getAttribute('href'), 'filetender.com') !== false) {
						$tenderurl = $a_elem->getAttribute('href');
						// $downloadurl = "http://file.filetender.com/Execdownload2.php?link=" . base64_encode($bo_table . "|" . $article_id . "|" . $filecount);
						$filecount += 1;
						$filename = innerHTML($a_elem);
						$filename = preg_replace('/[\t \n]*<[^>]*>[\t \n]*/', '', $filename); // remove html tags inside the title
						$filename = preg_replace('/^\s*(.*)\s*$/', '$1', $filename); // remove front and rear whitespaces
						$skip = true;
						foreach ($extensions as $ext) {
							if (endsWith($filename, $ext)) {
								$skip = false;
								break;
							}
						}
						if ($skip) {
							continue;
						}
						array_push($files_to_download,array('filename' => $filename, 'tenderurl' => $tenderurl));
						break;
					} 
				}
			}
		}
	}
	else {
		// tf not specified
		die();
	}

	if (count($files_to_download) < 1) {
		die();
	}

	if ($trav <= 3) { 
		// check if there are preferred torrent files. $trav == 4 is left to download every subtitle files
		$backup = array();
		// $have_least_one_torrent = false;
		$preferred_count = 0;
		// $have_backupmagnet = false;
		foreach ($files_to_download as $key => $file) {
			// if (!$have_backupmagnet and endsWith($file['filename'], '.trssmagnet')) {
			// 	$backupmagnet = $file;
			// 	$have_backupmagnet = true;
			// 	unset($files_to_download[$key]);
			// }
			if (endsWith($file['filename'], '.torrent')) {
				// $have_least_one_torrent = true;
				if (!empty($prefer) and stripos($file['filename'], $prefer) === false) {
					array_push($backup, $file);
					unset($files_to_download[$key]); // pitfall is that the indices will not be reindexed after unset
				}
				else  {
					$preferred_count += 1;
				}
			} 
		}
		if (!empty($prefer) and $preferred_count < 1) {
			$files_to_download = array_merge($files_to_download, $backup);
		}
		// if (!$have_least_one_torrent and $have_backupmagnet) {
		// 	array_push($files_to_download, $backupmagnet);
		// 	# only have magnet link, so we have no choice but to 
		// }
		$files_to_download = array_values($files_to_download);
	}
	if ($trav == 2 or $trav == 0) { // download first torrent only
		$first_passed = false;
		foreach ($files_to_download as $key => $file) {
			if (endsWith($file['filename'], '.torrent') or endsWith($file['filename'], '.trssmagnet')) {
				if($first_passed) {
					unset($files_to_download[$key]); // pitfall is that the indices will not be reindexed after unset
				}
				else {
					$first_passed = true;
				}
			}
			else {
				if ($trav == 0) {
					# download torrent or magnet only
					unset($files_to_download[$key]);
				}
			}
		}
		$files_to_download = array_values($files_to_download);
	}
	
	if (count($files_to_download) == 1) {
		if (endsWith($files_to_download[0]['filename'], '.trssmagnet')) {
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $files_to_download[0]['tenderurl'],
				CURLOPT_SSL_VERIFYPEER => FALSE,
				CURLOPT_RETURNTRANSFER => 0,
				CURLOPT_HEADER => 0
			));
			$doc = new DOMDocument();
			libxml_use_internal_errors(true); // suppress 'invalid xml/html' warnings
			$doc->loadHTML(curl_exec($ch));
			libxml_clear_errors();
			curl_close($ch);
			$magnet_uri = regex_search($doc, '/document\.location\.href=\'(.+?)\'/', 1);

			$files_to_download[0]['tenderurl'] = $magnet_uri;

		}

		$tenderurl = $files_to_download[0]['tenderurl'];
		$ch = curl_init();
		$doc = getWebSource($ch, 'tf', $tenderurl, true, $pageurl);
		curl_close($ch);
		if (empty($doc)) { 
			# no document retrieved
			die();
		}

		$downloadurl = 'http://www.filetender.com' . $doc->getElementById('download')->getAttribute('href');
		$headers = array('Host: www.filetender.com', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36');
		$file_content = url_get_contents($downloadurl, $tenderurl, $headers);
		header('Content-disposition: attachment; filename="' . $files_to_download[0]['filename'] . '"');
		header("Content-Type: application/octet-stream");
		echo $file_content;
		die();
	}
	elseif (count($files_to_download) > 1) {

		# create new gzip object
		$gzip_file = tempnam('.', 'trss-');
		$gzip_handle = fopen($gzip_file, 'w');
		# loop through each file
		$downloadcnt = 0;
		$headers = array('Host: www.filetender.com', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36');
		foreach ($files_to_download as $file) {
			if ($downloadcnt > 1) {
				sleep(2);
				$downloadcnt = 0;
			}
			else {
				$downloadcnt += 1;
			}
			$tenderurl = $file['tenderurl'];
			$ch = curl_init();
			$doc = getWebSource($ch, 'tf', $tenderurl, true, $pageurl);
			curl_close($ch);
			if (empty($doc)) { 
				# no document retrieved
				die();
			}

			$downloadurl = 'http://www.filetender.com' . $doc->getElementById('download')->getAttribute('href');
			# download file
			$tmp_file = tempnam('.', 'trss-');
			file_put_contents(
				$tmp_file,
				url_get_contents($downloadurl, $tenderurl, $headers)
				// file_get_contents($file['downloadurl'])
			);

			TarAddHeader($gzip_handle, $tmp_file, $file['filename']);
			TarWriteContents($gzip_handle, $tmp_file);
			unlink($tmp_file);
		}
		TarAddFooter($gzip_handle);
		fclose($gzip_handle);

		# send the file to the browser as a download
		header('Content-disposition: attachment; filename="' . $outputfilename .'.tar"');
		header("Content-Type: application/tar");
		readfile($gzip_file);
		unlink($gzip_file);
	}
	else {
		# No files to download
		http_response_code(404);
		die();
	}

	exit();
}

// *******************
// Retrieval Mode
// *******************

// Track Time
$time_start = microtime(true); 

// *******************
// Get & Set variables
$self = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$php_self = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$k = $_GET["k"];
$k = str_replace(" ","+",$k);
$maxpage = $_GET["maxpage"];
if (empty($maxpage)){
	$maxpage = 1;
}
$page = $_GET["page"];
$site = $_GET["s"];
if ($site != 'tf' and $site != 'th' and $site != 'all') {
	$site = 'tf';
}
if(isset($_GET["b_id"]) and !isset($_GET["bf"])) {
	$bf = $_GET["b_id"];
}
else {
	$bf = $_GET["bf"];
}
$bh = $_GET["bh"];
$trav = $_GET["trav"]; // way to get subtitles or multiple files
$trav = intval($trav);
if ($trav < 0 or $trav > 4) {
	$trav = 0;
}
$prefer = $_GET["prefer"];

if ($_GET["search"] == 1) {
	// Parameters for BT Search Plugin
	$searchmaxpage = $search_params['searchmaxpage'];
	if ($page > $searchmaxpage) {
		exit();
	}
	$trav = $search_params['trav']; 
	$prefer = $search_params['prefer'];
}

if (empty($bf) or !array_key_exists($bf, $tfcategory) or $bf == 'all') {
	$bf = array_keys($tfcategory); // when 'all', table is created from tfcateg                                  ory defined above
}
else {
	$bf = array($bf);
}
if (empty($bh) or !array_key_exists($bh, $thcategory) or $bh == 'all') {
	$bh = ''; // in case of TH, empty string means searching every categories
}
// *******************

// RSS Feed Generation: https://www.sanwebe.com/2013/07/creating-valid-rss-feed-using-php
header('Content-Type: text/xml; charset=utf-8', true); //set document header content type to be XML
$xml = new DOMDocument("1.0", "UTF-8"); // Create new DOM document.

//create "RSS" element
$rss = $xml->createElement("rss"); 
$rss_node = $xml->appendChild($rss); //add RSS element to XML node
$rss_node->setAttribute("xmlns:media", "http://search.yahoo.com/mrss/"); // media namespace
$rss_node->setAttribute("version","2.0"); //set RSS version

//Create RFC822 Date format to comply with RFC822
$date_f = date("r", time());

//create "channel" element under "RSS" element
$channel = $xml->createElement("channel");  
$channel_node = $rss_node->appendChild($channel);

//add general elements under "channel" node
//Some strings are potentially needed to be escaped and such strings are inserted using createTextNode function
$node = $channel_node->appendChild($xml->createElement("title")); //title
$node->appendChild($xml->createTextNode("Torrent Feed (k=" . $k . ", site=" . $site . ", maxpage=" . $maxpage .")"));
$channel_node->appendChild($xml->createElement("description", "Torrent RSS feed by trss. 업데이트는 clien.net에서 토롤코토프의 작성글을 참조하세요."));  //description
$node = $channel_node->appendChild($xml->createElement("link")); //website link 
$node->appendChild($xml->createTextNode($self));
$channel_node->appendChild($xml->createElement("language", "ko-kr"));  //language
$channel_node->appendChild($xml->createElement("lastBuildDate", $date_f));  //last build date
$channel_node->appendChild($xml->createElement("generator", "PHP DOMDocument")); //generator


if ($site == 'tf' or $site == 'all') {
	if (empty($tfaddress)) {
		header('Content-Type: text/html; charset=utf-8'); //set document header content type to be XML
		echo '$tfaddress에 주소가 입력되지 않았습니다. trss.php의 $tfaddress를 지정하세요.';
		die();
	}
	$ch = curl_init();
	foreach ($bf as $bo_table) {
		$prev_timestamp = NULL;
		$year_deduct = 0;
		$curpage = 1;
		if($page != ""){
			$curpage = $page;
			$maxpage = $curpage;
		}
		
		while ($curpage <= $maxpage) {
			$url = $tfaddress . '/board.php?b_id=' . $bo_table . '&mode=list&sc=' . $k . '&x=0&y=0&page='.$curpage;
			$doc = getWebSource($ch, 'tf', $url, true);
			if (empty($doc)) { 
				# no document retrieved
				break;
			}
			$doc = getElementsByClass($doc, 'table', 'b_list');
			if (count($doc) > 0) { 
				$doc = $doc[0];
			}
			else {
				# no .b_list retrieved
				break;
			}
			$tr_array = getElementsByClass($doc, 'tr', 'nbgcolor', true); // get tr without tr with class nbgcolor
			if (empty($tr_array)) {
				break;
			}
			$skip_first = false;
			foreach ($tr_array as $tr_elem) {
				if (!$skip_first and $curpage > 1  and empty($page)) { 
					// TF site has bug: if page > 1, first tr is the last tr of previous page
					// So when traversing multiple pages, skip the first tr element
					$skip_first = true;
					continue;
				}
				$tds = $tr_elem->getElementsByTagName('td');
				$date = innerHTML($tds->item(2)); // third td contains date info, hour:minute or month-day format
				if (strpos($date, ':')) {
					// $date = explode(':',preg_replace('/[\t \n]*/', '', $date)); // hour:minute format
					// $timestamp = mktime(intval($date[0]),intval($date[1]),0,date('n'),date('j'),idate('Y'));
					$timestamp = mktime(0,0,0);
				}
				else {
					$date = explode('-',preg_replace('/[\t \n]*/', '', $date)); // month-day format
					if ($year_deduct == 0 and $date[0] >= date('n') and $date[1] > date('j')) {
						$year_deduct += 1;
					}
					$timestamp = mktime(0,0,0,$date[0],$date[1],idate('Y')-$year_deduct );
				}

				if (!is_null($prev_timestamp) && $prev_timestamp < $timestamp) {
					$year_deduct += 1;
					$timestamp = strtotime('-1 year', $timestamp); # deduct one year
				}
				$prev_timestamp = $timestamp;

				$a_array = $tds->item(1)->getElementsByTagName('a'); // second td contains anchor tag with subject
				$title = '';
				$pageurl = '';
				// foreach ($a_array as $a_elem) {
				$a_elem = $a_array->item(1); // second <a> always contains subject so foreach above is unnecessary
				if ($a_elem->hasAttribute('class')) {
					$get_sub = false;
					$bo_subs_html = "";
					if ($bo_table == 'tmovie' or $bo_table == 'tani') {
						$bo_subs = array_merge(getElementsByClass($tds->item(1),'span','bo_sub'), getElementsByClass($a_elem,'span','bo_sub2'));
						if (count($bo_subs) > 0) {
							$get_sub = true;
							$bo_subs_html = innerHTML($bo_subs[0]);
							$bo_subs[0]->parentNode->removeChild($bo_subs[0]);
						}
					}
					$title = innerHTML($a_elem);
					$title = preg_replace('/[\t \n]*<[^>]*>[\t \n]*/', '', $title); // remove html tags inside the title
					$title = preg_replace('/^\s*(.*)\s*$/', '$1', $title); // remove front and rear whitespaces
					$title = preg_replace('/[\t\n]/', '', $title); 
					$title = preg_replace('/[ ]{2,}/', ' ', $title); // remove redundant spaces
					if (!empty($bo_subs_html)) {
						$title = '[' . $bo_subs_html . ']' . $title;
					}
					$pageurl = $tfaddress . '/' . $a_elem->getAttribute('href');
					$query_str = parse_url($pageurl, PHP_URL_QUERY);
					parse_str($query_str, $query_params);
					$downloadurl = $php_self . '?down=y&s=tf&trav=' . $trav . '&bo_table=' . $query_params["b_id"] . '&wr_id=' . $query_params["id"] . '&prefer=' . $prefer;
					addRSSItem($channel_node, $xml, $title, $downloadurl, "", $pageurl, 'TF_'.$tfcategory[$bo_table], date('r', $timestamp));
				}
			}                                  
			$curpage += 1;

			if (count($tr_array) < 35) {
				break;
				// if retrieved results are less than 35, no need to go to next page.
				// 35 is magic-number for TF
			}
		}
		
	}
	curl_close($ch);
	
}

if ($site == 'th') {
	if (empty($thaddress)) {
		header('Content-Type: text/html; charset=utf-8'); //set document header content type to be XML
		echo '$thaddress에 주소가 입력되지 않았습니다. trss.php의 $thaddress를 지정하세요.';
		die();
	}
	$prev_timestamp = NULL;
	$curpage = 1;
	if($page != ""){
		$curpage = $page;
		$maxpage = $curpage;
	}
	$ch = curl_init();
	while ($curpage <= $maxpage) {
		if (!empty($bh)) {
			$url = $thaddress . '/bbs/board.php?bo_table=' . $bh . '&sca=&sop=and&sfl=wr_subject&stx=' . $k . '&page=' . $curpage;
		}
		else {
			$url = $thaddress . '/bbs/search.php?search_flag=search&stx=' . $k . '&page=' . $curpage;
		}
		$doc = getWebSource($ch, 'th', $url);

		$doc = getElementsByClass($doc, 'div', 'board-list-body');
		if (count($doc) > 0) { 
			$doc = $doc[0];
		}
		else {
			# no .board-list-body retrieved
			break;
		}
		$doc = $doc->getElementsByTagName('table');
		if (!empty($doc)) { 
			$doc = $doc->item(0)->getElementsByTagName('tbody')->item(0);
		}
		else {
			# No table retrieved
			break;
		}

		$cnt = 0;
		$tr_array = $doc->getElementsByTagName('tr');
		if (count($tr_array) <= 0) {
			break;
		}
		foreach ($tr_array as $tr_elem) {
			if (stripos($tr_elem->getAttribute('class'), 'td-mobile') !== false) {
				continue;
			}
			$tds = $tr_elem->getElementsByTagName('td');
			if ($tds->length > 0) {
				
				$subjectHTML = innerHTML($tds->item(1)); // 2nd td is for subject
				$title = preg_replace('/[\t \n]*<[^>]*>[\t \n]*/', '', $subjectHTML); // remove html tags inside the title
				$title = preg_replace('/[\t\n]/', '', $title); // remove front and rear whitespaces
				$title = preg_replace('/[ ]{2,}/', ' ', $title); // remove redundant spaces

				if (empty($title)) {
					continue;
				}
				
				if(!empty($bh)) {
					$bo_table = $bh;
					$category = $thcategory[$bo_table];
					$article_id = regex_search($subjectHTML, '/'. $bo_table .'\/(.+?)\.html/', 1);
				}
				else{
					$category = innerHTML($tds->item(0));
					$bo_table = regex_search($subjectHTML, '/bo_table=([^<>\/&"]*)/', 1); 
					$article_id = regex_search($subjectHTML, '/wr_id=([^<>\/&"]*)/', 1);
				}
				
				$pageurl = $thaddress . '/bbs/board.php?bo_table=' . $bo_table . '&wr_id=' . $article_id;

				$downloadurl = getMagnetFromTH($ch, $pageurl); 
				// $downloadurl = $thaddress . "/bbs/download.php?bo_table=".$bo_table."&wr_id=".$article_id."&no=0";

				$date = explode('.',innerHTML($tds->item(3))); // year.month.day or month.day or else format
				if (count($date) > 2) {
					$timestamp = mktime(0,0,0,intval($date[1]),intval($date[2]),intval($date[0]));
				}
				elseif (count($date) > 1) {
					$timestamp = mktime(0,0,0,intval($date[0]),intval($date[1]),idate('Y'));
				}
				else {
					$timestamp = mktime(0,0,0);
				}
				$filesize = innerHTML($tds->item(2));
				if (strpos($filesize, 'G') !== false) {
					$filesize = floatval($filesize) * 1073741824;
				}
				elseif (strpos($filesize, 'M') !== false) {
					$filesize = floatval($filesize) * 1048576;
				}
				elseif (strpos($filesize, 'K') !== false) {
					$filesize = floatval($filesize) * 1024;
				}
				elseif (strpos($filesize, 'T') !== false) {
					$filesize = floatval($filesize) * 1024;
				}
				else {
					$filesize = floatval($filesize);
				}
				addRSSItem($channel_node, $xml, $title, $downloadurl, '', $pageurl, 'TH_'.$category, date('r', $timestamp), intval($filesize));
				
			}
		}
		$curpage += 1;
		if (count($tr_array) < 25) {
			break;
			// if retrieved results are less than 25, no need to go to next page.
			// 25 is magic-number for TH
		}
	}
	curl_close($ch);
}

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
$channel_node->appendChild($xml->createElement("wallclockTime", $execution_time));

echo $xml->saveXML();

//**********************

function getWebSource($ch, $site='sth', $url, $redirection=true, $referer=NULL) {
	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_FOLLOWLOCATION => $redirection, // no redirection
		CURLOPT_SSL_VERIFYPEER => FALSE,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_REFERER => $referer,
		CURLOPT_HEADER => 0,
		CURLOPT_COOKIEJAR => 'cookie.txt',
		CURLOPT_COOKIEFILE => 'cookie.txt',
	));
	if ($site == 'tf') {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: uuoobe=on;'));
	}

	$doc = new DOMDocument();
	libxml_use_internal_errors(true); // suppress 'invalid xml/html' warnings
	$doc->loadHTML(curl_exec($ch));
	libxml_clear_errors();

	return $doc;
}

function getMagnetFromTH($ch, $pageurl) {
	static $cnt = 0;

	(++$cnt) % 10 == 0 && sleep(2);

	$doc = getWebSource($ch, 'th', $pageurl);
	$doc = $doc->getElementById('fdownload0');
	if (empty($doc)) {
		return -1;
	}
	$doc = innerHTML($doc);
	$magnethash = regex_search($doc, "/magnet_link\('(.+?)'\);/", 1);
	return "magnet:?xt=urn:btih:" . $magnethash;
}

function addRSSItem(&$channel_node, &$root_node, $title, $downloadurl, $description, $pageurl, $category, $pubDate=NULL, $filesize=0) {
	$item_node = $channel_node->appendChild($root_node->createElement("item")); //create a new node called "item"
	$node = $item_node->appendChild($root_node->createElement("title")); //Add Title under "item"
	$node->appendChild($root_node->createTextNode($title));
	$node = $item_node->appendChild($root_node->createElement("link"));
	$node->appendChild($root_node->createTextNode($pageurl));
	$node = $item_node->appendChild($root_node->createElement("description"));
	$node->appendChild($root_node->createTextNode($description));
	$node = $item_node->appendChild($root_node->createElement("category"));
	$node->appendChild($root_node->createTextNode($category));
	if (empty($pubDate)) {
		$pubDate = date('r');
	}
	$node = $item_node->appendChild($root_node->createElement("pubDate", $pubDate));
	$node = $item_node->appendChild($root_node->createElement("media:hash", md5($downloadurl)));
	$node->setAttribute("algo", "md5");
	$node = $item_node->appendChild($root_node->createElement("enclosure")); // filesize for rss parser
	$node->setAttribute("length", $filesize);
	$node->setAttribute("url", $downloadurl);

	return;
}

# modified from https://stackoverflow.com/questions/20728839/get-element-by-classname-with-domdocument-method
function getElementsByClass(&$parentNode, $tagName, $className, $notThatClass=false) {
	# when $notThatClass is true, I don't want tags with class named $className
	$nodes=array();

	$childNodeList = $parentNode->getElementsByTagName($tagName);
	for ($i = 0; $i < $childNodeList->length; $i++) {
		$temp = $childNodeList->item($i);
		$classpos = stripos($temp->getAttribute('class'), $className);
		if ($notThatClass and $classpos === false) {
			$nodes[]=$temp;
		}
		elseif (!$notThatClass and $classpos !== false) {
			$nodes[]=$temp;
		}
		
	}

	return $nodes;
}

// https://stackoverflow.com/questions/2087103/how-to-get-innerhtml-of-domnode
function innerHTML($node) {
	return implode(array_map([$node->ownerDocument,"saveHTML"], 
							 iterator_to_array($node->childNodes)));
}

function regex_search($string, $pattern, $idx) {
	if (preg_match($pattern, $string, $matches)) {
		return $matches[$idx];
	}
}

// http://php.net/manual/en/function.gzwrite.php#88746
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
// Adds file header to the tar file, it is used before adding file content.
// f: file resource (provided by eg. fopen)
// phisfn: path to file
// archfn: path to file in archive, directory names must be followed by '/'
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
function TarAddHeader($f,$phisfn,$archfn)
{
	$info=stat($phisfn);
	$ouid=sprintf("%6s ", decoct($info[4]));
	$ogid=sprintf("%6s ", decoct($info[5]));
	$omode=sprintf("%6s ", decoct(fileperms($phisfn)));
	$omtime=sprintf("%11s", decoct(filemtime($phisfn)));
	if (@is_dir($phisfn))
	{
		 $type="5";
		 $osize=sprintf("%11s ", decoct(0));
	}
	else
	{
		 $type='';
		 $osize=sprintf("%11s ", decoct(filesize($phisfn)));
		 clearstatcache();
	}
	$dmajor = '';
	$dminor = '';
	$gname = '';
	$linkname = '';
	$magic = '';
	$prefix = '';
	$uname = '';
	$version = '';
	$chunkbeforeCS=pack("a100a8a8a8a12A12",$archfn, $omode, $ouid, $ogid, $osize, $omtime);
	$chunkafterCS=pack("a1a100a6a2a32a32a8a8a155a12", $type, $linkname, $magic, $version, $uname, $gname, $dmajor, $dminor ,$prefix,'');

	$checksum = 0;
	for ($i=0; $i<148; $i++) $checksum+=ord(substr($chunkbeforeCS,$i,1));
	for ($i=148; $i<156; $i++) $checksum+=ord(' ');
	for ($i=156, $j=0; $i<512; $i++, $j++)    $checksum+=ord(substr($chunkafterCS,$j,1));

	fwrite($f,$chunkbeforeCS,148);
	$checksum=sprintf("%6s ",decoct($checksum));
	$bdchecksum=pack("a8", $checksum);
	fwrite($f,$bdchecksum,8);
	fwrite($f,$chunkafterCS,356);
	return true;
}
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
// Writes file content to the tar file must be called after a TarAddHeader
// f:file resource provided by fopen
// phisfn: path to file
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
function TarWriteContents($f,$phisfn)
{
	if (@is_dir($phisfn))
	{
		return;
	}
	else
	{
		$size=filesize($phisfn);
		$padding=$size % 512 ? 512-$size%512 : 0;
		$f2=fopen($phisfn,"rb");
		while (!feof($f2)) fwrite($f,fread($f2,1024*1024));
		$pstr=sprintf("a%d",$padding);
		fwrite($f,pack($pstr,''));
	}
}
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
// Adds 1024 byte footer at the end of the tar file
// f: file resource
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
function TarAddFooter($f)
{
	fwrite($f,pack('a1024',''));
}

// https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
function startsWith($haystack, $needle)
{
	 $length = strlen($needle);
	 return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
	$length = strlen($needle);

	return $length === 0 || 
	(substr($haystack, -$length) === $needle);
}

// modified from https://stackoverflow.com/a/3979882/2309874
function url_get_contents ($url, $referer, $headers=array()) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}
?>
