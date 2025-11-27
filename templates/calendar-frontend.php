<?php
if (!defined('ABSPATH')) {
    exit;
}

// Pobierz calendar_id z atrybutów shortcode
$calendar_id = isset($atts['id']) ? intval($atts['id']) : 0;
$calendar = $calendar_id ? Advent_Calendar::get_calendar($calendar_id) : null;

if (!$calendar) {
    return '<p>Kalendarz nie znaleziony</p>';
}

$doors = Advent_Calendar::get_calendar_doors($calendar_id);
$settings = json_decode($calendar->settings, true);
$columns = isset($settings['columns']) ? intval($settings['columns']) : 6;
$rows = isset($settings['rows']) ? intval($settings['rows']) : 4;
$theme = isset($settings['theme']) ? sanitize_text_field($settings['theme']) : 'christmas';
$total_doors = $columns * $rows;

// Bezpieczne ustawienia domyślne
$safe_settings = wp_parse_args($settings, array(
    'columns' => 6,
    'rows' => 4,
    'start_date' => date('Y-12-01'),
    'end_date' => date('Y-12-24'),
    'theme' => 'christmas'
));
?>

<div class="advent-calendar advent-theme-<?php echo esc_attr($theme); ?>" 
     data-calendar-id="<?php echo esc_attr($calendar_id); ?>" 
     data-columns="<?php echo esc_attr($columns); ?>"
     style="display: grid; grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr); gap: 15px; margin: 30px 0; padding: 30px;">
    
    <?php 
    for ($i = 1; $i <= $total_doors; $i++): 
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
        
        <div class="advent-calendar-door door <?php echo esc_attr($door_class); ?> door-<?php echo intval($i); ?>" 
             data-door-id="<?php echo esc_attr($door_id); ?>"
             data-calendar-id="<?php echo esc_attr($calendar_id); ?>"
             data-door-number="<?php echo intval($i); ?>">
    
            <span class="door-number"><?php echo intval($i); ?></span>
    
            <?php if ($door && !empty($door->image_url)): ?>
                <!-- Obrazek z bazy -->
                <div class="door-image-container <?php echo $user_has_opened ? 'opened' : 'closed'; ?>">
                    <img src="<?php echo esc_url($door->image_url); ?>" alt="Door <?php echo intval($i); ?>" class="door-main-image">
                    <?php if (!$user_has_opened): ?>
                        <div class="door-overlay"></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Domyślny wygląd z gradientami i emoji -->
                <div class="door-default-content <?php echo $user_has_opened ? 'opened' : 'closed'; ?> 
                     <?php echo esc_attr($safe_settings['theme']) === 'christmas' ? 'christmas-default' : ''; ?>">
                     
                    <?php if ($user_has_opened): ?>
                        <span class="door-icon">🎁</span>
                    <?php else: ?>
                        <!-- Gradient + emoji -->
                        <div class="default-christmas-image door-<?php echo intval($i); ?>"></div>
                        <span class="door-number-default"><?php echo intval($i); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>

<style>
.advent-calendar {
    border-radius: 15px;
    position: relative;
    overflow: hidden;
}

.advent-calendar.theme-christmas {
    background: linear-gradient(135deg, #c41e3a 0%, #165b33 100%);
    border: 3px solid #ffd700;
}

.advent-calendar.theme-winter {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    border: 3px solid #dfe6e9;
}

.advent-calendar.theme-elegant {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    border: 3px solid #fd79a8;
}

.advent-calendar-door {
    position: relative;
    aspect-ratio: 1;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5em;
    transition: all 0.4s ease;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    overflow: hidden;
    background: rgba(255,255,255,0.2);
}

.advent-calendar-door.available {
    cursor: pointer;
}

.advent-calendar-door.available:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 12px 20px rgba(0,0,0,0.2);
}

.advent-calendar-door.locked {
    cursor: not-allowed;
    opacity: 0.6;
}

.advent-calendar-door.open {
    cursor: default;
}

/* Gradienty dla każdego drzwi */
.door-1 { background: linear-gradient(135deg, #c41e3a, #8b0000); }
.door-2 { background: linear-gradient(135deg, #165b33, #0d3d21); }
.door-3 { background: linear-gradient(135deg, #ffd700, #b8860b); }
.door-4 { background: linear-gradient(135deg, #1e3a8a, #1e40af); }
.door-5 { background: linear-gradient(135deg, #7e22ce, #6b21a8); }
.door-6 { background: linear-gradient(135deg, #dc2626, #b91c1c); }
.door-7 { background: linear-gradient(135deg, #059669, #047857); }
.door-8 { background: linear-gradient(135deg, #d97706, #b45309); }
.door-9 { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
.door-10 { background: linear-gradient(135deg, #db2777, #be185d); }
.door-11 { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.door-12 { background: linear-gradient(135deg, #84cc16, #65a30d); }
.door-13 { background: linear-gradient(135deg, #f59e0b, #d97706); }
.door-14 { background: linear-gradient(135deg, #ef4444, #dc2626); }
.door-15 { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.door-16 { background: linear-gradient(135deg, #ec4899, #db2777); }
.door-17 { background: linear-gradient(135deg, #14b8a6, #0d9488); }
.door-18 { background: linear-gradient(135deg, #f97316, #ea580c); }
.door-19 { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.door-20 { background: linear-gradient(135deg, #10b981, #059669); }
.door-21 { background: linear-gradient(135deg, #f43f5e, #e11d48); }
.door-22 { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.door-23 { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.door-24 { background: linear-gradient(135deg, #84cc16, #65a30d); }

.advent-calendar-door .door-number {
    position: absolute;
    top: 8px;
    left: 8px;
    font-size: 0.9em;
    background: rgba(0,0,0,0.3);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* Kontener obrazka */
.door-image-container {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.door-main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

/* Przyciemnienie dla zamkniętych drzwi */
.door-image-container.closed .door-main-image {
    filter: brightness(0.3) blur(1px);
}

.door-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
}

.door-image-container.opened .door-main-image {
    filter: brightness(1);
}

/* Domyślne obrazki z emoji */
.default-christmas-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Emoji dla każdego drzwi */
.door-1 .default-christmas-image::before { content: "1️⃣️"; }
.door-2 .default-christmas-image::before { content: "2️⃣️"; }
.door-3 .default-christmas-image::before { content: "3️⃣️"; }
.door-4 .default-christmas-image::before { content: "4️⃣️"; }
.door-5 .default-christmas-image::before { content: "5️⃣️"; }
.door-6 .default-christmas-image::before { content: "6️⃣️"; }
.door-7 .default-christmas-image::before { content: "7️⃣️"; }
.door-8 .default-christmas-image::before { content: "8️⃣️"; }
.door-9 .default-christmas-image::before { content: "9️⃣️"; }
.door-10 .default-christmas-image::before { content: "🔟"; }
.door-11 .default-christmas-image::before { content: "1️⃣️1️⃣️"; }
.door-12 .default-christmas-image::before { content: "1️⃣️2️⃣️"; }
.door-13 .default-christmas-image::before { content: "1️⃣️3️⃣️"; }
.door-14 .default-christmas-image::before { content: "1️⃣️4️⃣️"; }
.door-15 .default-christmas-image::before { content: "1️⃣️5️⃣️"; }
.door-16 .default-christmas-image::before { content: "1️⃣️6️⃣️"; }
.door-17 .default-christmas-image::before { content: "1️⃣️7️⃣️"; }
.door-18 .default-christmas-image::before { content: "1️⃣️8️⃣️"; }
.door-19 .default-christmas-image::before { content: "1️⃣️9️⃣️"; }
.door-20 .default-christmas-image::before { content: "2️⃣️0️⃣️"; }
.door-21 .default-christmas-image::before { content: "2️⃣️1️⃣️"; }
.door-22 .default-christmas-image::before { content: "2️⃣️2️⃣️"; }
.door-23 .default-christmas-image::before { content: "2️⃣️3️⃣️"; }
.door-24 .default-christmas-image::before { content: "2️⃣️4️⃣️"; }

.default-christmas-image::before {
    font-size: 2.5em;
    color: gold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
    font-weight: bold;
    z-index: 2;
}

.door-number-default {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5em;
    font-weight: bold;
    color: gold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    z-index: 2;
}

/* Domyślna zawartość bez obrazka */
.door-default-content {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.door-default-content.closed {
    border: 3px solid gold;
}

.door-default-content.opened {
    background: linear-gradient(145deg, #90ee90, #32cd32);
    border: 3px solid #228b22;
}

.door-icon {
    font-size: 2em;
    z-index: 2;
}

@media (max-width: 768px) {
    .advent-calendar {
        gap: 10px;
        padding: 15px;
        grid-template-columns: repeat(3, 1fr) !important;
    }
    
    .advent-calendar-door {
        font-size: 1.2em;
    }
    
    .default-christmas-image::before {
        font-size: 2em;
    }
}

@media (max-width: 480px) {
    .advent-calendar {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .default-christmas-image::before {
        font-size: 1.8em;
    }
}
</style>
