<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendar_id = isset($_GET['calendar_id']) ? intval($_GET['calendar_id']) : 0;
$calendar = $calendar_id ? Advent_Calendar::get_calendar($calendar_id) : null;

if (!$calendar && $calendar_id) {
    echo '<div class="error"><p>Kalendarz nie znaleziony.</p></div>';
    return;
}

$settings = $calendar ? json_decode($calendar->settings, true) : array(
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

$total_doors = ($settings['columns'] ?? 6) * ($settings['rows'] ?? 4);
$doors = $calendar ? Advent_Calendar::get_calendar_doors($calendar_id) : array();
?>

<div class="wrap advent-calendar-admin">
    <h1 class="wp-heading-inline">
        <?php echo $calendar ? 'Edytuj: ' . esc_html($calendar->title) : 'Nowy Kalendarz Adwentowy'; ?>
    </h1>
    
    <div class="advent-tabs">
        <button type="button" class="advent-tab active" data-tab="settings">Ustawienia</button>
        <?php if ($calendar): ?>
            <button type="button" class="advent-tab" data-tab="doors">Edytor Drzwi</button>
            <button type="button" class="advent-tab" data-tab="styles">Style</button>
            <button type="button" class="advent-tab" data-tab="statistics">Statystyki</button>
        <?php endif; ?>
    </div>
    
    <div id="settings" class="tab-content active">
        <div class="form-container">
            <input type="hidden" id="calendar-id" value="<?php echo $calendar_id; ?>">
            
            <div class="form-group">
                <label for="calendar-title">Nazwa Kalendarza *</label>
                <input type="text" id="calendar-title" class="form-control" value="<?php echo $calendar ? esc_attr($calendar->title) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="calendar-columns">Liczba kolumn</label>
                <select id="calendar-columns" class="form-control">
                    <?php for ($i = 3; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php selected($settings['columns'] ?? 6, $i); ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="calendar-rows">Liczba wierszy</label>
                <select id="calendar-rows" class="form-control">
                    <?php for ($i = 2; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php selected($settings['rows'] ?? 4, $i); ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="calendar-start-date">Data rozpoczęcia</label>
                <input type="date" id="calendar-start-date" class="form-control" value="<?php echo esc_attr($settings['start_date'] ?? date('Y-12-01')); ?>">
            </div>
            
            <div class="form-group">
                <label for="calendar-end-date">Data zakończenia</label>
                <input type="date" id="calendar-end-date" class="form-control" value="<?php echo esc_attr($settings['end_date'] ?? date('Y-12-24')); ?>">
            </div>
            
            <div class="form-group">
                <label for="calendar-theme">Motyw</label>
                <select id="calendar-theme" class="form-control">
                    <option value="christmas" <?php selected($settings['theme'] ?? 'christmas', 'christmas'); ?>>Świąteczny</option>
                    <option value="winter" <?php selected($settings['theme'] ?? 'christmas', 'winter'); ?>>Zimowy</option>
                    <option value="elegant" <?php selected($settings['theme'] ?? 'christmas', 'elegant'); ?>>Elegancki</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="calendar-default-animation">Domyślna animacja</label>
                <select id="calendar-default-animation" class="form-control">
                    <option value="fade" <?php selected($settings['default_animation'] ?? 'fade', 'fade'); ?>>Fade</option>
                    <option value="slide-up" <?php selected($settings['default_animation'] ?? 'fade', 'slide-up'); ?>>Slide Up</option>
                    <option value="zoom" <?php selected($settings['default_animation'] ?? 'fade', 'zoom'); ?>>Zoom</option>
                    <option value="bounce" <?php selected($settings['default_animation'] ?? 'fade', 'bounce'); ?>>Bounce</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="calendar-snow-effect" value="1" <?php checked($settings['snow_effect'] ?? true, true); ?>>
                    Efekt padającego śniegu
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="calendar-confetti-effect" value="1" <?php checked($settings['confetti_effect'] ?? true, true); ?>>
                    Efekt konfetti po otwarciu
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="calendar-enable-stats" value="1" <?php checked($settings['enable_stats'] ?? true, true); ?>>
                    Zbieraj statystyki otwierania
                </label>
            </div>
            
            <button type="button" id="save-calendar" class="btn btn-primary">Zapisz Kalendarz</button>
            
            <?php if ($calendar): ?>
                <a href="<?php echo admin_url('admin.php?page=advent-calendar'); ?>" class="btn btn-secondary">Powrót do listy</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($calendar): ?>
    <div id="doors" class="tab-content">
        <div class="door-editor-grid">
            <?php for ($i = 1; $i <= $total_doors; $i++): 
                $door = null;
                foreach ($doors as $d) {
                    if ($d->door_number == $i) {
                        $door = $d;
                        break;
                    }
                }
            ?>
                <div class="door-editor-item <?php echo $door ? 'has-content' : ''; ?>" 
                     data-door-number="<?php echo $i; ?>" 
                     data-door-id="<?php echo $door ? $door->id : ''; ?>">
                    <?php echo $i; ?>
                </div>
            <?php endfor; ?>
        </div>
        
        <div id="door-form" style="display: none; margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
            <h3>Edytuj Drzwi <span id="door-number-display"></span></h3>
            <div class="door-form-container">
                <input type="hidden" id="door-id">
                <input type="hidden" id="door-number">
                
                <div class="form-group">
                    <label for="door-title">Tytuł</label>
                    <input type="text" id="door-title" class="form-control" placeholder="Tytuł drzwi (opcjonalnie)">
                </div>
                
                <div class="form-group">
                    <label for="door-image">Obrazek</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="door-image" class="form-control" placeholder="URL obrazka" style="flex: 1;">
                        <button type="button" id="upload-door-image" class="btn btn-secondary">Wybierz obrazek</button>
                    </div>
                    <div id="door-image-preview" style="margin-top: 10px;"></div>
                </div>
                
                <div class="form-group">
                    <label>Typ zawartości</label>
                    <div style="display: flex; gap: 20px;">
                        <label><input type="radio" name="door_type" value="modal" checked> Modal</label>
                        <label><input type="radio" name="door_type" value="link"> Link</label>
                        <label><input type="radio" name="door_type" value="inline"> Bezpośrednio</label>
                    </div>
                </div>
                
                <div class="form-group" id="door-link-field" style="display: none;">
                    <label for="door-link">URL Linku</label>
                    <input type="url" id="door-link" class="form-control" placeholder="https://...">
                </div>
                
                <div class="form-group" id="door-content-field">
                    <label for="door-content">Zawartość</label>
                    <textarea id="door-content" class="form-control" rows="6" placeholder="Treść, która pojawi się po otwarciu drzwi..."></textarea>
                    <p class="description">Możesz używać HTML i shortcodów WordPress</p>
                </div>
                
                <div class="form-group">
                    <label for="door-animation">Animacja otwarcia</label>
                    <select id="door-animation" class="form-control">
                        <option value="fade">Fade</option>
                        <option value="slide-up">Slide Up</option>
                        <option value="zoom">Zoom</option>
                        <option value="bounce">Bounce</option>
                        <option value="flip">Flip</option>
                        <option value="rotate">Rotate</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="door-unlock-date">Data odblokowania</label>
                    <input type="date" id="door-unlock-date" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="door-custom-css">Niestandardowy CSS</label>
                    <textarea id="door-custom-css" class="form-control" rows="5" placeholder="Dodatkowe style CSS dla tych drzwi"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="button" id="save-door" class="btn btn-primary">Zapisz Drzwi</button>
                    <button type="button" id="cancel-door" class="btn btn-secondary">Anuluj</button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="styles" class="tab-content">
        <div class="style-editor-preview">
            <h3>Podgląd Kalendarza</h3>
            <div style="display: grid; grid-template-columns: repeat(<?php echo $settings['columns'] ?? 6; ?>, 1fr); gap: 10px; margin: 20px 0;">
                <?php for ($i = 1; $i <= $total_doors; $i++): ?>
                    <div style="aspect-ratio: 1; background: #c41e3a; color: white; display: flex; align-items: center; justify-content: center; border-radius: 5px; font-weight: bold;">
                        <?php echo $i; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="color-picker-group">
            <div class="color-picker-item">
                <label>Kolor główny</label>
                <input type="text" class="color-picker" value="#c41e3a">
            </div>
            <div class="color-picker-item">
                <label>Kolor drugoplanowy</label>
                <input type="text" class="color-picker" value="#165b33">
            </div>
            <div class="color-picker-item">
                <label>Kolor akcentowy</label>
                <input type="text" class="color-picker" value="#ffd700">
            </div>
        </div>
        
        <div class="form-group">
            <label for="custom-css">Niestandardowy CSS</label>
            <textarea id="custom-css" class="form-control" rows="10" placeholder="Dodaj własne style CSS"></textarea>
        </div>
        
        <button type="button" id="save-styles" class="btn btn-primary">Zapisz Style</button>
    </div>
    
    <div id="statistics" class="tab-content">
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Łączne otwarcia</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Unikalni użytkownicy</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Najpopularniejsze drzwi</div>
            </div>
        </div>
        
        <div class="form-group">
            <h3>Statystyki szczegółowe</h3>
            <div id="stats-chart" style="height: 300px; margin: 20px 0;">
                <p style="text-align: center; color: #666; padding: 50px;">Wczytywanie statystyk...</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="advent-calendar-messages"></div>
