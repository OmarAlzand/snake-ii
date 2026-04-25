# Snake II

A Nokia-inspired Snake clone. Plays in any browser. Runs on any cheap shared host.

🎮 **Live demo:** [alzandware.iq](https://alzandware.iq)

![Snake II screenshot](screenshot.png)

## Features

- 🐍 **99 different food sprites** — every game shows a new pixel-art treat
- 📱 **Mobile + desktop** — works with keyboard, touch d-pad, or swipe
- 🏆 **Global leaderboard** — top 5 scores, persisted on your own server
- 🌊 **Smooth motion** — frame interpolation makes the snake glide between cells
- ⚡ **Hold to boost** — keep pressing your direction for 2.2× speed
- 🎵 **Synthesised retro sounds** — generated with Web Audio, no sound files
- 📳 **Haptic feedback** — vibrates on mobile when you turn, eat, or die
- 🌙 **Auto-pause** — stops when you switch tabs or apps
- 🎨 **Pure pixel art** — no images, every sprite drawn with code

## Quick start (just play it locally)

You can run the game directly on your computer with no setup at all — you just won't have the global leaderboard.

1. Clone or [download this repo](https://github.com/OmarAlzand/snake-ii/archive/refs/heads/main.zip)
2. Open `index.html` in any browser

That's it. The game uses your browser's `localStorage` to save high scores locally.

## Full setup with leaderboard (5 minutes)

To run it on your own website with a global leaderboard:

### What you need

- Any web host that supports **PHP 7+** and **MySQL** (cPanel, Plesk, shared hosting, VPS — anything)
- HTTPS enabled (required by browsers for the leaderboard to work)

### Step 1 — Create a database

In your hosting control panel, create a new MySQL database and a user with full access to it. Note down:

- Database host (usually `localhost`)
- Database name
- Username
- Password

You don't need to create any tables — the game does that automatically on first run.

### Step 2 — Configure the game

1. Copy `config.example.php` to `config.php`
2. Open `config.php` and fill in your database details
3. Generate a random `APP_SECRET` and paste it in:

   ```bash
   openssl rand -hex 16
   ```

   Or just type a random long string. It just has to be something nobody else knows.

### Step 3 — Match the secret in the game

Open `index.html`, search for `APP_SECRET`, and paste the **same** string you used in `config.php`:

```javascript
const APP_SECRET = 'your-secret-here-must-match-config.php';
```

### Step 4 — Upload

Drop these files into your web root (`public_html` on cPanel):

```
index.html
scores.php
config.php          ← your filled-in version
.htaccess
```

That's it. Open your domain in a browser and play.

## What does APP_SECRET do?

Without a secret, anyone could submit fake high scores by sending requests to `scores.php` with `score=999`. The secret prevents this:

- The game computes `sha256(score + moves + time + APP_SECRET)` and sends it with each score
- The server recomputes the same hash and rejects the score if they don't match
- Since cheaters don't know the secret, they can't fake scores

If you don't care about leaderboard cheating (e.g. it's a private game for friends), you can leave the placeholder values — it just becomes a tiny barrier rather than a real one.

## Controls

| Action          | Desktop                | Mobile           |
| --------------- | ---------------------- | ---------------- |
| Start / Restart | `Enter` or `Space`     | Tap or swipe     |
| Move            | Arrow keys or WASD     | D-pad or swipe   |
| Boost           | Hold direction (180ms) | Hold d-pad       |
| Pause           | `Esc` or `P`           | (auto on blur)   |
| Resume          | `Esc`, `Enter` or move | Tap or move      |
| Back to menu    | `Esc` after game over  | Tap after death  |

## Code structure

The game is a single HTML file with everything inline — no build step, no dependencies. The `<script>` block is divided into 12 numbered sections:

| Section          | What it does                                            |
| ---------------- | ------------------------------------------------------- |
| 1. Configuration | Tunable game constants                                  |
| 2. Food sprites  | 99 hand-drawn 5×5 pixel foods                           |
| 3. Audio         | Synthesised retro sounds via Web Audio                  |
| 4. Vibration     | Mobile haptic feedback                                  |
| 5. Leaderboard   | Talks to `scores.php`, falls back to `localStorage`     |
| 6. Canvas        | Drawing primitives and the pixel font                   |
| 7. Game state    | The snake, food, score, etc.                            |
| 8. Game logic    | One game step (move, eat, collide)                      |
| 9. Render        | Draw the world with smooth tick interpolation           |
| 10. Main loop    | `requestAnimationFrame` loop, ticks at the snake speed  |
| 11. Input        | Keyboard, touch d-pad, swipe                            |
| 12. Init         | Wire everything up                                      |

Look for the `── N. SECTION NAME ──` headers to navigate.

## Tweaking the game

All gameplay constants live at the top of the script in **section 1**. Try changing:

```javascript
const STARTING_SPEED = 145;     // ms per move at score 0 (lower = faster)
const FASTEST_SPEED  = 65;      // speed cap at high scores
const BOOST_MULTIPLIER = 2.2;   // how much faster boost makes you
const COLS = 30;                // grid width
const ROWS = 17;                // grid height
const CELL_SIZE = 12;           // pixels per cell
```

Adding a new food is also easy. In **section 2**, append to the `FOODS` array:

```javascript
{ name: 'Pretzel 2', sprite: [0x0A, 0x15, 0x0E, 0x15, 0x0A] },
```

Each row of the sprite is a 5-bit number, where `0x10` is the leftmost pixel. Drawing them on graph paper first helps.

## Troubleshooting

**The leaderboard doesn't update**
Open your browser's DevTools → Network tab. Try playing a round and see what `scores.php` returns. If it's a 500 error, your `config.php` probably has wrong database credentials.

**No sound**
Browsers require a user interaction before audio can play. The first key press, tap, or click unlocks it.

**No vibration on mobile**
Same reason — vibration only works after a user touch. Some browsers (Safari) don't support it at all.

**The snake feels too slow / too fast**
Tweak `STARTING_SPEED` and `FASTEST_SPEED` in section 1.

**My score got rejected**
The server has anti-cheat sanity checks: minimum 400ms per food eaten, minimum 3 moves per food. If you legitimately played faster, you can relax these limits in `scores.php`.

## License

MIT — see [LICENSE](LICENSE).

Free to use, fork, modify, and ship. Attribution appreciated but not required.

## Credits

Built by [Omar Alzand](https://github.com/OmarAlzand). Done by Code by Opus 4.7

Inspired by **Snake II** (Nokia, 2000). The original is one of the most-played video games in history — over 350 million phones shipped with it. This is a love-letter, not a clone of the assets.
