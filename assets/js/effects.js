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
        snow.className = 'snow';
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
