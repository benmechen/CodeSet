# -*- coding: utf-8 -*-
# @Author: Ben
# @Date:   2017-02-04 13:01:56
# @Last Modified by:   Ben
# @Last Modified time: 2017-08-28 22:27:48

#!/usr/bin/env python
import sys
import os

def compileThenExec(file):
    ns = {}
    sys.modules['os'] = None
    sys.modules['sys'] = None
    sys.modules['shutil'] = None
    sys.modules['subprocess'] = None
    try:
        exec(open(file).read(), ns)
    except MemoryError:
        print("Error: Your program exceeds the memory limit")
    except EOFError:
        print("Error: Input commands disabled on this interpreter")
    except ImportError:
        print("Error: Python library blocked")
    except Exception as inst:
        print('Error:', str(inst.args[0]).capitalize())

## Set up secure environment to run code

file = input()

os.chdir(os.path.split(file+"/")
compileThenExec(file)