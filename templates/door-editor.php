<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendar_id = isset($_GET['calendar_id']) ? intval($_GET['calendar_id']) : 0;
$calendar = $calendar_id ? Advent_Calendar::get_calendar($calendar_id) : null;
$settings = $calendar ? json_decode($calendar->settings, true) : array();
$doors = $calendar ? Advent_Calendar::get_calendar_doors($calendar_id) : array();

$default_settings = array(
    'columns' => 6,
    'rows' => 4,
    'start_date' => date('Y-12-01'),
    'end_date' => date('Y-12-24'),
    'theme' => 'christmas',
    'default_animation' => 'fade',
    'snow_effect' => true,
    'confetti_effect' => true,
    'enable_stats' => true
);

$settings = wp_parse_args($settings, $default_settings);
$total_doors = $settings['columns'] * $settings['rows'];
?>

<div class="wrap advent-calendar-editor">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-edit"></span>
        <?php echo $calendar ? 'Edytuj: ' . esc_html($calendar->title) : 'Nowy Kalendarz Adwentowy'; ?>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=advent-calendar'); ?>" class="page-title-action">
        ← Wróć do listy
    </a>
    
    <hr class="wp-header-end">

    <div class="editor-container">
        <div class="editor-sidebar">
            <div class="sidebar-section">
                <h3>Ustawienia Kalendarza</h3>
                
                <input type="hidden" id="calendar-id" value="<?php echo $calendar ? $calendar->id : ''; ?>">
                
                <div class="form-field">
                    <label for="calendar-title">Nazwa kalendarza *</label>
                    <input type="text" id="calendar-title" 
                           value="<?php echo $calendar ? esc_attr($calendar->title) : ''; ?>" 
                           placeholder="Nazwa Twojego kalendarza" required>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="calendar-columns">Kolumny</label>
                        <select id="calendar-columns">
                            <?php for ($i = 3; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" 
                                    <?php selected($i, $settings['columns']); ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="calendar-rows">Wiersze</label>
                        <select id="calendar-rows">
                            <?php for ($i = 2; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" 
                                    <?php selected($i, $settings['rows']); ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="calendar-start-date">Data rozpoczęcia</label>
                        <input type="date" id="calendar-start-date" 
                               value="<?php echo esc_attr($settings['start_date']); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="calendar-end-date">Data zakończenia</label>
                        <input type="date" id="calendar-end-date" 
                               value="<?php echo esc_attr($settings['end_date']); ?>">
                    </div>
                </div>
                
                <div class="form-field">
    <label for="calendar-theme">Motyw</label>
    <div class="theme-preview-container">
        <select id="calendar-theme" name="theme">
            <option value="christmas" <?php selected('christmas', $settings['theme']); ?>>Świąteczny</option>
            <option value="winter" <?php selected('winter', $settings['theme']); ?>>Zimowy</option>
            <option value="modern" <?php selected('modern', $settings['theme']); ?>>Nowoczesny</option>
            <option value="classic" <?php selected('classic', $settings['theme']); ?>>Klasyczny</option>
        </select>
        
        <div class="theme-previews">
            <div class="theme-preview <?php echo $settings['theme'] === 'christmas' ? 'active' : ''; ?>" data-theme="christmas">
                <div class="preview-image christmas-preview"></div>
                <span class="preview-label">Świąteczny</span>
            </div>
            <div class="theme-preview <?php echo $settings['theme'] === 'winter' ? 'active' : ''; ?>" data-theme="winter">
                <div class="preview-image winter-preview"></div>
                <span class="preview-label">Zimowy</span>
            </div>
            <div class="theme-preview <?php echo $settings['theme'] === 'modern' ? 'active' : ''; ?>" data-theme="modern">
                <div class="preview-image modern-preview"></div>
                <span class="preview-label">Nowoczesny</span>
            </div>
            <div class="theme-preview <?php echo $settings['theme'] === 'classic' ? 'active' : ''; ?>" data-theme="classic">
                <div class="preview-image classic-preview"></div>
                <span class="preview-label">Klasyczny</span>
            </div>
        </div>
    </div>
</div>
                
                <div class="form-field">
                    <label for="calendar-default-animation">Domyślna animacja</label>
                    <select id="calendar-default-animation">
                        <option value="fade" <?php selected('fade', $settings['default_animation']); ?>>Fade</option>
                        <option value="slide" <?php selected('slide', $settings['default_animation']); ?>>Slide</option>
                        <option value="zoom" <?php selected('zoom', $settings['default_animation']); ?>>Zoom</option>
                        <option value="flip" <?php selected('flip', $settings['default_animation']); ?>>Flip</option>
                    </select>
                </div>
                
                <div class="form-field checkbox-field">
                    <label>
                        <input type="checkbox" id="calendar-snow-effect" 
                               <?php checked($settings['snow_effect']); ?>>
                        Efekt śniegu
                    </label>
                </div>
                
                <div class="form-field checkbox-field">
                    <label>
                        <input type="checkbox" id="calendar-confetti-effect" 
                               <?php checked($settings['confetti_effect']); ?>>
                        Efekt konfetti przy otwieraniu
                    </label>
                </div>
                
                <div class="form-field checkbox-field">
                    <label>
                        <input type="checkbox" id="calendar-enable-stats" 
                               <?php checked($settings['enable_stats']); ?>>
                        Śledź statystyki otwierania
                    </label>
                </div>
                
                <button type="button" id="save-calendar" class="button button-primary button-large">
                    <?php echo $calendar ? 'Zaktualizuj Kalendarz' : 'Utwórz Kalendarz'; ?>
                </button>
            </div>
            
            <?php if ($calendar): ?>
            <div class="sidebar-section">
                <h3>Shortcode</h3>
                <div class="shortcode-container">
                    <code>[advent_calendar id="<?php echo $calendar->id; ?>"]</code>
                    <button type="button" class="button button-small copy-shortcode" 
                            data-shortcode='[advent_calendar id="<?php echo $calendar->id; ?>"]'>
                        Kopiuj
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="editor-main">
            <?php if ($calendar): ?>
                <div class="doors-grid-container">
                    <h3>Drzwi Kalendarza (<?php echo $total_doors; ?> drzwi)</h3>
                    <p>Kliknij na numer drzwi aby edytować jego zawartość</p>
                    
                    <div class="doors-grid" style="grid-template-columns: repeat(<?php echo $settings['columns']; ?>, 1fr);">
                        <?php for ($i = 1; $i <= $total_doors; $i++): 
                            $door = array_filter($doors, function($d) use ($i) { 
                                return $d->door_number == $i; 
                            });
                            $door = !empty($door) ? reset($door) : null;
                            $has_content = $door && (!empty($door->title) || !empty($door->content) || !empty($door->image_url));
                        ?>
                            <div class="door-editor-item <?php echo $has_content ? 'has-content' : ''; ?> <?php echo $door ? 'saved' : ''; ?>" 
                                 data-door-number="<?php echo $i; ?>" 
                                 data-door-id="<?php echo $door ? $door->id : ''; ?>">
                                <div class="door-number"><?php echo $i; ?></div>
                                <?php if ($has_content): ?>
                                    <div class="door-status configured">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                    </div>
                                <?php else: ?>
                                    <div class="door-status empty">
                                        <span class="dashicons dashicons-plus"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div id="door-form" style="display: none;">
                    <div class="door-form-header">
                        <h3>Edytuj drzwi <span id="door-number-display"></span></h3>
                    </div>
                    
                    <input type="hidden" id="door-id">
                    <input type="hidden" id="door-number">
                    
                    <div class="form-field">
                        <label for="door-title">Tytuł drzwi</label>
                        <input type="text" id="door-title" placeholder="Tytuł nagłówka (opcjonalnie)">
                    </div>
                    
                    <div class="form-field">
                        <label for="door-unlock-date">Data odblokowania</label>
                        <input type="date" id="door-unlock-date">
                    </div>
                    
                    <div class="form-field">
                        <label>Typ drzwi</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="door_type" value="modal" checked>
                                Modal z treścią
                            </label>
                            <label>
                                <input type="radio" name="door_type" value="link">
                                Link zewnętrzny
                            </label>
                        </div>
                    </div>
                    
                    <div class="door-content-field">
                        <div class="form-field">
                            <label for="door-content">Treść</label>
                            <textarea id="door-content" rows="6" placeholder="Treść do wyświetlenia w modal..."></textarea>
                        </div>
                    </div>
                    
                    <div class="door-link-field" style="display: none;">
                        <div class="form-field">
                            <label for="door-link">Link URL</label>
                            <input type="url" id="door-link" placeholder="https://...">
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="door-image">Obraz</label>
                        <div class="image-upload-container">
                            <input type="text" id="door-image" placeholder="URL obrazka lub...">
                            <button type="button" id="upload-door-image" class="button">Wybierz obraz</button>
                        </div>
                        <div id="door-image-preview" class="image-preview" style="display: none;"></div>
                    </div>
                    
                    <div class="form-field">
                        <label for="door-animation">Animacja otwarcia</label>
                        <select id="door-animation">
                            <option value="fade">Fade</option>
                            <option value="slide">Slide</option>
                            <option value="zoom">Zoom</option>
                            <option value="flip">Flip</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="door-custom-css">Niestandardowy CSS</label>
                        <textarea id="door-custom-css" rows="4" placeholder="Dodatkowe style CSS dla tych drzwi..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="save-door" class="button button-primary">Zapisz Drzwi</button>
                        <button type="button" id="cancel-door" class="button">Anuluj</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-calendar-message">
                    <div class="message-content">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <h3>Najpierw utwórz kalendarz</h3>
                        <p>Wypełnij formularz po lewej stronie i kliknij "Utwórz Kalendarz", aby rozpocząć konfigurację drzwi.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.editor-container {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
    margin-top: 20px;
}

.editor-sidebar {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.editor-main {
    min-height: 600px;
}

.sidebar-section {
    margin-bottom: 30px;
}

.sidebar-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f1;
    color: #23282d;
}

.form-field {
    margin-bottom: 15px;
}

.form-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #23282d;
}

.form-field input[type="text"],
.form-field input[type="date"],
.form-field input[type="url"],
.form-field select,
.form-field textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.checkbox-field label,
.radio-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
    margin-bottom: 8px;
}

.radio-group label:last-child {
    margin-bottom: 0;
}

.image-upload-container {
    display: flex;
    gap: 10px;
}

.image-upload-container input {
    flex: 1;
}

.image-preview {
    margin-top: 10px;
    padding: 10px;
    background: #f6f7f7;
    border-radius: 4px;
    text-align: center;
}

.image-preview img {
    max-width: 200px;
    max-height: 150px;
    border-radius: 4px;
}

.doors-grid-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.doors-grid {
    display: grid;
    gap: 10px;
    margin-top: 15px;
}

.door-editor-item {
    position: relative;
    aspect-ratio: 1;
    background: #f6f7f7;
    border: 2px dashed #ccd0d4;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.door-editor-item:hover {
    border-color: #2271b1;
    background: #f0f6fc;
}

.door-editor-item.active {
    border-color: #2271b1;
    background: #dbeafe;
    border-style: solid;
}

.door-editor-item.has-content {
    border-color: #68de7c;
    border-style: solid;
    background: #f0f9f0;
}

.door-number {
    font-size: 18px;
    font-weight: bold;
    color: #646970;
}

.door-editor-item.has-content .door-number {
    color: #2271b1;
}

.door-status {
    position: absolute;
    top: 5px;
    right: 5px;
}

.door-status.configured {
    color: #68de7c;
}

.door-status.empty {
    color: #ccd0d4;
}

#door-form {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.door-form-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f1;
}

.door-form-header h3 {
    margin: 0;
    color: #23282d;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f1;
}

.no-calendar-message {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 60px 40px;
    text-align: center;
}

.message-content .dashicons {
    font-size: 60px;
    width: 60px;
    height: 60px;
    color: #ccd0d4;
    margin-bottom: 20px;
}

.message-content h3 {
    color: #646970;
    margin-bottom: 10px;
}

.message-content p {
    color: #8c8f94;
    margin: 0;
}

.button.loading {
    position: relative;
    color: transparent;
}

.button.loading .spinner {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    margin: 0;
}

.shortcode-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.shortcode-container code {
    flex: 1;
    background: #f6f7f7;
    padding: 8px 12px;
    border-radius: 3px;
    font-size: 13px;
    word-break: break-all;
}
</style>
<script>
jQuery(document).ready(function($) {
    // Inicjalizacja podglądu motywów
    function initThemePreviews() {
        $('.theme-preview').on('click', function() {
            const theme = $(this).data('theme');
            
            // Update select value
            $('#calendar-theme').val(theme);
            
            // Update active class
            $('.theme-preview').removeClass('active');
            $(this).addClass('active');
            
            console.log('Theme selected:', theme);
        });
        
        // Handle select change
        $('#calendar-theme').on('change', function() {
            const theme = $(this).val();
            $('.theme-preview').removeClass('active');
            $(`.theme-preview[data-theme="${theme}"]`).addClass('active');
        });
        
        // Initialize active state
        const currentTheme = $('#calendar-theme').val();
        $(`.theme-preview[data-theme="${currentTheme}"]`).addClass('active');
    }
    
    // Initialize when document is ready
    initThemePreviews();
});
</script>
