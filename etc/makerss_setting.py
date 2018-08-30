# -*- coding: utf-8 -*-
import sys, os
reload(sys)
sys.setdefaultencoding('utf-8')

import urllib, re
import json
import base64
import traceback
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.support.ui import WebDriverWait
from makerss_main import download


SITE_LIST = [
	{
		'TORRENT_SITE_TITLE': 'torrentboza',
		'TORRENT_SITE_URL': 'https://torrentboza.com',
		'BO_TABLE_LIST': ['ani'],
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/ul/li[%s]/div[2]/a',
	},
	{
		'TORRENT_SITE_TITLE': 'torrentmap',
		'TORRENT_SITE_URL': 'https://www.torrentmap.com',
		'BO_TABLE_LIST': ['movie_ero', 'movie_jd'], 
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div/table/tbody/tr[%s]/td[3]/div/a',
	},
	{
		'TORRENT_SITE_TITLE': 'downrose',
		'TORRENT_SITE_URL': 'https://downrose.com',
		'BO_TABLE_LIST': ['TOON'],
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div[1]/table/tbody/tr[%s]/td[2]/a',
	},
	{
		'TORRENT_SITE_TITLE': 'torrenthaja',
		'TORRENT_SITE_URL': 'https://torrenthaja.com',
		'BO_TABLE_LIST': ['torrent_movie', 'torrent_video'],
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//table[@class="table table-hover"]/tbody/tr[%s]/td[2]/div/a',
		'STEP' : 2,
		'HOW' : 'USING_MAGNET_REGAX',
		'MAGNET_REGAX' : "magnet_link\(\'(?P<magnet>.*?)\'\)",
		'MAGNET_MAKE_URL' : 'magnet:?xt=urn:btih:%s',
	},
	{
		'TORRENT_SITE_TITLE': 'avnori',
		'TORRENT_SITE_URL': 'https://avnori.com',
		'BO_TABLE_LIST': ['torrent_ymav', 'torrent_nmav'],
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//div[@class="gallery-boxes row"]/div[%s]/div/div/h6/a',
		'HOW' : 'USING_MAGNET_REGAX',
		'MAGNET_REGAX' : "magnet_link\(\'(?P<magnet>.*?)\'\)",
		'MAGNET_MAKE_URL' : 'magnet:?xt=urn:btih:%s',
	},
	{
		'TORRENT_SITE_TITLE': 'torrentao',
		'TORRENT_SITE_URL': 'https://torrentao.com',
		'BO_TABLE_LIST': ['torrent_movie_eng'],
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/div/table/tbody/tr[%s]/td[2]/a',
		'DOWNLOAD_FILE' : 'ON'
	},
	{
		'TORRENT_SITE_TITLE': 'tomovie',
		'TORRENT_SITE_URL': 'https://www.tomovie.net',
		'BO_TABLE_LIST': ['torrent_movie_eng'],
		'MAX_PAGE': 1,
		'XPATH_LIST_TAG'      : '//*[@id="fboardlist"]/ul/li[%s]/a',
		'DOWNLOAD_FILE' : 'ON'
	},
]


def GetList(driver, site, cate):
	# 리스트 생성
	indexList = []
	for page in range(1, site['MAX_PAGE']+1):
		print('PAGE : %s' % page)
		u = '%s/bbs/board.php?bo_table=%s&page=%s' % (site['TORRENT_SITE_URL'], cate, page)
		print('URL : %s' % u)
		driver.get(u)

		list_tag = site['XPATH_LIST_TAG'][:site['XPATH_LIST_TAG'].find('[%s]')]
		list = WebDriverWait(driver, 3).until(lambda driver: driver.find_elements_by_xpath(list_tag))
		step = 1 if 'STEP' not in site else site['STEP']
		for i in range(1, len(list)+1, step):
		#for i in range(1, 6):
			try:
				a = WebDriverWait(driver, 3).until(lambda driver: driver.find_element_by_xpath(site['XPATH_LIST_TAG'] % i))
				if a.get_attribute('href').find(cate) == -1: continue
				#a = WebDriverWait(driver, 3).until(lambda driver: driver.find_element_by_xpath(''))
				
				item = {}
				item['title'] = a.text
				item['detail_url'] = a.get_attribute('href')
				indexList.append(item)
			except:
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)


	# 세부 페이지에서 링크 추출
	list = []
	for item in indexList:
		print item['detail_url']
		driver.get(item['detail_url'])

		if 'HOW' not in site:
			try:
				link_element = WebDriverWait(driver, 10).until(lambda driver: driver.find_elements_by_xpath("//a[starts-with(@href,'magnet')]"))
				
				for magnet in link_element:
					print('URL : %s' % magnet.get_attribute('href')
					idx2 = 0
					# torrentao 에서 magnet이 붙어있다
					while True:
						idx1 = magnet.get_attribute('href').find('magnet:?xt=urn', idx2)
						idx2 = magnet.get_attribute('href').find('magnet:?xt=urn', idx1+1)
						if idx2 == -1: idx2 = len(magnet.get_attribute('href'))
						entity = {}
						entity['title'] = item['title']
						entity['link'] = magnet.get_attribute('href')[idx1:idx2]
						list.append(entity)
						print('TITLE : %s\nLINK : %s' % (entity['title'], entity['link']))
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
					print('TITLE : %s\nLINK : %s' % (entity['title'], entity['link']))
			except:
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)
		
		# 첨부파일 다운로드
		if 'DOWNLOAD_FILE' in site and site['DOWNLOAD_FILE'] is 'ON':
			try:
				tmp = '%s/bbs/download.php' % site['TORRENT_SITE_URL']
				link_element = WebDriverWait(driver, 10).until(lambda driver: driver.find_elements_by_xpath("//a[starts-with(@href,'%s')]" % tmp))
				for a_tag in link_element:
					tmp = a_tag.text
					tmps = tmp.split()
					flag = False
					filename = ''
					for t in tmps:
						for ext in ['.torrent', '.smi', '.srt', '.ass']:
							if t.find(ext) != -1:
								flag = True
								if ext != '.torrent': filename = t
								break
						if flag: break
					if flag and filename is not '':
						print('DOWNLOAD : %s' % filename)
						download(driver, a_tag.get_attribute('href'), filename)
			except:
				exc_info = sys.exc_info()
				traceback.print_exception(*exc_info)
				pass
	return list
