<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendars = Advent_Calendar::get_calendars();
$selected_calendar = isset($_GET['calendar_id']) ? intval($_GET['calendar_id']) : ($calendars ? $calendars[0]->id : 0);
?>

<div class="wrap advent-calendar-admin">
    <h1>Statystyki Kalendarzy Adwentowych</h1>
    
    <?php if (empty($calendars)): ?>
        <div class="notice notice-info">
            <p>Nie masz jeszcze żadnych kalendarzy. <a href="<?php echo admin_url('admin.php?page=advent-calendar-new'); ?>">Utwórz pierwszy kalendarz</a>.</p>
        </div>
    <?php else: ?>
        <div class="form-group">
            <label for="stats-calendar-select">Wybierz kalendarz:</label>
            <select id="stats-calendar-select" class="form-control" style="width: 300px;">
                <?php foreach ($calendars as $calendar): ?>
                    <option value="<?php echo $calendar->id; ?>" <?php selected($selected_calendar, $calendar->id); ?>>
                        <?php echo esc_html($calendar->title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div id="stats-content">
            <!-- Stats will be loaded here via AJAX -->
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
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    function loadStats(calendarId) {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'get_calendar_stats',
                nonce: '<?php echo wp_create_nonce('advent_calendar_admin_nonce'); ?>',
                calendar_id: calendarId
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            }
        });
    }
    
    function updateStatsDisplay(stats) {
        $('.stat-card:eq(0) .stat-number').text(stats.total_opens || 0);
        $('.stat-card:eq(1) .stat-number').text(stats.unique_visitors || 0);
        
        if (stats.popular_doors && stats.popular_doors.length > 0) {
            $('.stat-card:eq(2) .stat-number').text('#' + stats.popular_doors[0].door_number);
            $('.stat-card:eq(2) .stat-label').text(
                'Drzwi ' + stats.popular_doors[0].door_number + 
                ' (' + stats.popular_doors[0].open_count + ' otwarć)'
            );
        }
    }
    
    $('#stats-calendar-select').on('change', function() {
        loadStats($(this).val());
    });
    
    // Load initial stats
    if ($('#stats-calendar-select').val()) {
        loadStats($('#stats-calendar-select').val());
    }
});
</script>
