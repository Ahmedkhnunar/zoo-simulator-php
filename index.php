<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoo Simulator (PHP/AJAX)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fdf4;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .progress-bar-container {
            height: 8px;
            background-color: #e5e7eb;
        }
        .progress-bar {
            transition: width 0.5s ease-in-out, background-color 0.3s;
        }
        .dead-animal {
            background-color: #e5e7eb;
            color: #6b7280;
        }
        .dead-health {
            color: #6b7280;
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <div class="container mx-auto">
        <!-- Header and Simulation Time -->
        <header class="text-center mb-8">
            <h1 class="text-4xl sm:text-5xl font-bold text-green-700 mb-2">Zoo Simulator</h1>
            <div id="time-display" class="text-lg sm:text-xl font-semibold text-gray-700">Current Time: Hour 0</div>
        </header>

        <!-- Animal Status Display -->
        <main id="animal-display" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        </main>

        <!-- Control Panel -->
        <footer class="flex flex-col sm:flex-row justify-center items-center gap-4">
            <button id="pass-hour-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 w-full sm:w-auto text-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Pass 1 Hour
            </button>
            <button id="feed-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 w-full sm:w-auto text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                Feed Animals
            </button>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timeDisplay = document.getElementById('time-display');
            const animalDisplay = document.getElementById('animal-display');
            const passHourBtn = document.getElementById('pass-hour-btn');
            const feedBtn = document.getElementById('feed-btn');

            // --- Utility Functions for UI Rendering ---
            const renderZoo = (zooState) => {
                timeDisplay.textContent = `Current Time: Hour ${zooState.time}`;
                animalDisplay.innerHTML = '';

                const thresholds = { monkey: 30, giraffe: 50, elephant: 70 };
                const types = Object.keys(zooState.animals);

                types.forEach(type => {
                    const container = document.createElement('div');
                    container.className = 'card bg-white p-6 rounded-lg transition-all';
                    container.innerHTML = `
                        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex items-center">
                            ${type.charAt(0).toUpperCase() + type.slice(1)}s 
                            <span class="ml-2">${type === 'monkey' ? '🐒' : type === 'giraffe' ? '🦒' : '🐘'}</span>
                        </h2>
                        <ul class="space-y-4"></ul>
                    `;
                    const list = container.querySelector('ul');

                    zooState.animals[type].forEach(animal => {
                        const listItem = document.createElement('li');
                        listItem.className = 'flex flex-col gap-1 p-3 rounded-md bg-gray-50';

                        const healthPercentage = parseFloat(animal.health).toFixed(0);
                        const warningThreshold = thresholds[type] || 50;

                        let healthBarColor = 'bg-red-500';
                        if (animal.health > (warningThreshold + 15)) {
                            healthBarColor = 'bg-green-500';
                        } else if (animal.health > (warningThreshold + 10)) {
                            healthBarColor = 'bg-yellow-500';
                        }

                        // Dead animals display
                        if (animal.isDead) {
                            listItem.classList.add('opacity-50', 'dead-animal');
                            listItem.innerHTML = `
                                <div class="flex justify-between items-center text-sm font-semibold text-gray-800">
                                    <span>🪦 ${type.charAt(0).toUpperCase() + type.slice(1)} #${animal.id}</span>
                                    <span class="dead-health">Deceased</span>
                                </div>
                                <div class="progress-bar-container w-full rounded-full">
                                    <div class="progress-bar h-full rounded-full bg-gray-400" style="width: 100%;"></div>
                                </div>
                            `;
                        } else {
                            listItem.innerHTML = `
                                <div class="flex justify-between items-center text-sm font-semibold text-gray-800">
                                    <span>${type.charAt(0).toUpperCase() + type.slice(1)} #${animal.id}</span>
                                    <span>${healthPercentage}%</span>
                                </div>
                                <div class="progress-bar-container w-full rounded-full">
                                    <div class="progress-bar h-full rounded-full ${healthBarColor}" style="width: ${healthPercentage}%;"></div>
                                </div>
                                ${type === 'elephant' && animal.health < 70 ? 
                                    `<div class="text-sm italic text-orange-500">Cannot walk</div>` : ''}
                            `;
                        }

                        list.appendChild(listItem);
                    });

                    animalDisplay.appendChild(container);
                });
            };

            // --- AJAX and Event Handlers ---
            const updateZoo = async (action) => {
                try {
                    const formData = new URLSearchParams();
                    formData.append('action', action);

                    const response = await fetch('zoo.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const zooState = await response.json();
                    renderZoo(zooState);

                } catch (err) {
                    console.error('Error updating zoo:', err);
                    animalDisplay.innerHTML = `
                        <div class="col-span-full text-center text-red-600 font-semibold">
                            Failed to load zoo data. Please refresh the page.
                        </div>
                    `;
                }
            };

            passHourBtn.addEventListener('click', () => updateZoo('pass_hour'));
            feedBtn.addEventListener('click', () => updateZoo('feed'));

            // Initial load of the zoo state
            updateZoo('init');
        });
    </script>
</body>
</html>
