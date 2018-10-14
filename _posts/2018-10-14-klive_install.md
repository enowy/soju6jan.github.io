---
title: "KLive Server 설치"
date: 2018-10-14 08:26:28 -0900
categories: klive
---

# Klive Server
Klive Server는 설정된 환경에 맞게 M3U와 EPG 파일을 만들어서 클라이언트에 전송하는 역할을 하며, 주기적으로 파일을 경신한다.
> 사용환경 : OS에 상관없이 python 2.7이 구동되는 환경이면 작동한다

***
## 소스 다운로드
 + git clone
````
soju6jan@soju6jan-ubuntu:~$ git clone https://github.com/soju6jan/Klive.git
'Klive'에 복제합니다...
remote: Counting objects: 152, done.
remote: Compressing objects: 100% (103/103), done.
remote: Total 152 (delta 68), reused 108 (delta 39), pack-reused 0
오브젝트를 받는 중: 100% (152/152), 146.04 KiB | 111.00 KiB/s, 완료.
델타를 알아내는 중: 100% (68/68), 완료.
연결을 확인하는 중입니다... 완료.
````

 + lib 폴더 복사
````
soju6jan@soju6jan-ubuntu:~$ cd Klive/
soju6jan@soju6jan-ubuntu:~/Klive$ mv lib/ klive/
soju6jan@soju6jan-ubuntu:~/Klive$ cd klive/
````
****
## python 세팅
 이미 설정된 환경이면 패스. 반드시 가상환경을 사용해야 하는건 아님
 + pip 설치
````
soju6jan@soju6jan-ubuntu:~/Klive/klive$ sudo apt install python-pip
[sudo] password for soju6jan:
패키지 목록을 읽는 중입니다... 완료
의존성 트리를 만드는 중입니다
상태 정보를 읽는 중입니다... 완료
다음 패키지가 자동으로 설치되었지만 더 이상 필요하지 않습니다:
  libavdevice-ffmpeg56 libsdl1.2debian linux-headers-4.13.0-36
  linux-headers-4.13.0-36-generic linux-headers-4.13.0-37
````
<br><br>
 + 필요 패키지 설치 (virtualenv)
````
soju6jan@soju6jan-ubuntu:~/Klive/klive$ pip install virtualenv
Collecting virtualenv
  Downloading https://files.pythonhosted.org/packages/b6/30/96a02b2287098b23b875bc8c2f58071c35d2efe84f747b64d523721dc2b5/virtualenv-16.0.0-py2.py3-none-any.whl (1.9MB)
    100% |████████████████████████████████| 1.9MB 201kB/s
Installing collected packages: virtualenv
Successfully installed virtualenv
````

 + 가상환경 세팅
````
soju6jan@soju6jan-ubuntu:~/Klive/klive$ virtualenv venv
New python executable in /home/soju6jan/Klive/klive/venv/bin/python
Installing setuptools, pip, wheel...done.
soju6jan@soju6jan-ubuntu:~/Klive/klive$ . venv/bin/activate
````

 + 필요 모듈 설치
````
(venv) soju6jan@soju6jan-ubuntu:~/Klive/klive$ pip install -r requirements.txt
Collecting flask (from -r requirements.txt (line 1))
  Downloading https://files.pythonhosted.org/packages/7f/e7/08578774ed4536d3242b14dacb4696386634607af824ea997202cd0edb4b/Flask-1.0.2-py2.py3-none-any.whl (91kB)
    100% |████████████████████████████████| 92kB 391kB/s
````
***
#### - 세팅 수정
 + settings.py 수정
```python
config = {
	'bindAddr':'soju6jan.iptime.org',
    'bindPort': 9801,
	# for plex tvhproxy
    'tvhURL': 'http://soju6jan:dlgkdbs02!0@192.168.0.15:9981',
    'tunerCount': 6,
    'tvhWeight': 300,
    'streamProfile': 'pass',
    'ffmpeg' : 'ffmpeg',
}
````
 + 각 사이트 계정정보 입력
 + 커스텀 설정
 > 자신만의 채널목록을 수정하려면 USE_CUSTOM 을 ```true```로 설정

   ```python
   USE_CUSTOM				= True
   USE_CUSTOM_SOURCE		= 'custom.txt'
   USE_CUSTOM_SPLIT_CHAR	= ':'
   USE_CUSTOM_REGEX		= '^(?P<channel_id>.*?)%s(?P<channel_number>.*?)%s(?P<channel_name>.*?)$' % (USE_CUSTOM_SPLIT_CHAR, USE_CUSTOM_SPLIT_CHAR)
   USE_CUSTOM_M3U			= 'klive_custom.m3u'
   USE_CUSTOM_EPG			= 'klive_custom.xml'
   ````
 + custom.txt
 ```
 #지상파
 KBS|11:1:
 POOQ|K01:1-1:KBS1(푹)
 KBS|12:2:
 POOQ|K02:2-1:KBS2(푹)
 MBC|01:3:
 POOQ|M01:3-1: MBC(푹)
 SBS|S01:4:
 OLLEH|241:5:

 #
 TVING|C00551:11:
 OLLEH|280:11-2:tvN (올레)
 OKSUSU|872:11-1:tvN (옥수수)

 CHANNEL_NUMBER_START:21
 #PLAY
 OLLEH|453::
 OLLEH|452::
 ```
  + 기본형식 : ```[ID]:[채널번호]:[채널이름]```
  + 채널번호와, 채널이름 생략 가능
  + ```#``` 주석처리
  + ```CHANNEL_NUMBER_START``` 채널번호가 없을 때 시작 채널번호
***
#### 실행
````
(venv) soju6jan@soju6jan-ubuntu:~/Klive/klive$ python kliveProxy.py
````
***
#### 서비스 등록
+ kliveProxy.service 수정

  ````
  [Unit]
  Description=KLive Server

  [Service]
  Environment=
  WorkingDirectory=/home/soju6jan/Klive/klive/
  ExecStart=/home/soju6jan/Klive/klive/venv/bin/python /home/soju6jan/Klive/klive/kliveProxy.py
  Restart=always

  [Install]
  WantedBy=multi-user.target
  ````

+ 서비스 등록 설정
  ````
  sudo cp kliveProxy.service /etc/systemd/system/
  sudo systemctl daemon-reload
  sudo systemctl enable kliveProxy.service
  sudo systemctl start kliveProxy.service
  ````

- 서비스 관련 명령
  ````
  sudo service kliveProxy stop
  sudo service kliveProxy start
  sudo service kliveProxy restart
  ````
