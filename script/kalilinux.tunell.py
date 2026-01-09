#!/usr/bin/env python2
#coding = utf-8
import socket
import sys
import os
import re
from threading import Thread

class Hack(object):
    def __init__(self,src_addr=None,dst_addr=None):
        self.src_addr = src_addr
        self.dst_addr = dst_addr

    def request(self,data):
        return data

    def response(self,data):
        return data

ROUTES = [
    {
        'name'      :'HTTP',
        'addr'      :('127.0.0.1',80),
        'route'     :b'^(GET|POST)',
        'hack'      :Hack,
    },{
        'name'      :'SOCKS5',
        'addr'      :('127.0.0.1',3128),
        'route'     :b'^\x05',
        'hack'      :Hack,
    },{
        'name'      :'SSH',
        'addr'      :('127.0.0.1',22),
        'route'     :b'^SSH',
        'hack'      :Hack,
    },{
        'name'      :'NC',
        'addr'      :('127.0.0.1',1337),
        'route'     :b'.*',
        'hack'      :Hack,
    }
]

class TcpTunnel(Thread):
    SOCKS = {}
    def __init__(self,srcsock,srcaddr):
        Thread.__init__(self)
        self.srcsock = srcsock
        self.srcaddr = srcaddr
        self.dstsock = self.SOCKS[srcsock] if srcsock in self.SOCKS else socket.socket(socket.AF_INET,socket.SOCK_STREAM)
        self.iskeep  = True

    def s(self,dstsock,srcsock):
        while self.iskeep:
            try:
                buff = dstsock.recv(10240)
            except Exception as e:
                break
            buff = self.hack.response(buff)
            srcsock.sendall(buff)
            if not buff:
                self.iskeep = False
                break
        srcsock.close()

    def run(self):
        while self.iskeep:
            try:
                buff = self.srcsock.recv(10240)
            except Exception as e:
                break
            if not buff:
                self.iskeep = False
                break
            if self.srcsock not in self.SOCKS:
                for value in ROUTES:
                    if re.search(value['route'],buff,re.IGNORECASE):
                        sys.stdout.write('[+] Connect %s%s <--> %s\n'%(value['name'],str(value['addr']),str(self.srcaddr)))
                        self.hack = value['hack'](self.srcaddr,value['addr'])
                        self.dstsock.connect(value['addr'])
                        break
                self.SOCKS[self.srcsock] = self.dstsock
                Thread(target=self.s,args=(self.dstsock,self.srcsock,)).start()
            # sys.stdout.write('[!]' + buff.encode('hex') + '[!]\n')
            buff = self.hack.request(buff)
            self.dstsock.sendall(buff)
        self.dstsock.close()
        try: sys.stdout.write('[+] DisConnect %s%s <--> %s\n'%(value['name'],str(value['addr']),str(self.srcaddr)))
        except: sys.stdout.write('[+] DisConnect\n')

class SockProxy(object):
    def __init__(self,host='0.0.0.0',port=8080,listen=100):
        self.host = host
        self.port = port
        self.listen = listen
        self.socks = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
        self.socks.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        self.socks.bind((self.host,self.port))

    def start(self):
        self.socks.listen(self.listen)
        print('Start Proxy Listen - %s:%s'%(self.host,self.port))
        while True:
            sock,addr = self.socks.accept()
            T = TcpTunnel(sock,addr)
            T.start()

if __name__ == '__main__':
    try:
        # c = SockProxy('0.0.0.0', 8080)
        c = SockProxy()
        c.start()
    except KeyboardInterrupt:
        sys.exit()