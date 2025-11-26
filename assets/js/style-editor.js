jQuery(document).ready(function($) {
    'use strict';
    
    const StyleEditor = {
        init: function() {
            this.bindEvents();
            this.loadStylePresets();
        },
        
        bindEvents: function() {
            $('#save-styles').on('click', this.saveStyles.bind(this));
            $('#apply-preset').on('click', this.applyPreset.bind(this));
            $('.preset-item').on('click', this.selectPreset.bind(this));
            $('#reset-styles').on('click', this.resetStyles.bind(this));
        },
        
        loadStylePresets: function() {
            const presets = {
                'christmas': {
                    name: 'Świąteczny',
                    colors: {
                        primary: '#c41e3a',
                        secondary: '#165b33',
                        accent: '#ffd700'
                    },
                    css: '.advent-calendar.theme-christmas { border: 3px solid #ffd700; }'
                },
                'winter': {
                    name: 'Zimowy',
                    colors: {
                        primary: '#74b9ff',
                        secondary: '#0984e3',
                        accent: '#dfe6e9'
                    },
                    css: '.advent-calendar.theme-winter { border: 3px solid #dfe6e9; }'
                },
                'elegant': {
                    name: 'Elegancki',
                    colors: {
                        primary: '#2d3436',
                        secondary: '#636e72',
                        accent: '#fd79a8'
                    },
                    css: '.advent-calendar.theme-elegant { border: 3px solid #fd79a8; }'
                }
            };
            
            this.presets = presets;
            this.renderPresets(presets);
        },
        
        renderPresets: function(presets) {
            const container = $('#style-presets');
            container.empty();
            
            Object.keys(presets).forEach(key => {
                const preset = presets[key];
                const presetItem = $(
                    '<div class="preset-item" data-preset="' + key + '">' +
                    '<div class="preset-preview" style="background: ' + preset.colors.primary + '"></div>' +
                    '<span class="preset-name">' + preset.name + '</span>' +
                    '</div>'
                );
                
                container.append(presetItem);
            });
        },
        
        selectPreset: function(e) {
            const presetKey = $(e.currentTarget).data('preset');
            const preset = this.presets[presetKey];
            
            if (preset) {
                $('.preset-item').removeClass('active');
                $(e.currentTarget).addClass('active');
                
                $('#primary-color').val(preset.colors.primary).trigger('change');
                $('#secondary-color').val(preset.colors.secondary).trigger('change');
                $('#accent-color').val(preset.colors.accent).trigger('change');
                $('#custom-css').val(preset.css);
                
                this.updatePreview();
            }
        },
        
        applyPreset: function() {
            const selectedPreset = $('.preset-item.active').data('preset');
            if (selectedPreset) {
                this.saveStyles();
            } else {
                alert('Wybierz preset stylu!');
            }
        },
        
        saveStyles: function() {
            const button = $('#save-styles');
            const stylesData = {
                primary: $('#primary-color').val(),
                secondary: $('#secondary-color').val(),
                accent: $('#accent-color').val(),
                custom_css: $('#custom-css').val()
            };
            
            button.prop('disabled', true).text('Zapisywanie...');
            
            $.ajax({
                url: adventCalendar.ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_calendar_styles',
                    nonce: adventCalendar.nonce,
                    calendar_id: $('#calendar-id').val(),
                    styles: stylesData
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage('Style zapisane pomyślnie!', 'success');
                    } else {
                        this.showMessage('Błąd podczas zapisywania stylów', 'error');
                    }
                },
                error: () => {
                    this.showMessage('Błąd połączenia', 'error');
                },
                complete: () => {
                    button.prop('disabled', false).text('Zapisz Style');
                }
            });
        },
        
        resetStyles: function() {
            if (confirm('Czy na pewno chcesz zresetować style do domyślnych?')) {
                $('#primary-color').val('#c41e3a').trigger('change');
                $('#secondary-color').val('#165b33').trigger('change');
                $('#accent-color').val('#ffd700').trigger('change');
                $('#custom-css').val('');
                
                $('.preset-item').removeClass('active');
                this.updatePreview();
            }
        },
        
        updatePreview: function() {
            const primary = $('#primary-color').val();
            const secondary = $('#secondary-color').val();
            const accent = $('#accent-color').val();
            
            $('.style-preview-door').css({
                'background': primary,
                'border-color': accent
            });
            
            $('.style-preview-calendar').css({
                'background': `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`,
                'border-color': accent
            });
        },
        
        showMessage: function(message, type) {
            const messageDiv = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('#style-editor-messages').html(messageDiv);
            
            setTimeout(() => {
                messageDiv.fadeOut();
            }, 5000);
            
            messageDiv.on('click', '.notice-dismiss', function() {
                messageDiv.remove();
            });
        }
    };
    
    StyleEditor.init();
    
    $('.color-picker').on('change', function() {
        StyleEditor.updatePreview();
    });
});
