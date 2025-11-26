# Exercise 5.2 - Vehicle Inheritance

## Objective

Learn class inheritance by creating a Vehicle parent class with specialized Car and Bike child classes.

## Duration

1.5-2 hours

## Task

Create a vehicle inheritance hierarchy that demonstrates parent and child classes, properties inheritance, and method overriding.

## Requirements

- [ ] Create a `Vehicle.php` file with a `Vehicle` abstract parent class or base class
- [ ] Add properties: `brand`, `model`, `year`, `speed`
- [ ] Add a constructor to initialize the properties
- [ ] Add methods: `accelerate()`, `brake()`, `getInfo()`
- [ ] Create a `Car.php` class that extends `Vehicle`
- [ ] Car should have additional property: `numDoors`
- [ ] Car should override `getInfo()` to include doors info
- [ ] Create a `Bike.php` class that extends `Vehicle`
- [ ] Bike should have additional property: `hasStorage`
- [ ] Bike should override `getInfo()` to include storage info
- [ ] Use `parent::` to call parent methods when needed

## Starter Files

Work in `Vehicle.php`, `Car.php`, and `Bike.php` - see starter code there.

## Expected Output

```
php Vehicle.php

=== Car ===
Brand: Toyota
Model: Camry
Year: 2023
Speed: 0 km/h
Doors: 4

After accelerating:
Speed: 100 km/h

After braking:
Speed: 50 km/h

=== Bike ===
Brand: Harley-Davidson
Model: Sportster
Year: 2022
Speed: 0 km/h
Has Storage: Yes

After accelerating:
Speed: 120 km/h
```

## Checklist

- [ ] Vehicle parent class created with properties
- [ ] Constructor initializes all properties
- [ ] Accelerate and brake methods work correctly
- [ ] Car class extends Vehicle properly
- [ ] Bike class extends Vehicle properly
- [ ] Both Car and Bike have their own properties
- [ ] getInfo() is properly overridden in both child classes
- [ ] parent:: used correctly when needed
- [ ] Code runs without errors

## Tips

- Use `extends` keyword to create child classes
- Use `parent::methodName()` to call parent methods
- Child classes inherit all parent properties and methods
- Use `$this->` to access properties inherited from parent
- Consider making the parent class abstract with `abstract class`
