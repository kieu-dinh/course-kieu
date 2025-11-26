# Exercise 02 - Grade System

## Objective

Practice conditions with a student grading system.

---

## Task

Create `grades.php` that processes student scores.

### Requirements

1. Function `getLetterGrade(int $score): string`
   - 90-100: A
   - 80-89: B
   - 70-79: C
   - 60-69: D
   - Below 60: F

2. Function `isPassing(int $score): bool`
   - Returns true if score >= 60

3. Function `getGradeMessage(int $score): string`
   - Returns encouraging message based on grade

4. Function `processStudent(string $name, int $score): array`
   - Returns array with name, score, grade, passed, message

---

## Starter Code

```php
<?php

function getLetterGrade(int $score): string {
    // Use if/elseif or match
}

function isPassing(int $score): bool {
    // Your code
}

function getGradeMessage(int $score): string {
    $grade = getLetterGrade($score);

    // Return message based on grade
    // A: "Excellent work!"
    // B: "Great job!"
    // C: "Good effort!"
    // D: "You passed, but study more."
    // F: "Need improvement. Don't give up!"
}

function processStudent(string $name, int $score): array {
    return [
        'name' => $name,
        'score' => $score,
        'grade' => getLetterGrade($score),
        'passed' => isPassing($score),
        'message' => getGradeMessage($score),
    ];
}

// Test with students
$students = [
    ['name' => 'Alice', 'score' => 95],
    ['name' => 'Bob', 'score' => 82],
    ['name' => 'Charlie', 'score' => 71],
    ['name' => 'Diana', 'score' => 65],
    ['name' => 'Eve', 'score' => 45],
];

foreach ($students as $student) {
    $result = processStudent($student['name'], $student['score']);
    echo "{$result['name']}: {$result['score']} = {$result['grade']}";
    echo $result['passed'] ? " (PASSED)" : " (FAILED)";
    echo " - {$result['message']}\n";
}
```

---

## Expected Output

```
Alice: 95 = A (PASSED) - Excellent work!
Bob: 82 = B (PASSED) - Great job!
Charlie: 71 = C (PASSED) - Good effort!
Diana: 65 = D (PASSED) - You passed, but study more.
Eve: 45 = F (FAILED) - Need improvement. Don't give up!
```

---

## Bonus Challenges

1. Add function `calculateAverage(array $scores): float`
2. Add function `getClassStats(array $students): array` that returns:
   - Average score
   - Highest score
   - Lowest score
   - Pass rate (percentage)

---

## Checklist

- [ ] All grade ranges work correctly
- [ ] Edge cases work (90, 80, 70, 60, 59)
- [ ] Messages match grades
- [ ] Loop processes all students
