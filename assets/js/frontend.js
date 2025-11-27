jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Advent Calendar Frontend JS loaded!');
    console.log('adventCalendar object:', adventCalendar); 
    console.log('ajaxurl:', adventCalendar.ajaxurl);
    console.log('nonce:', adventCalendar.nonce);
    
    const AdventCalendar = {
        init: function() {
            console.log('Initializing Advent Calendar...');
            this.bindEvents();
            this.checkOpenedDoors();
            this.checkLocalStorageDoors();
        },
        
        bindEvents: function() {
            $('.advent-calendar-door.available').on('click', this.openDoor.bind(this));
            $(document).on('click', '.advent-modal-close, .advent-modal', this.closeModal.bind(this));
        },
        
        openDoor: function(e) {
    e.preventDefault();
    const door = $(e.currentTarget);
    
    if (door.hasClass('loading') || door.hasClass('open')) {
        return;
    }
    
    door.addClass('loading');
    
    const doorId = door.data('door-id');
    const calendarId = door.data('calendar-id');
    
    // Pobierz user_session z localStorage LUB utwórz nowy
    let userSession = localStorage.getItem('advent_calendar_session');
    if (!userSession) {
        userSession = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('advent_calendar_session', userSession);
        
        // Zapisz też w cookie dla PHP
        document.cookie = "advent_calendar_user_session=" + userSession + "; max-age=" + (365*24*60*60) + "; path=/";
    }
    
    $.ajax({
        url: adventCalendar.ajaxurl,
        type: 'POST',
        data: {
            action: 'open_door',
            door_id: doorId,
            calendar_id: calendarId,
            user_session: userSession,
            nonce: adventCalendar.nonce
        },
        success: (response) => {
            door.removeClass('loading');
            
            if (response.success) {
                this.handleDoorOpenSuccess(door, response.data);
                // Oznacz drzwi jako otwarte w localStorage
                this.markDoorAsOpened(doorId, userSession);
            } else {
                this.handleDoorOpenError(door, response.data);
            }
        },
        error: () => {
            door.removeClass('loading');
            this.showError('Błąd połączenia. Spróbuj ponownie.');
        }
    });
},

// Dodaj nową funkcję
markDoorAsOpened: function(doorId, userSession) {
    let openedDoors = JSON.parse(localStorage.getItem('advent_opened_doors') || '{}');
    if (!openedDoors[userSession]) {
        openedDoors[userSession] = [];
    }
    if (!openedDoors[userSession].includes(doorId)) {
        openedDoors[userSession].push(doorId);
        localStorage.setItem('advent_opened_doors', JSON.stringify(openedDoors));
    }
},
        
        handleDoorOpenSuccess: function(door, data) {
    door.addClass('open door-animation-' + data.animation);
    
    if (data.effects && data.effects.length) {
        data.effects.forEach(effect => {
            this.playEffect(effect);
        });
    }
    
    setTimeout(() => {
        // Usuń przyciemnienie jeśli jest obrazek
        if (door.find('.door-image-container').length) {
            door.find('.door-image-container').removeClass('closed').addClass('opened');
            door.find('.door-overlay').remove();
        } else if (door.find('.door-default-content').length) {
            // Dla drzwi bez obrazka
            door.find('.door-default-content').removeClass('closed').addClass('opened');
            door.find('.door-icon').text('🎁');
        }
        
        if (data.door_type === 'modal') {
            this.showModal(data.content);
        } else if (data.door_type === 'link' && data.link_url) {
            window.open(data.link_url, '_blank');
        }
        
        door.addClass('door-success');
    }, 600);
},
        
        handleDoorOpenError: function(door, error) {
            this.showError(error || 'Wystąpił błąd podczas otwierania drzwi.');
        },
        
        showModal: function(content) {
            const modal = $(
                '<div class="advent-modal active">' +
                '<div class="advent-modal-content">' +
                '<button class="advent-modal-close">&times;</button>' +
                '<div class="door-content-wrapper">' + content + '</div>' +
                '</div>' +
                '</div>'
            );
            
            $('body').append(modal);
            $('body').addClass('modal-open');
        },
        
        closeModal: function(e) {
            if ($(e.target).hasClass('advent-modal-close') || $(e.target).hasClass('advent-modal')) {
                $('.advent-modal').remove();
                $('body').removeClass('modal-open');
            }
        },
        
        showInlineContent: function(door, content) {
            door.find('.door-content').html(content);
        },
        
        playEffect: function(effect) {
            switch(effect) {
                case 'confetti':
                    this.createConfetti();
                    break;
                case 'snow':
                    this.createSnow();
                    break;
                case 'sparkle':
                    this.createSparkles();
                    break;
            }
        },
        
        createConfetti: function() {
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
            const container = $('<div class="confetti-container"></div>');
            $('body').append(container);
            
            for (let i = 0; i < 150; i++) {
                const confetti = $('<div class="confetti"></div>');
                confetti.css({
                    left: Math.random() * 100 + 'vw',
                    background: colors[Math.floor(Math.random() * colors.length)],
                    animationDuration: (Math.random() * 3 + 2) + 's',
                    width: (Math.random() * 10 + 5) + 'px',
                    height: (Math.random() * 10 + 5) + 'px'
                });
                
                container.append(confetti);
                
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
            
            setTimeout(() => {
                container.remove();
            }, 5000);
        },
        
        createSnow: function() {
            const container = $('<div class="snow-container"></div>');
            const snowflakes = ['❄', '❅', '❆'];
            $('body').append(container);
            
            for (let i = 0; i < 50; i++) {
                const snow = $('<div class="snowflake"></div>');
                snow.text(snowflakes[Math.floor(Math.random() * snowflakes.length)]);
                snow.css({
                    left: Math.random() * 100 + 'vw',
                    animationDuration: (Math.random() * 5 + 5) + 's',
                    fontSize: (Math.random() * 10 + 15) + 'px'
                });
                
                container.append(snow);
                
                setTimeout(() => {
                    snow.remove();
                }, 10000);
            }
            
            setTimeout(() => {
                container.remove();
            }, 10000);
        },
        
        createSparkles: function() {
            const container = $('body');
            const sparkleCount = 30;
            
            for (let i = 0; i < sparkleCount; i++) {
                const sparkle = $('<div class="sparkle"></div>');
                sparkle.css({
                    left: Math.random() * 100 + 'vw',
                    top: Math.random() * 100 + 'vh',
                    animationDelay: Math.random() * 2 + 's'
                });
                
                container.append(sparkle);
                
                setTimeout(() => {
                    sparkle.remove();
                }, 2000);
            }
        },
        
        showError: function(message) {
            const errorDiv = $('<div class="advent-calendar-error">' + message + '</div>');
            $('body').append(errorDiv);
            
            setTimeout(() => {
                errorDiv.fadeOut(() => errorDiv.remove());
            }, 3000);
        },

        checkLocalStorageDoors: function() {
        const userSession = localStorage.getItem('advent_calendar_session');
        if (!userSession) return;
        
        const openedDoors = JSON.parse(localStorage.getItem('advent_opened_doors') || '{}');
        const userOpenedDoors = openedDoors[userSession] || [];
        
        userOpenedDoors.forEach(doorId => {
            const $door = $('.advent-calendar-door[data-door-id="' + doorId + '"]');
            if ($door.length && !$door.hasClass('open')) {
                $door.removeClass('available locked').addClass('open');
                // Możesz też załadować zawartość drzwi
                this.loadDoorContent(doorId, $door);
            }
        });
    },

        loadDoorContent: function(doorId, $door) {
        $.ajax({
            url: adventCalendar.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_door_content',
                door_id: doorId,
                nonce: adventCalendar.nonce
            },
            success: (response) => {
                if (response.success) {
                    $door.find('.door-content').html(response.data.content);
                }
            }
        });
    },
        
        checkOpenedDoors: function() {
            $('.advent-calendar-door.open').each(function() {
                const door = $(this);
                const doorId = door.data('door-id');
                
                $.ajax({
                    url: adventCalendar.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_door_content',
                        door_id: doorId,
                        nonce: adventCalendar.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            door.find('.door-content').html(response.data.content);
                        }
                    }
                });
            });
        }
    };
    
    AdventCalendar.init();
});
