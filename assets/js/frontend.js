jQuery(document).ready(function($) {
    $('.advent-calendar.door:not(.locked)').on('click', function() {
        const door = $(this);
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
            success: function(response) {
                if (response.success) {
                    door.addClass('open');
                    
                    // Odtwórz animację
                    if (response.data.animation) {
                        playAnimation(response.data.animation);
                    }
                    
                    // Pokaż zawartość
                    showDoorContent(response.data.content, doorId);
                }
            }
        });
    });
    
    function showDoorContent(content, doorId) {
        const modal = $('<div class="advent-modal active">' +
            '<div class="advent-modal-content">' +
            '<span class="advent-modal-close">&times;</span>' +
            '<div class="door-content">' + content + '</div>' +
            '</div>' +
            '</div>');
        
        $('body').append(modal);
        
        modal.find('.advent-modal-close').on('click', function() {
            modal.remove();
        });
        
        modal.on('click', function(e) {
            if (e.target === modal[0]) {
                modal.remove();
            }
        });
    }
    
    function playAnimation(type) {
        switch(type) {
            case 'confetti':
                createConfetti();
                break;
            case 'snow':
                createSnow();
                break;
        }
    }
});
