from utils.django_job import WebshellModel

class Example(WebshellModel):
    """
    用于记录 webshell 信息
    """
    pass
Example.register()
