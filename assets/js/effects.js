function createConfetti() {
    const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
    const container = document.body;
    
    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        confetti.style.width = (Math.random() * 10 + 5) + 'px';
        confetti.style.height = (Math.random() * 10 + 5) + 'px';
        
        container.appendChild(confetti);
        
        setTimeout(() => {
            confetti.remove();
        }, 5000);
    }
}

function createSnow() {
    const container = document.body;
    const snowflakes = ['❄', '❅', '❆'];
    
    for (let i = 0; i < 50; i++) {
        const snow = document.createElement('div');
        snow.className = 'snowflake';
        snow.textContent = snowflakes[Math.floor(Math.random() * snowflakes.length)];
        snow.style.left = Math.random() * 100 + 'vw';
        snow.style.animationDuration = (Math.random() * 5 + 5) + 's';
        snow.style.fontSize = (Math.random() * 10 + 15) + 'px';
        
        container.appendChild(snow);
        
        setTimeout(() => {
            snow.remove();
        }, 10000);
    }
}

function createSparkles() {
    const container = document.body;
    const sparkleCount = 30;
    
    for (let i = 0; i < sparkleCount; i++) {
        const sparkle = document.createElement('div');
        sparkle.className = 'sparkle';
        sparkle.style.left = Math.random() * 100 + 'vw';
        sparkle.style.top = Math.random() * 100 + 'vh';
        sparkle.style.animationDelay = Math.random() * 2 + 's';
        
        container.appendChild(sparkle);
        
        setTimeout(() => {
            sparkle.remove();
        }, 2000);
    }
}

function createStars() {
    const container = document.body;
    const starCount = 20;
    
    for (let i = 0; i < starCount; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + 'vw';
        star.style.top = Math.random() * 100 + 'vh';
        star.style.animationDelay = Math.random() * 2 + 's';
        
        container.appendChild(star);
        
        setTimeout(() => {
            star.remove();
        }, 3000);
    }
}

jQuery(document).ready(function($) {
    window.adventCalendarEffects = {
        confetti: createConfetti,
        snow: createSnow,
        sparkles: createSparkles,
        stars: createStars
    };
});
