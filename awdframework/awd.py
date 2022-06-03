import threading

class Task(threading.Thread):
    
    def __init__(self, ips:list, port=80):
        threading.Thread.__init__(self)
        self.ips = ips
        self.port = port

    def exp(self,ip):
        """
        You should override `exp()`
        """
        pass

    def run(self):
        try:
            while len(self.ips):
                ip = self.ips.pop()
                self.exp(ip)
        except:
            pass


class AwdTask(Task):
    def __init__(self, ips: list, port=80):
        super().__init__(ips, port)
    
    def write_webshell(self,ip):
        pass
    
    def attack_use_webshell(self,ip):
        pass

    def run(self):
        try:
            while len(self.ips):
                ip = self.ips.pop()
                if self.attack_use_webshell.__code__.co_code != b'd\x00S\x00':
                    self.attack_use_webshell(ip)
                elif self.write_webshell.__code__.co_code != b'd\x00S\x00':
                    self.write_webshell(ip)
                elif self.exp.__code__.co_code != b'd\x01S\x00':
                    self.exp(ip)
                else:
                    print("[+]You must override `exp()` or `write_webshell()` or `attack_use_webshell()`")
        except:
            pass



class Attack:
    def __init__(self,ips_file,task_class=Task,port=80,thread_num=3,**args) -> None:
        with open(ips_file) as f:
            self.ips = f.read().split("\n")
        self.port = port
        self.thread_num = thread_num
        self.task_class = task_class

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
