jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Advent Calendar Frontend JS loaded!');
    
    const AdventCalendar = {
        init: function() {
            console.log('Initializing Advent Calendar...');
            this.bindEvents();
            this.initializeUserSession();
            this.checkLocalStorageDoors();
            this.cleanupModals(); // DODAJ TE LINIĘ - czyści ewentualne pozostałe modale
        },
        
        bindEvents: function() {
            // Delegated event for better performance
            $(document).on('click', '.advent-calendar-door.available', this.openDoor.bind(this));
            $(document).on('click', '.advent-modal-close, .advent-modal', this.closeModal.bind(this));
            
            // Escape key to close modal
            $(document).on('keydown', this.handleKeydown.bind(this));
        },
        
        // DODAJ NOWĄ METODĘ do czyszczenia modalów
        cleanupModals: function() {
            // Usuń wszystkie modale które mogły pozostać z poprzedniego ładowania
            $('.advent-modal').remove();
            $('body').removeClass('modal-open');
        },
        
        initializeUserSession: function() {
            // Initialize user session if not exists
            let userSession = localStorage.getItem('advent_calendar_session');
            if (!userSession) {
                userSession = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('advent_calendar_session', userSession);
                
                // Save in cookie for PHP compatibility
                this.setCookie('advent_calendar_user_session', userSession, 365);
            }
            return userSession;
        },
        
        checkLocalStorageDoors: function() {
            const userSession = localStorage.getItem('advent_calendar_session');
            if (!userSession) {
                console.log('No user session found, skipping door check');
                return;
            }
            
            const openedDoors = JSON.parse(localStorage.getItem('advent_opened_doors') || '{}');
            const userOpenedDoors = openedDoors[userSession] || [];
            
            console.log('Found opened doors in localStorage:', userOpenedDoors);
            
            // DODAJ TIMEOUT aby upewnić się że DOM jest w pełni załadowany
            setTimeout(() => {
                userOpenedDoors.forEach(doorId => {
                    const $door = $('.advent-calendar-door[data-door-id="' + doorId + '"]');
                    if ($door.length && !$door.hasClass('open')) {
                        console.log('Marking door as opened from localStorage:', doorId);
                        $door.removeClass('available locked').addClass('open');
                        
                        // POPRAWKA: TYLKO aktualizuj wizualnie drzwi, NIE pokazuj modala
                        this.updateDoorVisualState($door);
                    }
                });
            }, 100);
        },

        updateDoorVisualState: function($door, doorData = null) {
    const doorId = $door.data('door-id');
    
    // Sprawdź czy drzwi mają obrazek
    if (doorData && doorData.image_url) {
        // Ustaw obrazek jako tło
        $door.css({
            'background-image': 'url("' + doorData.image_url + '")',
            'background-size': 'cover',
            'background-position': 'center'
        });
        
        // Dodaj overlay dla lepszej czytelności
        if (!$door.find('.door-image-overlay').length) {
            $door.append('<div class="door-image-overlay"></div>');
        }
        
        // Usuń domyślną zawartość
        $door.find('.door-default-content').remove();
    } else {
        // Brak obrazka - standardowy wygląd
        if ($door.find('.door-image-container').length) {
            $door.find('.door-image-container').removeClass('closed').addClass('opened');
            $door.find('.door-overlay').remove();
        } else if ($door.find('.door-default-content').length) {
            $door.find('.door-default-content').removeClass('closed').addClass('opened');
            $door.find('.door-icon').text('🎁');
        }
    }
    
    // Dodaj styl dla otwartych drzwi
    $door.addClass('open').removeClass('available locked');
},
        
        setCookie: function(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Strict";
        },
        
        openDoor: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const door = $(e.currentTarget);
            
            // Prevent multiple clicks
            if (door.hasClass('loading') || door.hasClass('open')) {
                return;
            }
            
            door.addClass('loading');
            
            const doorId = door.data('door-id');
            const calendarId = door.data('calendar-id');
            const userSession = this.initializeUserSession();
            
            console.log('Opening door:', doorId, 'Calendar:', calendarId, 'Session:', userSession);
            
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
                    console.log('Door open response:', response);
                    door.removeClass('loading');
                    
                    if (response.success) {
                        this.handleDoorOpenSuccess(door, response.data);
                        this.markDoorAsOpened(doorId, userSession);
                    } else {
                        this.handleDoorOpenError(door, response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error, xhr);
                    door.removeClass('loading');
                    this.showError('Błąd połączenia. Spróbuj ponownie.');
                }
            });
        },

        handleDoorOpenSuccess: function(door, data) {
            console.log('Handling door open success:', data);
            
            // Handle already opened doors
            if (data.already_opened) {
                // POPRAWKA: Jeśli drzwi są już otwarte, tylko pokaż modal jeśli użytkownik kliknął
                this.showDoorContent(door, data);
                return;
            }
            
            // Add animation class
            const animation = data.animation || 'fade';
            door.addClass('open door-animation-' + animation);
            
            // Play effects if available
            if (data.effects && data.effects.length) {
                data.effects.forEach(effect => {
                    this.playEffect(effect);
                });
            }
            
            // Show content after animation
            setTimeout(() => {
                this.showDoorContent(door, data);
                door.addClass('door-success');
            }, 600);
        },

        showDoorContent: function(door, data) {
            console.log('Showing door content:', data);
            
            // Update door visual state
            this.updateDoorVisualState(door);
            
            // Handle different door types
            if (data.door_type === 'modal') {
                this.showModal(data);
            } else if (data.door_type === 'link' && data.link_url) {
                this.openExternalLink(data.link_url);
            }
        },

        showModal: function(data) {
            // Najpierw wyczyść istniejące modale
            this.cleanupModals();
            
            // Create modal structure
            const modal = $(
                '<div class="advent-modal active" tabindex="-1" role="dialog" aria-labelledby="modal-title" aria-hidden="true">' +
                '<div class="advent-modal-content" role="document">' +
                '<button class="advent-modal-close" aria-label="Zamknij">&times;</button>' +
                '<div class="door-content-wrapper"></div>' +
                '</div>' +
                '</div>'
            );
            
            const contentWrapper = modal.find('.door-content-wrapper');
            
            // Add title if available
            if (data.title) {
                contentWrapper.append('<h3 id="modal-title" class="door-title">' + this.escapeHtml(data.title) + '</h3>');
            }
            
            // Add image if available
            if (data.image_url) {
                contentWrapper.append(
                    '<div class="door-image">' +
                    '<img src="' + this.escapeHtml(data.image_url) + '" alt="' + (data.title || 'Door image') + '">' +
                    '</div>'
                );
            }
            
            // Add content
            if (data.content) {
                contentWrapper.append('<div class="door-content-text">' + data.content + '</div>');
            }
            
            // Add link if available
            if (data.link_url) {
                contentWrapper.append(
                    '<div class="door-actions">' +
                    '<a href="' + this.escapeHtml(data.link_url) + '" class="btn btn-primary" target="_blank" rel="noopener">Przejdź do strony</a>' +
                    '</div>'
                );
            }
            
            $('body').append(modal).addClass('modal-open');
            
            // Focus trap for accessibility
            modal.focus();
            
            // Close on background click
            modal.on('click', (e) => {
                if (e.target === modal[0]) {
                    this.closeModal(e);
                }
            });
        },

        openExternalLink: function(url) {
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        },

        closeModal: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if ($(e.target).hasClass('advent-modal-close') || $(e.target).hasClass('advent-modal')) {
                $('.advent-modal').remove();
                $('body').removeClass('modal-open');
            }
        },

        handleKeydown: function(e) {
            // Close modal on Escape key
            if (e.key === 'Escape' && $('.advent-modal').length) {
                this.closeModal(e);
            }
        },

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
                default:
                    console.log('Unknown effect:', effect);
            }
        },
        
        createConfetti: function() {
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
            const container = $('<div class="confetti-container" aria-hidden="true"></div>');
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
            const container = $('<div class="snow-container" aria-hidden="true"></div>');
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
                const sparkle = $('<div class="sparkle" aria-hidden="true"></div>');
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
            // Remove existing errors
            $('.advent-calendar-error').remove();
            
            const errorDiv = $(
                '<div class="advent-calendar-error" role="alert" aria-live="polite">' + 
                this.escapeHtml(message) + 
                '</div>'
            );
            
            $('body').append(errorDiv);
            
            setTimeout(() => {
                errorDiv.fadeOut(() => errorDiv.remove());
            }, 5000);
        },

        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Initialize when DOM is ready
    AdventCalendar.init();
});
