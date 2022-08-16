def readlines(path: str) -> list:
    c = []
    with open(path) as f:
        c = [l.strip() for l in f.readlines()]
    return c


def readbytes(path: str):
    with open(path, "br") as f:
        return f.read()


def readstr(path: str):
    with open(path, "r") as f:
        return f.read()


def writelines(path: str, lines: list):
    with open(path, "w") as f:
        f.writelines([l + "\n" for l in lines])


def write(path: str, content):
    if type(content) == bytes:
        with open(path, "wb") as f:
            f.write(content)
    elif type(content) == str:
        with open(path, "w") as f:
            f.write(content)
    elif type(content) == list:
        writelines(path=path, lines=content)
    else:
        raise Exception("content must be type of str , bytes or list")