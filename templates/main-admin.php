<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendars = Advent_Calendar::get_calendars();
?>

<div class="wrap advent-calendar-admin">
    <h1 class="wp-heading-inline">Kalendarze Adwentowe</h1>
    <a href="<?php echo admin_url('admin.php?page=advent-calendar-new'); ?>" class="page-title-action">Dodaj Nowy</a>
    
    <hr class="wp-header-end">
    
    <?php if (empty($calendars)): ?>
        <div class="notice notice-info">
            <p>Nie masz jeszcze żadnych kalendarzy. <a href="<?php echo admin_url('admin.php?page=advent-calendar-new'); ?>">Utwórz pierwszy kalendarz</a>.</p>
        </div>
    <?php else: ?>
        <div class="calendar-grid">
            <?php foreach ($calendars as $calendar): 
                $settings = json_decode($calendar->settings, true);
                $doors = Advent_Calendar::get_calendar_doors($calendar->id);
            ?>
                <div class="calendar-card">
                    <h3><?php echo esc_html($calendar->title); ?></h3>
                    <p><strong>Data utworzenia:</strong> <?php echo date('d.m.Y', strtotime($calendar->created_at)); ?></p>
                    <p><strong>Liczba drzwi:</strong> <?php echo count($doors); ?></p>
                    <p><strong>Motyw:</strong> <?php echo ucfirst($settings['theme'] ?? 'christmas'); ?></p>
                    
                    <div class="calendar-actions">
                        <a href="<?php echo admin_url('admin.php?page=advent-calendar-new&calendar_id=' . $calendar->id); ?>" class="btn btn-primary">Edytuj</a>
                        <a href="#" class="btn btn-secondary get-shortcode" data-calendar-id="<?php echo $calendar->id; ?>">Pobierz Shortcode</a>
                        <button class="btn btn-danger delete-calendar" data-calendar-id="<?php echo $calendar->id; ?>">Usuń</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('.delete-calendar').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Czy na pewno chcesz usunąć ten kalendarz? Ta operacja jest nieodwracalna.')) {
            return;
        }
        
        var button = $(this);
        var calendarId = button.data('calendar-id');
        
        $.ajax({
            url: adventCalendar.ajaxurl,
            type: 'POST',
            data: {
                action: 'advent_calendar_delete',
                nonce: adventCalendar.nonce,
                calendar_id: calendarId
            },
            success: function(response) {
                if (response.success) {
                    button.closest('.calendar-card').fadeOut();
                } else {
                    alert('Błąd podczas usuwania kalendarza.');
                }
            }
        });
    });

    $('.get-shortcode').on('click', function(e) {
        e.preventDefault();
        var calendarId = $(this).data('calendar-id');
        alert('Shortcode: [advent_calendar id="' + calendarId + '"]');
    });
});
</script>
