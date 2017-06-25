#! /usr/bin/python
#encoding: utf-8

# CREATEE BY zhangzhidong
# DATE 2017/6/25

import time
import types

#开始时间
start_time = time.time()

# 输出文件
# file_result = open('result.log','w+')

index = 0

with open('string.log') as file:
    for line in file:
        if index > 100000:
            break
        arr = line.split('|')
        create_time = int(time.mktime(time.strptime(arr[0], '%Y-%m-%d %H:%M:%S')))
        ip = arr[1]


        if '.xml' in arr[2] or '.php' in arr[2] or '.jsp' in arr[2] or '.asp' in arr[2] or 'document' in arr[2]:
            continue

        requests = arr[2].split(' ')

        if requests[0]=='GET':
            pass
        elif requests[0]=="POST":
            pass
        else:
            continue

        print create_time, ip
        # file_result.write(line)
        index=index+1


#结束时间
end_time = time.time()

print "使用时间",end_time-start_time
