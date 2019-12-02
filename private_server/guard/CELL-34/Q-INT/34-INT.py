ansr=0
anssu=0
ansdiff=0
c = int(input())

for i in range(c):
    a = int(input())
    ansr=ansr+a
    
for i in range(c+1):
    anssu=anssu+i
    
ansdiff=anssu-ansr
print(ansdiff)