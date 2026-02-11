// Get the h1 element
const greetingEl = document.getElementById('greeting');

// Get current hour (0-23)
const now = new Date();
const hour = now.getHours();

let greetingText = '';
let emoji = '👑';

// Determine greeting based on hour
if (hour >= 5 && hour < 12) {
    greetingText = 'Magandang Umaga';
} else if (hour >= 12 && hour < 18) {
    greetingText = 'Magandang Hapon';
} else {
    greetingText = 'Magandang Gabi';
}

// Update the h1 content
greetingEl.textContent = `${greetingText} ${emoji}`;
