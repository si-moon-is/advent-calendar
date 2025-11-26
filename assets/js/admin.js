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
            $('#save-calendar').on('click', this.saveCalendar.bind(this));
            $(document).on('click', '.delete-calendar', this.deleteCalendar.bind(this));
            $(document).on('click', '.door-editor-item', this.editDoor.bind(this));
            $('#save-door').on('click', this.saveDoor.bind(this));
            $('#cancel-door').on('click', this.cancelDoorEdit.bind(this));
            $('#upload-door-image').on('click', this.uploadImage.bind(this));
            $('input[name="door_type"]').on('change', this.toggleDoorType.bind(this));
        },

        initColorPickers: function() {
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.color-picker').wpColorPicker();
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
            console.log('Advent Calendar: Save button clicked');

            const button = $('#save-calendar');
            const title = $('#calendar-title').val().trim();

            if (!title) {
                console.log('Advent Calendar: Validation failed - empty title');
                this.showMessage('Nazwa kalendarza jest wymagana!', 'error');
                $('#calendar-title').focus();
                return;
            }

            const formData = {
                action: 'advent_calendar_save',
                nonce: adventCalendar.nonce,
                title: title,
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

            const calendarId = $('#calendar-id').val();
            if (calendarId) {
                formData.id = parseInt(calendarId);
            }

            console.log('Advent Calendar: Sending AJAX data:', formData);

            button.prop('disabled', true).addClass('loading');
            button.html('<span class="spinner"></span> Zapisywanie...');

            $.ajax({
                url: adventCalendar.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    console.log('Advent Calendar: AJAX response received:', response);
                    
                    if (response.success) {
                        console.log('Advent Calendar: Save successful');
                        this.showMessage(response.data.message, 'success');
                        
                        if (response.data.redirect) {
                            setTimeout(() => {
                                window.location.href = response.data.redirect;
                            }, 1000);
                        } else {
                            $('#calendar-id').val(response.data.id);
                        }
                    } else {
                        console.log('Advent Calendar: Save failed with error:', response.data);
                        this.showMessage(response.data || 'Wystąpił nieznany błąd', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Advent Calendar: AJAX Error:', error);
                    console.error('Advent Calendar: Status:', status);
                    console.error('Advent Calendar: XHR response:', xhr.responseText);
                    this.showMessage('Błąd połączenia z serwerem. Spróbuj ponownie.', 'error');
                },
                complete: () => {
                    button.prop('disabled', false).removeClass('loading');
                    button.html('Zapisz Kalendarz');
                }
            });
        },

        deleteCalendar: function(e) {
            e.preventDefault();
            
            if (!confirm('Czy na pewno chcesz usunąć ten kalendarz?')) {
                return;
            }

            const button = $(e.target);
            const calendarId = button.data('calendar-id');

            $.ajax({
                url: adventCalendar.ajaxurl,
                type: 'POST',
                data: {
                    action: 'advent_calendar_delete',
                    nonce: adventCalendar.nonce,
                    calendar_id: calendarId
                },
                success: (response) => {
                    if (response.success) {
                        button.closest('.calendar-card').fadeOut();
                    } else {
                        this.showMessage(response.data, 'error');
                    }
                },
                error: () => {
                    this.showMessage('Błąd podczas usuwania', 'error');
                }
            });
        },

        editDoor: function(e) {
            const doorItem = $(e.currentTarget);
            const doorNumber = doorItem.data('door-number');
            const calendarId = $('#calendar-id').val();

            if (!calendarId) {
                this.showMessage('Najpierw zapisz kalendarz!', 'error');
                return;
            }

            $('.door-editor-item').removeClass('active');
            doorItem.addClass('active');

            $('#door-form').show();
            $('#door-number-display').text(doorNumber);

            const doorId = doorItem.data('door-id');
            if (doorId) {
                $.ajax({
                    url: adventCalendar.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'advent_calendar_get_door',
                        nonce: adventCalendar.nonce,
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
                this.resetDoorForm(doorNumber);
            }
        },

        populateDoorForm: function(doorData) {
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
            $('#door-id').val('');
            $('#door-number').val(doorNumber);
            $('#door-title').val('');
            $('#door-content').val('');
            $('#door-image').val('');
            $('#door-link').val('');
            $('input[name="door_type"][value="modal"]').prop('checked', true);
            $('#door-animation').val($('#calendar-default-animation').val() || 'fade');
            $('#door-custom-css').val('');

            const startDate = $('#calendar-start-date').val();
            if (startDate) {
                const unlockDate = new Date(startDate);
                unlockDate.setDate(unlockDate.getDate() + (doorNumber - 1));
                $('#door-unlock-date').val(unlockDate.toISOString().split('T')[0]);
            } else {
                const defaultDate = new Date();
                defaultDate.setMonth(11);
                defaultDate.setDate(doorNumber);
                $('#door-unlock-date').val(defaultDate.toISOString().split('T')[0]);
            }

            this.toggleDoorType();
            this.updateImagePreview('');
        },

        saveDoor: function(e) {
            e.preventDefault();

            const button = $('#save-door');
            const formData = {
                action: 'advent_calendar_save_door',
                nonce: adventCalendar.nonce,
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

            button.prop('disabled', true).addClass('loading');
            button.html('<span class="spinner"></span> Zapisywanie...');

            $.ajax({
                url: adventCalendar.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        if (response.data.id) {
                            $('.door-editor-item.active').data('door-id', response.data.id);
                            $('.door-editor-item.active').addClass('has-content');
                        }
                    } else {
                        this.showMessage(response.data, 'error');
                    }
                },
                error: () => {
                    this.showMessage('Błąd połączenia', 'error');
                },
                complete: () => {
                    button.prop('disabled', false).removeClass('loading');
                    button.html('Zapisz Drzwi');
                }
            });
        },

        cancelDoor
