# -*- coding: utf-8 -*-
import sys, os, time
reload(sys)
sys.setdefaultencoding('utf-8')

from makerss_setting import *

def MakeRssFeed(where, cate, list):
	str =  '<rss xmlns:showrss=\"http://showrss.info/\" version=\"2.0\">\n'
	str += '\t<channel>\n'
	str += '\t\t<title>' + '%s - %s</title>\n' % (where, cate)
	str += '\t\t<link></link>\n'
	str += '\t\t<description></description>\n'
	for item in list:
		str += '\t\t<item>\n'
		str += '\t\t\t<title>%s</title>\n' % item['title']
		str += '\t\t\t<link>%s</link>\n' % item['link']
		#str += '\t\t\t<description>%s</description>\n' % item['title']
		str += '\t\t\t<showrss:showid></showrss:showid>\n'
		str += '\t\t\t<showrss:showname>%s</showrss:showname>\n' % item['title']
		str += '\t\t</item>\n'
	str += '\t</channel>\n'
	str += '</rss>'
	return str.replace('&', '&amp;')

def WriteFile(filename, data ):
	try:
		#with open(filename, "w", encoding='utf8') as f:
		with open(filename, "w") as f:
			f.write( unicode(data) )
		f.close()
		return
	except Exception as e:
		print('W11:%s' % e)
		pass
	try:
		with open(filename, "w", encoding='utf8') as f:
		#with open(filename, "w") as f:
			f.write( data )
		f.close()
		return
	except Exception as e:
		print('W22:%s' % e)
		pass
 
def download(driver, download_url, filename, path):
	print('Injecting retrieval code into web page : %s' % download_url)
	driver.execute_script("""
	    window.file_contents = null;
	    var xhr = new XMLHttpRequest();
	    xhr.responseType = 'blob';
	    xhr.onload = function() {
	        var reader  = new FileReader();
	        reader.onloadend = function() {
	            window.file_contents = reader.result;
	        };
	        reader.readAsDataURL(xhr.response);
	    };
	    xhr.open('GET', %(download_url)s);
	    xhr.send();
	""".replace('\r\n', ' ').replace('\r', ' ').replace('\n', ' ') % {
	    'download_url': json.dumps(download_url),
	})

	print('Looping until file is retrieved')
	downloaded_file = None
	while downloaded_file is None:
	    # Returns the file retrieved base64 encoded (perfect for downloading binary)
	    downloaded_file = driver.execute_script('return (window.file_contents !== null ? window.file_contents.split(\',\')[1] : null);')
	    #print(downloaded_file)
	    if not downloaded_file:
	        print('\tNot downloaded, waiting...')
	        time.sleep(0.5)
	print('\tDone')

	print('Writing file to disk : %s' % path)
	if path is not None: filepath = os.path.join(path, filename)
	else: filepath = filename
	#print filepath
	fp = open(filepath, 'wb')
	fp.write(base64.b64decode(downloaded_file))
	fp.close()
	print('\tDone')


def GetDriver():
	#from selenium.webdriver.firefox.options import Options
	#options = Options()
	#options.add_argument("--headless")
	#driver = webdriver.Firefox(firefox_options=options)
	driver = webdriver.Remote(command_executor='http://127.0.0.1:8910', desired_capabilities=DesiredCapabilities.PHANTOMJS)
	driver.implicitly_wait(10)
	return driver

def Start(site):
	print('MAKERSS START : %s' % site['TORRENT_SITE_TITLE'])
	for cate in site['BO_TABLE_LIST']:
		print('CATE : %s' % cate)
		list = GetList(driver, site, cate)
		if len(list) == 0: return -1
		str = MakeRssFeed(site['TORRENT_SITE_TITLE'], cate, list)
		#print('RSS : %s' % str)
		WriteFile('%s_%s.xml' % (site['TORRENT_SITE_TITLE'], cate), str)
		time.sleep(10)


if __name__ == "__main__":
	global driver
	driver = GetDriver()
	for site in SITE_LIST:
		try:
			Start(site)
		except:
			exc_info = sys.exc_info()
			traceback.print_exception(*exc_info)
			print('ERROR : %s' % site['TORRENT_SITE_TITLE'])
	driver.quit()

