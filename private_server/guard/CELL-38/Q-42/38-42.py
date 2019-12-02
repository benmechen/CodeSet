animals = ["aardvark", "badger", "duck", "emu", "fennec fox"]
# Use index() to find "duck"
duck_index = animals.index("duck")

animals.insert(duck_index, "cobra")


# Observe what prints after the insert operation
print(animals)