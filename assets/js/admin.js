jQuery(document).ready(function($) {
    'use strict';
    
    const AdventCalendarAdmin = {
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initTabs();
            console.log('Advent Calendar Admin initialized');
        },
        
        bindEvents: function() {
            // Zapisywanie kalendarza
            $('#save-calendar').on('click', this.saveCalendar.bind(this));
            
            // Usuwanie kalendarza
            $('.delete-calendar').on('click', this.deleteCalendar.bind(this));
            
            // Edycja drzwi
            $('.door-editor-item').on('click', this.editDoor.bind(this));
            
            // Zapisywanie drzwi
            $('#save-door').on('click', this.saveDoor.bind(this));
            
            // Upload obrazka
            $('#upload-door-image').on('click', this.uploadImage.bind(this));
            
            // Zmiana typu drzwi
            $('input[name="door_type"]').on('change', this.toggleDoorType.bind(this));
        },
        
        initColorPickers: function() {
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.color-picker').wpColorPicker();
            } else {
                console.warn('wpColorPicker not available');
            }
        },
        
        initTabs: function() {
            $('.advent-tab').on('click', function() {
                const tabId = $(this).data('tab');
                
                $('.advent-tab').removeClass('active');
                $('.tab-content').removeClass('active');
                
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });
        },
        
        saveCalendar: function(e) {
            e.preventDefault();
            console.log('Saving calendar...');
            
            const button = $('#save-calendar');
            const form = $('#calendar-form');
            
            button.prop('disabled', true).addClass('loading');
            button.html('<span class="spinner"></span> Zapisywanie...');
            
            // Pobierz dane z formularza
            const formData = {
                action: 'save_calendar',
                nonce: adventCalendarAdmin.nonce,
                title: $('#calendar-title').val(),
                columns: $('#calendar-columns').val(),
                rows: $('#calendar-rows').val(),
                start_date: $('#calendar-start-date').val(),
                end_date: $('#calendar-end-date').val(),
                theme: $('#calendar-theme').val(),
                default_animation: $('#calendar-default-animation').val(),
                snow_effect: $('#calendar-snow-effect').is(':checked') ? 1 : 0,
                confetti_effect: $('#calendar-confetti-effect').is(':checked') ? 1 : 0,
                enable_stats: $('#calendar-enable-stats').is(':checked') ? 1 : 0
            };
            
            // Jeśli edytujemy istniejący kalendarz
            const calendarId = $('#calendar-id').val();
            if (calendarId) {
                formData.id = parseInt(calendarId);
            }
            
            console.log('Sending data:', formData);
            
            $.ajax({
                url: adventCalendarAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    console.log('Response:', response);
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        
                        // Jeśli to nowy kalendarz, przekieruj do edycji
                        if (!calendarId && response.data.id) {
                            setTimeout(() => {
                                window.location.href = 'admin.php?page=advent-calendar-new&calendar_id=' + response.data.id;
                            }, 1000);
                        }
                    } else {
                        this.showMessage(response.data || 'Wystąpił błąd podczas zapisywania', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error);
                    this.showMessage('Błąd połączenia. Spróbuj ponownie.', 'error');
                },
                complete: () => {
                    button.prop('disabled', false).removeClass('loading');
                    button.html('Zapisz Kalendarz');
                }
            });
        },
        
        deleteCalendar: function(e) {
            e.preventDefault();
            
            if (!confirm('Czy na pewno chcesz usunąć ten kalendarz? Ta operacja jest nieodwracalna.')) {
                return;
            }
            
            const button = $(e.target);
            const calendarId = button.data('calendar-id');
            
            $.ajax({
                url: adventCalendarAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_calendar',
                    nonce: adventCalendarAdmin.nonce,
                    calendar_id: calendarId
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        button.closest('.calendar-card').fadeOut();
                    } else {
                        this.showMessage(response.data, 'error');
                    }
                },
                error: () => {
                    this.showMessage('Błąd podczas usuwania kalendarza.', 'error');
                }
            });
        },
        
        editDoor: function(e) {
            const doorItem = $(e.currentTarget);
            const doorNumber = doorItem.data('door-number');
            const calendarId = $('#calendar-id').val();
            
            $('.door-editor-item').removeClass('active');
            doorItem.addClass('active');
            
            $('#door-form').show();
            $('#door-number-display').text(doorNumber);
            
            // Sprawdź czy drzwi już istnieją
            const doorId = doorItem.data('door-id');
            if (doorId) {
                // Ładuj istniejące drzwi
                $.ajax({
                    url: adventCalendarAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_door',
                        nonce: adventCalendarAdmin.nonce,
                        door_id: doorId
                    },
                    success: (response) => {
                        if (response.success) {
                            this.populateDoorForm(response.data);
                        } else {
                            this.resetDoorForm(doorNumber);
                        }
                    },
                    error: () => {
                        this.resetDoorForm(doorNumber);
                    }
                });
            } else {
                // Nowe drzwi
                this.resetDoorForm(doorNumber);
            }
        },
        
        populateDoorForm: function(doorData) {
            console.log('Populating door form:', doorData);
            $('#door-id').val(doorData.id);
            $('#door-number').val(doorData.door_number);
            $('#door-title').val(doorData.title || '');
            $('#door-content').val(doorData.content || '');
            $('#door-image').val(doorData.image_url || '');
            $('#door-link').val(doorData.link_url || '');
            $('input[name="door_type"][value="' + (doorData.door_type || 'modal') + '"]').prop('checked', true);
            $('#door-animation').val(doorData.animation || 'fade');
            $('#door-custom-css').val(doorData.custom_css || '');
            $('#door-unlock-date').val(doorData.unlock_date || '');
            
            this.toggleDoorType();
            this.updateImagePreview(doorData.image_url || '');
        },
        
        resetDoorForm: function(doorNumber) {
            console.log('Resetting door form for number:', doorNumber);
            $('#door-id').val('');
            $('#door-number').val(doorNumber);
            $('#door-title').val('');
            $('#door-content').val('');
            $('#door-image').val('');
            $('#door-link').val('');
            $('input[name="door_type"][value="modal"]').prop('checked', true);
            $('#door-animation').val($('#calendar-default-animation').val() || 'fade');
            $('#door-custom-css').val('');
            
            // Ustaw datę odblokowania na podstawie daty startowej
            const startDate = $('#calendar-start-date').val();
            if (startDate) {
                const unlockDate = new Date(startDate);
                unlockDate.setDate(unlockDate.getDate() + (doorNumber - 1));
                $('#door-unlock-date').val(unlockDate.toISOString().split('T')[0]);
            } else {
                const defaultDate = new Date();
                defaultDate.setMonth(11); // Grudzień
                defaultDate.setDate(doorNumber);
                $('#door-unlock-date').val(defaultDate.toISOString().split('T')[0]);
            }
            
            this.toggleDoorType();
            this.updateImagePreview('');
        },
        
        saveDoor: function(e) {
            e.preventDefault();
            console.log('Saving door...');
            
            const button = $('#save-door');
            const formData = {
                action: 'save_door',
                nonce: adventCalendarAdmin.nonce,
                door_id: $('#door-id').val(),
                calendar_id: $('#calendar-id').val(),
                door_number: $('#door-number').val(),
                title: $('#door-title').val(),
                content: $('#door-content').val(),
                image_url: $('#door-image').val(),
                link_url: $('#door-link').val(),
                door_type: $('input[name="door_type"]:checked').val(),
                animation: $('#door-animation').val(),
                custom_css: $('#door-custom-css').val(),
                unlock_date: $('#door-unlock-date').val()
            };
            
            console.log('Door data:', formData);
            
            button.prop('disabled', true).addClass('loading');
            button.html('<span class="spinner"></span> Zapisywanie...');
            
            $.ajax({
                url: adventCalendarAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    console.log('Door save response:', response);
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        
                        // Zaktualizuj ID drzwi w edytorze
                        if (response.data.id) {
                            $('.door-editor-item.active').data('door-id', response.data.id);
                            $('.door-editor-item.active').addClass('has-content');
                        }
                    } else {
                        this.showMessage(response.data || 'Wystąpił błąd podczas zapisywania drzwi', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', error);
                    this.showMessage('Błąd połączenia. Spróbuj ponownie.', 'error');
                },
                complete: () => {
                    button.prop('disabled', false).removeClass('loading');
                    button.html('Zapisz Drzwi');
                }
            });
        },
        
        uploadImage: function(e) {
            e.preventDefault();
            
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('Funkcja uploadu obrazków nie jest dostępna. Upewnij się, że WordPress media jest załadowany.');
                return;
            }
            
            const frame = wp.media({
                title: 'Wybierz obrazek drzwi',
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            frame.on('select', () => {
                const attachment = frame.state().get('selection').first().toJSON();
                $('#door-image').val(attachment.url);
                this.updateImagePreview(attachment.url);
            });
            
            frame.open();
        },
        
        updateImagePreview: function(imageUrl) {
            const preview = $('#door-image-preview');
            
            if (imageUrl) {
                preview.html('<img src="' + imageUrl + '" style="max-width: 200px; height: auto; border-radius: 5px;">');
            } else {
                preview.html('<p style="color: #666; font-style: italic;">Brak obrazka</p>');
            }
        },
        
        toggleDoorType: function() {
            const doorType = $('input[name="door_type"]:checked').val();
            
            if (doorType === 'link') {
                $('#door-link-field').show();
                $('#door-content-field').hide();
            } else {
                $('#door-link-field').hide();
                $('#door-content-field').show();
            }
        },
        
        showMessage: function(message, type) {
            // Usuń istniejące komunikaty
            $('.advent-calendar-notice').remove();
            
            const messageDiv = $('<div class="advent-calendar-notice notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.advent-calendar-admin').prepend(messageDiv);
            
            // Auto-ukrywanie po 5 sekundach
            setTimeout(() => {
                messageDiv.fadeOut(() => messageDiv.remove());
            }, 5000);
            
            // Dismiss button
            messageDiv.on('click', '.notice-dismiss', function() {
                messageDiv.remove();
            });
            
            // Ręczne ukrywanie po kliknięciu
            messageDiv.on('click', function() {
                $(this).remove();
            });
        }
    };
    
    // Sprawdź czy zmienna adventCalendarAdmin jest dostępna
    if (typeof adventCalendarAdmin === 'undefined') {
        console.error('adventCalendarAdmin variable is not defined');
        // Utwórz fallback
        window.adventCalendarAdmin = {
            ajaxurl: '/wp-admin/admin-ajax.php',
            nonce: 'fallback_nonce'
        };
    }
    
    AdventCalendarAdmin.init();
});
