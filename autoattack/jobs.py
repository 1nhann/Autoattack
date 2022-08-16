from django.contrib import admin
from django.urls import path
urlpatterns = [
    path('', admin.site.urls),
]
#--------------------------------------------------------------------
# banner
from utils.encoder import base64_decode
s = "ICAgIBtbMDsxOzM0Ozk0bV9fXxtbMG0gICAgICAgICAbWzA7MzRtX18bWzBtICAgICAgICAgICAgICAbWzA7MzdtX18bWzBtICAbWzA7MzdtX18bWzBtICAgICAgICAgICAgIBtbMDsxOzMwOzkwbV9fG1swbSAgCiAgIBtbMDsxOzM0Ozk0bS8bWzBtICAgG1swOzE7MzQ7OTRtfBtbMG0gG1swOzM0bV9fG1swbSAgG1swOzM0bV9fLxtbMG0gG1swOzM0bS9fX19fXxtbMG0gIBtbMDszN21fX19fG1swbSAbWzA7MzdtXy8bWzBtIBtbMDszN20vXy8bWzBtIBtbMDszN20vX18bWzA7MTszMDs5MG1fX18bWzBtIBtbMDsxOzMwOzkwbV9fX19fXy8bWzBtIBtbMDsxOzMwOzkwbS9fXxtbMG0KICAbWzA7MzRtLxtbMG0gG1swOzM0bS98G1swbSAbWzA7MzRtfC8bWzBtIBtbMDszNG0vG1swbSAbWzA7MzRtLxtbMG0gG1swOzM0bS8bWzBtIBtbMDszN21fXy8bWzBtIBtbMDszN21fXxtbMG0gG1swOzM3bVwvG1swbSAbWzA7MzdtX18bWzBtIBtbMDszN21gLxtbMG0gG1swOzE7MzA7OTBtX18vG1swbSAbWzA7MTszMDs5MG1fXy8bWzBtIBtbMDsxOzMwOzkwbV9fG1swbSAbWzA7MTszMDs5MG1gLxtbMG0gG1swOzE7MzA7OTBtX18bWzA7MTszNDs5NG1fLxtbMG0gG1swOzE7MzQ7OTRtLy9fLxtbMG0KIBtbMDszNG0vG1swbSAbWzA7MzRtX19fG1swbSAbWzA7MzRtLxtbMG0gG1swOzM3bS9fLxtbMG0gG1swOzM3bS8bWzBtIBtbMDszN20vXy8bWzBtIBtbMDszN20vXy8bWzBtIBtbMDszN20vG1swbSAbWzA7MTszMDs5MG0vXy8bWzBtIBtbMDsxOzMwOzkwbS8bWzBtIBtbMDsxOzMwOzkwbS9fLxtbMG0gG1swOzE7MzA7OTBtL18vG1swbSAbWzA7MTszMDs5MG0vG1swOzE7MzQ7OTRtXy8bWzBtIBtbMDsxOzM0Ozk0bS8bWzBtIBtbMDsxOzM0Ozk0bS9fXy8bWzBtIBtbMDsxOzM0Ozk0bSw8G1swbSAgIAobWzA7MzdtL18vG1swbSAgG1swOzM3bXxfXF9fLF8vXF9fG1swOzE7MzA7OTBtL1xfX19fL1xfXyxfL1xfXxtbMDsxOzM0Ozk0bS9cX18vXF9fLF8vXF9fXy8bWzA7MzRtXy98X3wbWzBtCgoJCQkJCQkJCQkJG1szMm1BdXRob3I6IBtbMzNtMW5oYW5uChtbMG0="
print(base64_decode(s))

# --------------------------------------------------------------------
from django.core.management import call_command

#自动完成数据库的构建、admin用户的创建
call_command('makemigrations')
call_command('migrate')
call_command('initadmin')

# --------------------------------------------------------------------
# jobs 打开定时任务管理器

from utils.django_job import Scheduler
scheduler = Scheduler.init()

import autoattack.example.exp
# import autoattack.example.write_webshell
# import autoattack.example.get_flag
# import autoattack.example.pwn

# jobs 当中放的是一个个 tuple，表示要定时运行的函数和对应的 id
jobs = [
    (autoattack.example.exp.attacker.attack, "example.exp"),
    # (autoattack.example.write_webshell.attacker.attack,"example.webshell"),
    # (autoattack.example.get_flag.attacker.attack,"example.getflag"),
    # (autoattack.example.pwn.attacker.attack,"example.exp"),
]
jobs += [

]
scheduler.add_jobs(jobs,seconds=5) #每5秒执行一次
# scheduler.add_jobs_cron(jobs,hour="10-12",minute="20,40") #每天 10:20 10:40 11:20 11:40 12:20 12:40 执行一次



