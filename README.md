# 🐒🦒🐘 Zoo Simulator (PHP Core)

A simple Zoo Simulator built with **PHP core** (no frameworks).  
Implements a simulation with Monkeys, Giraffes, and Elephants, each with health values, feeding, and death rules.

---

## ✨ Features
- Starts with **5 of each animal** (Monkey, Giraffe, Elephant).
- Each hour:
  - Animal health decays randomly by **0–20% of current health**.
  - Monkeys die if health < **30%**.
  - Giraffes die if health < **50%**.
  - Elephants cannot walk if health < **70%**, and die if still <70% after the next hour.
- Feeding:
  - Generates random **10–25% heal** per animal type.
  - Increases health by percentage of current health (capped at 100%).
  - Elephants can recover above 70% to avoid death.
- UI:
  - **Pass 1 Hour** button (simulates one hour).
  - **Feed Animals** button (applies heal).
  - Dynamic status display with health bars and elephant walk status.

---

## 🚀 Getting Started

### Requirements
- PHP **8.0+**
- No database or frameworks needed
- A local PHP server (`php -S`) or Apache/Nginx with PHP

### Run Locally
1. Clone this repo:
   ```bash
   git clone https://github.com/Ahmedkhnunar/zoo-simulator-php.git
   cd zoo-simulator-php

2. Start a PHP local server:

   ```bash
   php -S localhost:8000
   ```

3. Open your browser at:

   ```
   http://localhost:8000/index.php
   ```

---

## 🧪 Demo

* Click **Pass 1 Hour** → simulates health decay.
* Click **Feed Animals** → heals animals by random %.
* Watch animals die or recover based on thresholds.

---

## 📂 Project Structure

```
.
├── index.php    # Main UI + simulator logic
├── zoo.php      # Core PHP classes and animal rules
└── README.md    # Instructions
```

---

## 🔖 Notes

* Built without frameworks (pure PHP + JS + Tailwind).
* Deterministic testing possible by replacing random values with fixed numbers.

---
