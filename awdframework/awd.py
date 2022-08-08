import threading
import socket
from time import sleep
import abc

class Task(threading.Thread):
    def __init__(self):
        threading.Thread.__init__(self)
    
    @abc.abstractmethod
    def task(self):
        pass
    
    def run(self):
        try:
            self.task()
        except:
            return

class Attakc:
    def __init__(self,task_class,thread_num):
        self.task_class = task_class
        self.thread_num = thread_num
    def attack(self):
        tasks = []
        for i in range(self.thread_num):
            t = self.task_class()
            tasks.append(t)
            t.setDaemon(True)
            t.start()
        try:
            while len(tasks):
                sleep(10)
                for t in tasks:
                    if not t.is_alive():
                        tasks.remove(t)

        except KeyboardInterrupt:
            exit("User Quit")


class AwdTask(Task):
    def __init__(self, ips: list, port=80):
        super(AwdTask,self).__init__()
        self.ips = ips
        self.port = port
        
    def exp(self,ip):
        """
        You should override `exp()`
        """
        pass
    
    def write_webshell(self,ip):
        pass
    
    def attack_use_webshell(self,ip):
        pass

    def task(self):
        while len(self.ips):
            ip = self.ips.pop()
            if not self.check_connect(ip):
                print("[+] {:<20} is not down , cannot connect".format(f"{ip}:{self.port}"))
                continue
            if self.attack_use_webshell.__code__.co_code != b'd\x00S\x00':
                self.attack_use_webshell(ip)
            elif self.write_webshell.__code__.co_code != b'd\x00S\x00':
                self.write_webshell(ip)
            elif self.exp.__code__.co_code != b'd\x01S\x00':
                self.exp(ip)
            else:
                print("[+]You must override `exp()` or `write_webshell()` or `attack_use_webshell()`")

    def check_connect(self,ip):
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(1)
        try:
            sock.connect((ip, self.port))
        except:
            return False
        return True

class AwdAttack(Attakc):
    def __init__(self,ips:list,awd_task_class=AwdTask,port=80,thread_num=3,**args) -> None:
        super(AwdAttack,self).__init__(awd_task_class,thread_num)
        self.ips = ips
        self.port = port

    def attack(self):
        tasks = []
        ips = self.ips.copy()
        for i in range(self.thread_num):
            t = self.task_class(ips=ips,port=self.port)
            tasks.append(t)
            t.setDaemon(True)
            t.start()
        try:
            while len(tasks):
                for t in tasks:
                    if not t.is_alive():
                        tasks.remove(t)

        except KeyboardInterrupt:
            exit("User Quit")
