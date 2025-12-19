<?php
if (!defined('ABSPATH')) {
    exit;
}

// Debugowanie - TYLKO dla admina
if (current_user_can('manage_options')) {
    echo '<!-- ADVENT CALENDAR DEBUG: ';
    echo 'Calendar ID: ' . $calendar_id;
    echo ' | Doors: ' . count($doors);
    echo ' | Columns: ' . $columns;
    echo ' | Theme: ' . $theme;
    echo ' -->';
}

// Bezpieczne domyślne ustawienia
$safe_settings = array(
    'columns' => $columns,
    'rows' => 4,
    'start_date' => date('Y-12-01'),
    'end_date' => date('Y-12-24'),
    'theme' => $theme
);

// Ścieżka do obrazków motywu (sprawdź obie możliwe ścieżki)
$theme_images_base = '';
$theme_paths_to_check = array(
    ADVENT_CALENDAR_PLUGIN_PATH . "templates/thems/{$theme}/images/",
    ADVENT_CALENDAR_PLUGIN_PATH . "templates/themes/{$theme}/images/"
);

foreach ($theme_paths_to_check as $path) {
    if (is_dir($path) && file_exists($path . 'door-1.png')) {
        $theme_images_base = str_replace(ADVENT_CALENDAR_PLUGIN_PATH, ADVENT_CALENDAR_PLUGIN_URL, $path);
        break;
    }
}
?>

<div class="advent-calendar-container advent-theme-<?php echo esc_attr($theme); ?>">
    <div class="advent-calendar" 
         data-calendar-id="<?php echo esc_attr($calendar_id); ?>" 
         data-columns="<?php echo esc_attr($columns); ?>"
         data-theme="<?php echo esc_attr($theme); ?>">
        
        <?php if (empty($doors)): ?>
            <p class="advent-error">Brak skonfigurowanych drzwi dla tego kalendarza.</p>
        <?php else: ?>
            <div class="doors-grid" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr); gap: 15px; padding: 20px;">
                <?php for ($i = 1; $i <= $total_doors; $i++): 
                    $door = null;
                    foreach ($doors as $d) {
                        if ($d->door_number == $i) {
                            $door = $d;
                            break;
                        }
                    }
                    
                    $can_open = $door ? Advent_Calendar::can_unlock_door($door->door_number, $safe_settings) : false;
                    $user_has_opened = $door ? Advent_Calendar::has_user_opened_door($door->id) : false;
                    $door_class = $user_has_opened ? 'open' : ($can_open ? 'available' : 'locked');
                    $door_id = $door ? intval($door->id) : 0;
                    
                    // Obrazek użytkownika (jeśli został ustawiony w adminie)
                    $user_image_url = $door && !empty($door->image_url) ? $door->image_url : '';
                    
                    // Obrazek motywu (domyślny)
                    $theme_image_url = $theme_images_base ? $theme_images_base . 'door-' . $i . '.png' : '';
                    
                    // Sprawdź czy obrazek motywu istnieje
                    $has_theme_image = false;
                    if ($theme_image_url) {
                        $theme_image_path = str_replace(ADVENT_CALENDAR_PLUGIN_URL, ADVENT_CALENDAR_PLUGIN_PATH, $theme_image_url);
                        $has_theme_image = file_exists($theme_image_path);
                    }
                ?>
                    
                    <div class="advent-door <?php echo esc_attr($door_class); ?> door-<?php echo intval($i); ?>" 
                         data-door-id="<?php echo esc_attr($door_id); ?>"
                         data-calendar-id="<?php echo esc_attr($calendar_id); ?>"
                         data-door-number="<?php echo intval($i); ?>"
                         style="aspect-ratio: 1; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5em; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden;">
                        
                        <!-- Numer drzwi -->
                        <span class="door-number" style="position: absolute; top: 8px; left: 8px; background: rgba(0,0,0,0.7); color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8em; z-index: 10; border: 1px solid #ffd700;">
                            <?php echo intval($i); ?>
                        </span>
                        
                        <?php if ($user_has_opened && $user_image_url): ?>
                            <!-- PO otwarciu: Obrazek użytkownika -->
                            <div class="door-content" style="width: 100%; height: 100%; background-image: url('<?php echo esc_url($user_image_url); ?>'); background-size: cover; background-position: center; border-radius: 8px;"></div>
                            <div class="door-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 1;"></div>
                        
                        <?php elseif ($user_has_opened && !$user_image_url): ?>
                            <!-- PO otwarciu: Brak obrazka użytkownika - pokaż ikonę -->
                            <div style="width: 100%; height: 100%; background: linear-gradient(145deg, #90ee90, #32cd32); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 3px solid #228b22;">
                                <span style="font-size: 2em; z-index: 2;">🎁</span>
                            </div>
                        
                        <?php elseif ($has_theme_image && !$user_has_opened): ?>
                            <!-- PRZED otwarciem: Obrazek motywu -->
                            <div style="width: 100%; height: 100%; background-image: url('<?php echo esc_url($theme_image_url); ?>'); background-size: cover; background-position: center; border-radius: 8px; filter: brightness(0.8);"></div>
                            <div class="door-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1;">
                                <span style="color: white; font-size: 1.5em; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"><?php echo intval($i); ?></span>
                            </div>
                        
                        <?php else: ?>
                            <!-- Domyślny wygląd: Gradient (brak obrazków motywu) -->
                            <div style="width: 100%; height: 100%; background: linear-gradient(145deg, #d4af37, #ffd700); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 3px solid #b8860b;">
                                <span style="color: #8b4513; font-size: 1.8em; z-index: 2;"><?php echo intval($i); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            
            <!-- Debug info tylko dla admina -->
            <?php if (current_user_can('manage_options')): ?>
                <div style="background: #f8f9fa; padding: 10px; margin-top: 20px; border-radius: 5px; font-size: 12px;">
                    <strong>Debug info:</strong><br>
                    Theme: <?php echo $theme; ?><br>
                    Theme images base: <?php echo $theme_images_base ? $theme_images_base : 'NOT FOUND'; ?><br>
                    First door theme image: <?php echo $theme_images_base ? $theme_images_base . 'door-1.png' : 'N/A'; ?><br>
                    Door 1 has theme image: <?php echo $has_theme_image ? 'YES' : 'NO'; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Wczytaj CSS -->
<link rel="stylesheet" href="<?php echo ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/frontend.css'; ?>">

<script>
jQuery(document).ready(function($) {
    console.log('Advent Calendar loaded: #<?php echo $calendar_id; ?>');
    console.log('Theme: <?php echo $theme; ?>');
    
    // Obsługa kliknięcia w drzwi
    $('.advent-door.available').on('click', function(e) {
        e.preventDefault();
        var door = $(this);
        var doorId = door.data('door-id');
        var calendarId = door.data('calendar-id');
        var doorNumber = door.data('door-number');
        
        if (!doorId) {
            console.error('No door ID');
            return;
        }
        
        console.log('Opening door:', doorId, 'Calendar:', calendarId);
        
        // AJAX do otwierania drzwi
        $.ajax({
            url: adventCalendar.ajaxurl,
            type: 'POST',
            data: {
                action: 'open_door',
                door_id: doorId,
                calendar_id: calendarId,
                user_session: 'test_session_' + Date.now(),
                nonce: adventCalendar.nonce
            },
            beforeSend: function() {
                door.addClass('loading');
            },
            success: function(response) {
                console.log('Door open response:', response);
                door.removeClass('loading');
                
                if (response.success) {
                    // Oznacz jako otwarte
                    door.removeClass('available').addClass('open');
                    
                    // Jeśli response ma obrazek, zaktualizuj
                    if (response.data && response.data.image_url) {
                        door.find('.door-content').css('background-image', 'url("' + response.data.image_url + '")');
                    }
                    
                    // Pokazuj confetti
                    if (window.createConfetti) {
                        createConfetti();
                    }
                } else {
                    alert('Błąd: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                door.removeClass('loading');
                alert('Błąd połączenia z serwerem');
            }
        });
    });
    
    // Pokazuj podpowiedź dla zablokowanych drzwi
    $('.advent-door.locked').on('click', function(e) {
        e.preventDefault();
        var doorNumber = $(this).data('door-number');
        alert('Drzwi #' + doorNumber + ' są jeszcze zablokowane! Odblokują się w odpowiednim dniu.');
    });
});
</script>
