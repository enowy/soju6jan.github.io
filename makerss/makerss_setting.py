# -*- coding: utf-8 -*-
import sys, os
reload(sys)
sys.setdefaultencoding('utf-8')

import urllib, re
import json
import base64
import traceback, time
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.support.ui import WebDriverWait
from makerss_main import download

############## 2018-10-24
# http://jaewook.net/archives/2613 기준
"""
SITE_LIST = [
	# 1.토렌트보자
	{
		'TORRENT_SITE_TITLE': 'torrentboza',
		'TORRENT_SITE_URL': 'https://torrentboza.com',
		'BO_TABLE_LIST': ['drama', 'ent', 'daq'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
		'QUERY' : '&sca=&sfl=wr_subject&sop=and&stx=NEXT'
	},
	{
		'TORRENT_SITE_TITLE': 'torrentboza',
		'TORRENT_SITE_URL': 'https://torrentboza.com',
		'BO_TABLE_LIST': ['movie', 'ani'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
		'DOWNLOAD_FILE' : 'ON',
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
	},
	{
		'TORRENT_SITE_TITLE': 'torrentboza',
		'TORRENT_SITE_URL': 'https://torrentboza.com',
		'BO_TABLE_LIST': ['ero_movie'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
	},
	# 2.토렌트맵
	{
		'TORRENT_SITE_TITLE': 'torrentmap',
		'TORRENT_SITE_URL': 'https://www.torrentmap.com',
		'BO_TABLE_LIST': ['kr_ent', 'kr_daq'], 
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[2]/div/a[2]',
		'QUERY' : '&sca=&sop=and&sfl=wr_subject&stx=NEXT'
	},
	{
		'TORRENT_SITE_TITLE': 'torrentmap',
		'TORRENT_SITE_URL': 'https://www.torrentmap.com',
		'BO_TABLE_LIST': ['kr_drama'], 
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[2]/div/a',
		'QUERY' : '&sca=&sop=and&sfl=wr_subject&stx=NEXT'
	},
	{
		'TORRENT_SITE_TITLE': 'torrentmap',
		'TORRENT_SITE_URL': 'https://www.torrentmap.com',
		'BO_TABLE_LIST': ['movie_new'], 
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div/table/tbody/tr[%s]/td[3]/div/a[2]',
		'DOWNLOAD_FILE' : 'ON',
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
		#'DOWNLOAD_PATH' : '/volume1/video/download/movie/sub'
	},
	# 3.다운로즈
	{
		'TORRENT_SITE_TITLE': 'downrose',
		'TORRENT_SITE_URL': 'https://downrose.com',
		'BO_TABLE_LIST': ['KOR_TV'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[3]/a',
		'QUERY' : '&sop=and&sfl=wr_subject&stx=720p+NEXT'
	},
	{
		'TORRENT_SITE_TITLE': 'downrose',
		'TORRENT_SITE_URL': 'https://downrose.com',
		'BO_TABLE_LIST': ['NEW_MOVIE'],
		'MAX_PAGE': 2,
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[%s]/div/div/div[2]/strong/a',
		'DOWNLOAD_FILE' : 'ON',
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
	},
	# 4.토렌트하자 
	{
		'TORRENT_SITE_TITLE': 'torrenthaja',
		'TORRENT_SITE_URL': 'https://torrenthaja.com',
		'BO_TABLE_LIST': ['torrent_drama', 'torrent_ent', 'torrent_docu'],
		'XPATH_LIST_TAG'      : '//table[@class="table table-hover"]/tbody/tr[%s]/td[2]/div/a',
		'STEP' : 2,
		'HOW' : 'USING_MAGNET_REGAX',
		'MAGNET_REGAX' : "magnet_link\(\'(?P<magnet>.*?)\'\)",
		'MAGNET_MAKE_URL' : 'magnet:?xt=urn:btih:%s',
		'QUERY' : '&sca=&sop=and&sfl=wr_subject&stx=NEXT'
	},
	{
		'TORRENT_SITE_TITLE': 'torrenthaja',
		'TORRENT_SITE_URL': 'https://torrenthaja.com',
		'BO_TABLE_LIST': ['torrent_movie', 'torrent_kmovie'],
		'XPATH_LIST_TAG'      : '//table[@class="table table-hover"]/tbody/tr[%s]/td[2]/div/a',
		'STEP' : 2,
		'HOW' : 'USING_MAGNET_REGAX',
		'MAGNET_REGAX' : "magnet_link\(\'(?P<magnet>.*?)\'\)",
		'MAGNET_MAKE_URL' : 'magnet:?xt=urn:btih:%s',
		'DOWNLOAD_FILE' : 'ON'
	},
	{
		'TORRENT_SITE_TITLE': 'avnori',
		'TORRENT_SITE_URL': 'https://avnori.com',
		'BO_TABLE_LIST': ['torrent_ymav', 'torrent_nmav', 'torrent_amav'],
		'XPATH_LIST_TAG'      : '//div[@class="gallery-boxes row"]/div[%s]/div/div/h6/a',
		'HOW' : 'USING_MAGNET_REGAX',
		'MAGNET_REGAX' : "magnet_link\(\'(?P<magnet>.*?)\'\)",
		'MAGNET_MAKE_URL' : 'magnet:?xt=urn:btih:%s',
	},
	# 5.토렌트린
	{
		'TORRENT_SITE_TITLE': 'torrentlin',
		'TORRENT_SITE_URL': 'https://torrentlin.com',
		'BO_TABLE_LIST': ['torrent_kortv_ent', 'torrent_kortv_social', 'torrent_kortv_drama'],
		'XPATH_LIST_TAG'      : '//form[@name="fboardlist"]/table/tbody/tr[%s]/td[2]/nobr/a',
		'QUERY' : '&sop=and&sfl=wr_subject&stx=720p+NEXT'
	},
	{
		'TORRENT_SITE_TITLE': 'torrentlin',
		'TORRENT_SITE_URL': 'https://torrentlin.com',
		'BO_TABLE_LIST': ['torrent_movie_new'],
		'XPATH_LIST_TAG'      : '//form[@name="fboardlist"]/table/tbody/tr[%s]/td[3]/nobr/a',
		'DOWNLOAD_FILE' : 'ON',
		'DOWNLOAD_REGEX' : "file_download\(\'(?P<url>.*?)\'\,\'(?P<filename>.*?)\'",
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
	},
	# 7.토렌트왈. 사이트에서 QUERY 지원안함
	{
		'TORRENT_SITE_TITLE': 'torrentwal',
		'TORRENT_SITE_URL': 'https://torrentwal.net',
		'BO_TABLE_LIST': ['torrent_variety', 'torrent_tv', 'torrent_docu' ],
		'XPATH_LIST_TAG'      : '//*[@id="main_body"]/table/tbody/tr[%s]/td[1]/nobr/a',
		'HOW' : 'INCLUDE_MAGNET_IN_INPUT',
	},
	{
		'TORRENT_SITE_TITLE': 'torrentwal',
		'TORRENT_SITE_URL': 'https://torrentwal.net',
		'BO_TABLE_LIST': ['torrent_movie' ],
		'XPATH_LIST_TAG'      : '//*[@id="main_body"]/table/tbody/tr[%s]/td[1]/nobr/a',
		'HOW' : 'INCLUDE_MAGNET_IN_INPUT',
		'DOWNLOAD_FILE' : 'ON',
		'DOWNLOAD_REGEX' : "file_download\(\'(?P<url>.*?)\'\,\s?\'(?P<filename>.*?)\'",
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
	},
	# 8.토렌트콜
	{
		'TORRENT_SITE_TITLE': 'torrentcall',
		'TORRENT_SITE_URL': 'https://torrentcall.net',
		'BO_TABLE_LIST': ['kordrama', 'ent', 'dacu' ],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
		'START_INDEX' : 2,
		'QUERY' : '&sop=and&sfl=wr_subject&stx=720p+NEXT',
		'HOW' : 'INCLUDE_MAGNET_IN_LIST_AND_INCLUDE_LIST_ON_VIEW'
	},
	{
		'TORRENT_SITE_TITLE': 'torrentcall',
		'TORRENT_SITE_URL': 'https://torrentcall.net',
		'BO_TABLE_LIST': ['movie'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
		'START_INDEX' : 2,
		'HOW' : 'INCLUDE_MAGNET_IN_LIST_AND_INCLUDE_LIST_ON_VIEW',
		'DOWNLOAD_FILE' : 'ON',
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'

	},
	# 9.토렌트퐁
	{
		'TORRENT_SITE_TITLE': 'torrentpong',
		'TORRENT_SITE_URL': 'https://torrentpong.com',
		'BO_TABLE_LIST': ['kordrama', 'ent', 'dacu' ],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[2]/a',
		'START_INDEX' : 2,
		'QUERY' : '&sop=and&sfl=wr_subject&stx=720p+NEXT',
		'HOW' : 'INCLUDE_MAGNET_IN_LIST_AND_INCLUDE_LIST_ON_VIEW',
		'SLEEP' : 5
	},
	{
		'TORRENT_SITE_TITLE': 'torrentpong',
		'TORRENT_SITE_URL': 'https://torrentpong.com',
		'BO_TABLE_LIST': ['movie'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[2]/a',
		'START_INDEX' : 2,
		'HOW' : 'INCLUDE_MAGNET_IN_LIST_AND_INCLUDE_LIST_ON_VIEW',
		'SLEEP' : 5,
		'DOWNLOAD_FILE' : 'ON',
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
	},
	{
		'TORRENT_SITE_TITLE': 'sspong',
		'TORRENT_SITE_URL': 'https://sspong.com',
		'BO_TABLE_LIST': ['to_kor', 'to_jpn', 'to_jpn1'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[2]/div[1]/div[%s]/div/div/div/div[2]/div[1]/a',
		'SLEEP' : 5, 
		'MAX_PAGE' : 1
	},
	{
		'TORRENT_SITE_TITLE': 'sspong',
		'TORRENT_SITE_URL': 'https://sspong.com',
		'BO_TABLE_LIST': ['to_west'],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[2]/div[1]/div[%s]/div/div/div/div[3]/div[1]/a',
		'SLEEP' : 5, 
		'MAX_PAGE' : 1
	},
	# 10.토렌트서치
	{
		'TORRENT_SITE_TITLE': 'torrent7979',
		'TORRENT_SITE_URL': 'https://torrent7979.com',
		'BO_TABLE_LIST': ['newtv', 'newgame', 'newutil' ],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[1]/a',
		'QUERY' : '&sop=and&sfl=wr_subject&stx=720p+NEXT',
	},
	{
		'TORRENT_SITE_TITLE': 'torrent7979',
		'TORRENT_SITE_URL': 'https://torrent7979.com',
		'BO_TABLE_LIST': ['newmovie' ],
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
		'START_INDEX' : 15,
		'DOWNLOAD_FILE' : 'ON',
		#'DOWNLOAD_PATH' : 'D:\\work\\makerss\\sub'
	},
	# 티프리카
	{
		'SITE_TYPE' : 'NORMAL',
		'TORRENT_SITE_TITLE': 'tfreeca',
		'TORRENT_SITE_URL': 'http://www.tfreeca22.com',
		'BO_TABLE_LIST' : ['tdrama', 'tent', 'tv'],
		'BO_TABLE_URL' : 'http://www.tfreeca22.com/board.php?b_id=%s&mode=list',
		'XPATH_LIST_TAG'      : '//body/table/tbody/tr/td[2]/table[3]/tbody/tr[%s]/td[2]/div/a[2]',
		'START_INDEX' : 3,
		'QUERY' : '&sc=720p-NEXT',
	},
]
"""
SITE_LIST = [
	{
		'TORRENT_SITE_TITLE': 'torrentmap',
		'TORRENT_SITE_URL': 'https://www.torrentmap.com',
		'BO_TABLE_LIST': ['kr_drama'], 
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[2]/div/a',
		'QUERY' : '&sca=&sop=and&sfl=wr_subject&stx=NEXT'
	},
]


def GetList(driver, site, cate):
	# 리스트 생성
	indexList = []
	max_page = site['MAX_PAGE'] if 'MAX_PAGE' in site else 1
	for page in range(1, max_page+1):
		print('PAGE : %s' % page)
		if 'SITE_TYPE' not in site: u = '%s/bbs/board.php?bo_table=%s&page=%s' % (site['TORRENT_SITE_URL'], cate, page)
		else: u = site['BO_TABLE_URL'] % cate;
		if 'QUERY' in site: u += site['QUERY']
		print('URL : %s' % u)
		driver.get(u)

		list_tag = site['XPATH_LIST_TAG'][:site['XPATH_LIST_TAG'].find('[%s]')]
		list = WebDriverWait(driver, 3).until(lambda driver: driver.find_elements_by_xpath(list_tag))
		step = 1 if 'STEP' not in site else site['STEP']
		start = 1 if 'START_INDEX' not in site else site['START_INDEX']
		for i in range(start, len(list)+1, step):
			try:
				a = WebDriverWait(driver, 3).until(lambda driver: driver.find_element_by_xpath(site['XPATH_LIST_TAG'] % i))
				if a.get_attribute('href').find(cate) == -1: continue
				item = {}
				item['title'] = a.text.strip()
				item['detail_url'] = a.get_attribute('href')
				indexList.append(item)
			except:
				print('NOT BBS : %s' % i)
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)
	# 세부 페이지에서 링크 추출
	list = []
	for item in indexList:
		print ('URL : %s' % item['detail_url'])
		driver.get(item['detail_url'])
		if 'HOW' not in site or site['HOW'] != 'USING_MAGNET_REGAX':
			try:
				# TODO 
				if site['TORRENT_SITE_TITLE'] == 'tfreeca': driver.switch_to_frame("external-frame")
				if 'HOW' in site and site['HOW'] == 'INCLUDE_MAGNET_IN_INPUT': link_element = WebDriverWait(driver, 10).until(lambda driver: driver.find_elements_by_xpath("//input[starts-with(@value,'magnet')]"))
				else: link_element = WebDriverWait(driver, 10).until(lambda driver: driver.find_elements_by_xpath("//a[starts-with(@href,'magnet')]"))
				for magnet in link_element:
					if 'HOW' in site and site['HOW'] == 'INCLUDE_MAGNET_IN_LIST_AND_INCLUDE_LIST_ON_VIEW':
						if not magnet.text.startswith('magnet'): break
					if 'HOW' in site and site['HOW'] == 'INCLUDE_MAGNET_IN_INPUT':
						entity = {}
						entity['title'] = item['title']
						entity['link'] = magnet.get_attribute('value')
						print entity['link']
						list.append(entity)
						try: print('TITLE : %s\nLINK : %s' % (entity['title'], entity['link']))
						except: pass
						continue
					idx2 = 0
					# torrentao 에서 magnet이 붙어있다
					while True:
						idx1 = magnet.get_attribute('href').find('magnet:?xt=urn', idx2)
						idx2 = magnet.get_attribute('href').find('magnet:?xt=urn', idx1+1)
						if idx2 == -1: idx2 = len(magnet.get_attribute('href'))
						# 중복검사
						entity = {}
						entity['title'] = item['title']
						entity['link'] = magnet.get_attribute('href')[idx1:idx2]
						flag = False
						for tmp in list:
							if tmp['link'] == entity['link']:
								flag = True
								break
						if flag == False:
							list.append(entity)
							try: print('TITLE : %s\nLINK : %s' % (entity['title'], entity['link']))
							except: pass
						if idx2 == len(magnet.get_attribute('href')): break
			except:
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)

		elif site['HOW'] == 'USING_MAGNET_REGAX':
			try:
				regax = re.compile(site['MAGNET_REGAX'], re.IGNORECASE)
				#match = regax.search(driver.page_source)
				match = regax.findall(driver.page_source)
				for m in match:
					entity = {}
					entity['title'] = item['title']
					entity['link'] = site['MAGNET_MAKE_URL'] % m
					list.append(entity)
					try: print('TITLE : %s\nLINK : %s' % (entity['title'], entity['link']))
					except: pass
			except:
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)
		
		# 첨부파일 다운로드
		if 'DOWNLOAD_FILE' in site and site['DOWNLOAD_FILE'] is 'ON':
			try:
				if 'DOWNLOAD_REGEX' not in site:
					tmp = '%s/bbs/download.php' % site['TORRENT_SITE_URL']
					link_element = WebDriverWait(driver, 5).until(lambda driver: driver.find_elements_by_xpath("//a[starts-with(@href,'%s')]" % tmp))
				else:
					link_element = WebDriverWait(driver, 5).until(lambda driver: driver.find_elements_by_xpath("//a[contains(@href,'bbs/download.php')]"))

				for a_tag in link_element:
					flag = False
					filename = ''
					if 'DOWNLOAD_REGEX' not in site:
						tmp = a_tag.text.replace('\n', ' ').replace('\r', '')
						url = a_tag.get_attribute('href')
					else:
						regax = re.compile(site['DOWNLOAD_REGEX'], re.IGNORECASE)
						match = regax.search(a_tag.get_attribute('href'))
						if not match: continue
						tmp = match.group('filename')
						url = match.group('url')
						idx = url.find('bbs/download.php')
						url = site['TORRENT_SITE_URL'] + '/' + url[idx:]
					for ext in ['.torrent', '.smi', '.srt', '.ass']:
						idx = tmp.find(ext)
						if idx != -1:
							flag = True
							if ext != '.torrent': 
								filename = tmp[:idx + len(ext)]
								filename = filename.replace('\\', ' ').replace('/', ' ').replace(':', ' ').replace('*', ' ').replace('?', ' ').replace('"', ' ').replace('<', ' ').replace('>', ' ').replace('|', ' ')
							break
					if flag and filename is not '':
						print('DOWNLOAD : %s' % filename)
						download(driver, url, filename, site['DOWNLOAD_PATH'] if 'DOWNLOAD_PATH' in site else None)
			except:
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)
				pass
		if 'SLEEP' in site: time.sleep(site['SLEEP'])
	return list
