def cube(number):
    return number ** 3

def by_three(number):
    if number % 3 == 0:
        return cube(number)
    return False

        
print(by_three(12))
print(by_three(5))