import hashlib
import base64
def md5(message):
    if type(message) == str:
        message = message.encode()
    if type(message) != bytes:
        raise Exception("message must be bytes or str")
    md5 = hashlib.md5()
    md5.update(message)
    return md5.hexdigest()

def base64_encode(data):
    if type(data) == bytes:
        return base64.b64encode(data)
    elif type(data) == str:
        return base64.b64encode(data.encode()).decode()
    else:
        raise Exception("data must be bytes or str")

def base64_decode(data):
    if type(data) == bytes:
        return base64.b64decode(data)
    elif type(data) == str:
        return base64.b64decode(data.encode()).decode()
    else:
        raise Exception("data must be bytes or str")

