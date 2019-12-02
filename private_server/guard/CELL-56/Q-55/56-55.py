def cube(number):
    return number * number * number
    
def by_three(number):
    if int(number / 3) * 3 == number:
        return cube(number)
    else:
        return False







        
print(by_three(12))
print(by_three(5))
