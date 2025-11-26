<?php
$calendar = Advent_Calendar::get_calendar($atts['id']);
$doors = Advent_Calendar::get_calendar_doors($atts['id']);
$settings = json_decode($calendar->settings, true);

$columns = $atts['columns'] ?? $settings['columns'] ?? 6;
$theme = $atts['theme'] ?? $settings['theme'] ?? 'christmas';
?>

<div class="advent-calendar theme-<?php echo $theme; ?>" 
     style="grid-template-columns: repeat(<?php echo $columns; ?>, 1fr)">
    
    <?php foreach ($doors as $door): ?>
        <?php
        $can_open = Advent_Calendar::can_unlock_door($door->door_number, $settings);
        $is_open = $door->is_open;
        $door_class = $is_open ? 'open' : ($can_open ? 'available' : 'locked');
        ?>
        
        <div class="advent-calendar-door door <?php echo $door_class; ?>" 
             data-door-id="<?php echo $door->id; ?>"
             data-calendar-id="<?php echo $atts['id']; ?>">
            
            <span class="door-number"><?php echo $door->door_number; ?></span>
            
            <?php if ($is_open && $door->image_url): ?>
                <img src="<?php echo $door->image_url; ?>" alt="Door <?php echo $door->door_number; ?>">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
