#!/usr/local/bin/python2.7

import sys
import os
import time
import logging
import smtplib
import socket
import environment as MyENV

from stompest.config import StompConfig
from stompest.protocol import StompSpec
from stompest.sync import Stomp
from subprocess import check_output
from daemon import runner
from logging.handlers import TimedRotatingFileHandler
from subprocess import CalledProcessError
from base64 import b64encode

LOGFILE = MyENV.logfile
PIDFILE = MyENV.pidfile
STDIN = '/dev/null'
STDOUT = '/dev/tty'
STDERR = '/dev/tty'
TIMEOUT = 5

processgid=0 # 1048 apache, run as root for now
processuid=0 # 1048 apache, run as root for now

hostname=socket.gethostname()

class TalisDaemon():
    def __init__(self):
        self.stdin_path = STDIN
        self.stdout_path = STDOUT
        self.stderr_path = STDERR
        self.pidfile_path =  PIDFILE
        self.pidfile_timeout = TIMEOUT

    def run(self):
        if(processgid > 0): os.setgid(processgid)
        if(processuid > 0): os.setuid(processuid)
        config = StompConfig('tcp://localhost:61613')
        topic = "/%s/%s" % (MyENV.queue_or_topic,MyENV.queue_or_topic_name) #"/queue/talis"
        client = Stomp(config)
        self.logger = logging.getLogger(MyENV.queue_or_topic_name) 
        self.logger.setLevel(logging.DEBUG)
        handler = TimedRotatingFileHandler(LOGFILE, when='midnight', interval=1, backupCount=30)
        formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
        handler.setFormatter(formatter)
        self.logger.addHandler(handler)

        try:
            self.logger.info("Daemon started with pid %d" % os.getpid())
            client.connect()
            client.subscribe(topic, {StompSpec.ACK_HEADER: StompSpec.ACK_CLIENT_INDIVIDUAL})
            self.logger.info("started listening")
            while True:
                frame = client.receiveFrame()
                body = frame.body
                self.logger.info("Received Frame [%s]" % body)
                encoded_body = b64encode(body)
                cmd = "%s %s" % (MyENV.the_devil,encoded_body)
                try:
                    check_output(cmd, shell=True)
                except CalledProcessError, e:
                    msg = "The devil failed with cmd %s exit status %d and output: %s" % (e.cmd, e.returncode, e.output)
                    self.logger.error(msg)
                client.ack(frame)
                
                    
        except Exception, e:
            msg = "Exception in %s: %s" % (sys.argv[0], str(e))
            self.logger.error(msg)
            exit(1)
            
if(sys.argv[1]=="status"):
    if(os.path.exists(PIDFILE)):
        pid = int(open(PIDFILE,"r").read())
        try:
            os.kill(pid,0)
            exit(0)
        except Exception, e:
            exit(1)
    else:
        exit(1)
        
app = TalisDaemon()
daemon_runner = runner.DaemonRunner(app)
daemon_runner.do_action()
