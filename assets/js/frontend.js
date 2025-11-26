jQuery(document).ready(function($) {
    'use strict';
    
    const AdventCalendar = {
        init: function() {
            this.bindEvents();
            this.checkOpenedDoors();
        },
        
        bindEvents: function() {
            $('.advent-calendar.door:not(.locked)').on('click', this.openDoor.bind(this));
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
            
            $.ajax({
                url: adventCalendar.ajaxurl,
                type: 'POST',
                data: {
                    action: 'open_door',
                    door_id: doorId,
                    calendar_id: calendarId,
                    nonce: adventCalendar.nonce
                },
                success: (response) => {
                    door.removeClass('loading');
                    
                    if (response.success) {
                        this.handleDoorOpenSuccess(door, response.data);
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
        
        handleDoorOpenSuccess: function(door, data) {
            // Dodaj animację otwarcia
            door.addClass('open door-animation-' + data.animation);
            
            // Odtwórz efekty specjalne
            if (data.effects && data.effects.length) {
                data.effects.forEach(effect => {
                    this.playEffect(effect);
                });
            }
            
            // Pokaż zawartość
            setTimeout(() => {
                if (data.door_type === 'modal') {
                    this.showModal(data.content);
                } else if (data.door_type === 'link' && data.link_url) {
                    window.open(data.link_url, '_blank');
                } else {
                    this.showInlineContent(door, data.content);
                }
                
                // Oznacz jako sukces
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
            // ... istniejący kod confetti ...
        },
        
        createSnow: function() {
            // ... istniejący kod snow ...
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
        
        checkOpenedDoors: function() {
            $('.advent-calendar.door.open').each(function() {
                const door = $(this);
                const doorId = door.data('door-id');
                
                // Załaduj zawartość dla już otwartych drzwi
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
