<?php
session_start();

// Initialize the zoo data if it's not already in the session
function initializeZoo() {
    if (!isset($_SESSION['zoo'])) {
        $_SESSION['zoo'] = [
            'time' => 0,
            'animals' => [
                'monkey' => [],
                'giraffe' => [],
                'elephant' => []
            ],
            'elephantDeathHour' => []
        ];

        // Create 5 animals for each type with full health
        for ($i = 1; $i <= 5; $i++) {
            $_SESSION['zoo']['animals']['monkey'][] = createAnimal($i);
            $_SESSION['zoo']['animals']['giraffe'][] = createAnimal($i);
            $_SESSION['zoo']['animals']['elephant'][] = createAnimal($i);
        }
    }
}

// Create a new animal array
function createAnimal($id) {
    return [
        'id' => $id,
        'health' => 100,
        'isDead' => false
    ];
}

// Generate a random float between min and max
function randomFloat($min, $max) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

// Reduce health of animals as time passes
function passHour() {
    $_SESSION['zoo']['time'] += 1;

    foreach ($_SESSION['zoo']['animals'] as $type => &$animals) {
        foreach ($animals as &$animal) {
            if ($animal['isDead']) {
                // Skip dead animals
                continue;
            }

            // Calculate health reduction (0-20% of current health)
            $reducePercent = randomFloat(0, 20);
            $reduceAmount = $animal['health'] * ($reducePercent / 100);
            $animal['health'] -= $reduceAmount;

            if ($animal['health'] < 0) {
                $animal['health'] = 0;
            }

            // Check death rules
            if ($type == 'monkey' && $animal['health'] < 30) {
                $animal['isDead'] = true;
            } elseif ($type == 'giraffe' && $animal['health'] < 50) {
                $animal['isDead'] = true;
            } elseif ($type == 'elephant') {
                handleElephantDeath($animal);
            }
        }
        unset($animal); // break reference
    }
    unset($animals); // break reference
}

// Special elephant death logic: health < 70 for two hours => death
function handleElephantDeath(&$animal) {
    $id = $animal['id'];
    $time = $_SESSION['zoo']['time'];

    if ($animal['health'] < 70) {
        if (!isset($_SESSION['zoo']['elephantDeathHour'][$id])) {
            // Mark the hour when health first dropped below 70
            $_SESSION['zoo']['elephantDeathHour'][$id] = $time;
        } else {
            // If still below threshold next hour, elephant dies
            if ($_SESSION['zoo']['elephantDeathHour'][$id] == $time - 1) {
                $animal['isDead'] = true;
                unset($_SESSION['zoo']['elephantDeathHour'][$id]);
            }
        }
    } else {
        // If health recovers above 70, remove the death timer
        if (isset($_SESSION['zoo']['elephantDeathHour'][$id])) {
            unset($_SESSION['zoo']['elephantDeathHour'][$id]);
        }
    }
}

// Feed animals to increase their health
function feedAnimals() {
    // Generate one random feeding % per animal type
    $feedBoosts = [
        'monkey'   => randomFloat(10, 25),
        'giraffe'  => randomFloat(10, 25),
        'elephant' => randomFloat(10, 25)
    ];

    foreach ($_SESSION['zoo']['animals'] as $type => &$animals) {
        foreach ($animals as &$animal) {
            if ($animal['isDead']) {
                continue;
            }

            // Use the single value for this animal type
            $increasePercent = $feedBoosts[$type];
            $increaseAmount = $animal['health'] * ($increasePercent / 100);
            $animal['health'] += $increaseAmount;

            if ($animal['health'] > 100) {
                $animal['health'] = 100;
            }

            // If elephant health recovers above 70, clear death timer
            if ($type == 'elephant' && $animal['health'] >= 70) {
                unset($_SESSION['zoo']['elephantDeathHour'][$animal['id']]);
            }
        }
    }
}

// Get the current zoo state
function getZooState() {
    return $_SESSION['zoo'];
}

// Main program execution
initializeZoo();

$action = isset($_POST['action']) ? $_POST['action'] : 'init';

if ($action == 'pass_hour') {
    passHour();
} elseif ($action == 'feed') {
    feedAnimals();
}
// else: init or unknown action - do nothing

// Return the current zoo state as JSON
header('Content-Type: application/json');
echo json_encode(getZooState());
exit;
