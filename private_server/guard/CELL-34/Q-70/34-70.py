class Employee(object):
    """Models real-life employees!"""
    def __init__(self, employee_name):
        self.employee_name = employee_name

    def calculate_wage(self, hours):
        self.hours = hours
        return hours * 20.00

# Add your code below!
class PartTimeEmployee(Employee):
    def __init__(self, employee_name):
        self.employee_name = employee_name
    
    def calculate_wage(self, hours):
        self.hours = hours
        return hours * 12.00



        
employee = PartTimeEmployee("Brad")
print(employee.calculate_wage(10))