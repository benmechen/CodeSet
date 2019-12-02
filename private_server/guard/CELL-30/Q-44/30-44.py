start_list = [5, 3, 1, 2, 4]
square_list = []

for start_list in start_list:
    # Your code here!
    number = start_list
    square_list.append(number**2)
    square_list.sort()
print(square_list)