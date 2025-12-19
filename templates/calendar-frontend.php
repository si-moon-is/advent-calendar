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
?>

<div class="advent-calendar-container advent-theme-<?php echo esc_attr($theme); ?>">
    <div class="advent-calendar" 
         data-calendar-id="<?php echo esc_attr($calendar_id); ?>" 
         data-columns="<?php echo esc_attr($columns); ?>">
        
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
                ?>
                    
                    <div class="advent-door <?php echo esc_attr($door_class); ?>" 
                         data-door-id="<?php echo esc_attr($door_id); ?>"
                         data-calendar-id="<?php echo esc_attr($calendar_id); ?>"
                         data-door-number="<?php echo intval($i); ?>"
                         style="aspect-ratio: 1; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5em; cursor: pointer; transition: all 0.3s ease; position: relative;">
                        
                        <!-- Numer drzwi -->
                        <span class="door-number" style="position: absolute; top: 8px; left: 8px; background: rgba(0,0,0,0.7); color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8em;">
                            <?php echo intval($i); ?>
                        </span>
                        
                        <?php if ($door && !empty($door->image_url) && $user_has_opened): ?>
                            <!-- Obrazek po otwarciu -->
                            <div style="width: 100%; height: 100%; background-image: url('<?php echo esc_url($door->image_url); ?>'); background-size: cover; background-position: center; border-radius: 8px;"></div>
                        <?php elseif ($user_has_opened): ?>
                            <!-- Ikona prezentu po otwarciu -->
                            <span style="font-size: 2em;">🎁</span>
                        <?php else: ?>
                            <!-- Domyślny wygląd zamkniętych drzwi -->
                            <div style="width: 100%; height: 100%; background: linear-gradient(145deg, #d4af37, #ffd700); border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 3px solid #b8860b;">
                                <span style="color: #8b4513; font-size: 1.8em;"><?php echo intval($i); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Wczytaj CSS z pliku zamiast inline -->
<link rel="stylesheet" href="<?php echo ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/frontend.css'; ?>">

<script>
// Inicjalizacja kalendarza po załadowaniu DOM
jQuery(document).ready(function($) {
    console.log('Advent Calendar loaded: #<?php echo $calendar_id; ?>');
    
    // Obsługa kliknięcia w drzwi
    $('.advent-door.available').on('click', function(e) {
        e.preventDefault();
        var door = $(this);
        var doorId = door.data('door-id');
        
        if (doorId) {
            console.log('Opening door:', doorId);
            // Tutaj dodaj AJAX do otwierania drzwi
            door.removeClass('available').addClass('open');
            door.html('<span style="font-size: 2em;">🎁</span>');
        }
    });
    
    // Pokazuj podpowiedź dla admina
    $('.advent-door.locked').on('click', function(e) {
        e.preventDefault();
        alert('Te drzwi są jeszcze zablokowane!');
    });
});
</script>
