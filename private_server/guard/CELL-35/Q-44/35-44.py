start_list = [5, 3, 1, 2, 4]
square_list = []

# Your code here!
for numbers in start_list:
    square_list.append(numbers ** 2)
square_list.sort()
print(square_list)
square_list.sort()