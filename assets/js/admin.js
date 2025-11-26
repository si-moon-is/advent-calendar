jQuery(document).ready(function($) {
    'use strict';

    // Główny obiekt zarządzający
    const AdventCalendarAdmin = {
        init: function() {
            this.bindEvents();
            console.log('Advent Calendar Admin initialized');
        },

        bindEvents: function() {
            // Zapisywanie kalendarza
            $('#save-calendar').on('click', this.saveCalendar.bind(this));
            
            // Usuwanie kalendarza
            $(document).on('click', '.delete-calendar', this.deleteCalendar.bind(this));
            
            // Tabs
            $('.advent-tab').on('click', this.switchTab.bind(this));
            
            // Edycja drzwi
            $(document).on('click', '.door-editor-item', this.editDoor.bind(this));
            
            // Zapisywanie drzwi
            $('#save-door').on('click', this.saveDoor.bind(this));
            
            // Anulowanie edycji drzwi
            $('#cancel-door').on('click', this.cancelDoorEdit.bind(this));
            
            // Upload obrazka
            $('#upload-door-image').on('click', this.uploadImage.bind(this));
            
            // Zmiana typu drzwi
            $('input[name="door_type"]').on('change', this.toggleDoorType.bind(this));
        },

        saveCalendar: function(e) {
            e.preventDefault();
            console.log('Saving calendar...');

            const button = $('#save-calendar');
            const title = $('#calendar-title').val().trim();

            // Walidacja
            if (!title) {
                this.showMessage('Nazwa kalendarza jest wymagana!', 'error');
                $('#calendar-title').focus();
                return;
            }

            // Przygotuj dane
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

            // Dodaj ID jeśli edytujemy istniejący
            const calendarId = $('#calendar-id').val();
            if (calendarId) {
                formData.id = parseInt(calendarId);
            }

            console.log('Sending data:', formData);

            // Zmień stan przycisku
            button.prop('disabled', true).text('Zapisywanie...');

            // Wyślij żądanie AJAX
            $.ajax({
                url: adventCalendar.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    console.log('Response received:', response);
                    
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        
                        // Jeśli to nowy kalendarz, przekieruj
                       
