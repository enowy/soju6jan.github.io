---
title: "KLive Server 설치 - U5"
date: 2018-10-14 08:26:28 -0900
categories: klive
---

# Klive Server 설치 - U5
U5 linux는 일반적인 환경이 아니기에 몇가지 작업이 추가되고, 서비스 등록방법이 다릅니다.
 - 참고
  + [Klive Server 설치](https://soju6jan.github.io/2018-10-14-klive_install/)
  + [카페 설명글](https://cafe.naver.com/mk802/27246)
 - 환경설치 후 Klive Server 설치 글을 따라하시고, 서비스 등록 부분만 적용하시면 됩니다.

***
## 환경설치
````
root@AOL-Debian:~# apt-get install git-core
root@AOL-Debian:~# apt-get install python-pip
root@AOL-Debian:~# apt-get install libevent-dev
root@AOL-Debian:~# apt-get install python-all-dev
````

## 서비스 등록
+ kliveProxy.sh 수정
  python 경로와 kliveProxy 경로 수정

  ````
  do_start() {
    if [ -z "$pid" ]; then
    echo "start klive server."
    cd /root/Klive/klive
    /root/Klive/klive/venv/bin/python /root/Klive/klive/kliveProxy.py &
    else
    echo "kilve server already running."
    fi
  }
  ````
+ 서비스 등록
 ````
 root@AOL-Debian:~/Klive/klive# mv kliveProxy.sh /etc/init.d/kliveProxy
 root@AOL-Debian:~/Klive/klive# chmod a+x /etc/init.d/kliveProxy
 root@AOL-Debian:~/Klive/klive# update-rc.d kliveProxy defaults
 root@AOL-Debian:~/Klive/klive# service kliveProxy start
 ````
 
