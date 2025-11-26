<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendars = Advent_Calendar::get_calendars();
?>

<div class="wrap advent-calendar-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-calendar-alt"></span>
        Kalendarze Adwentowe
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=advent-calendar-new'); ?>" class="page-title-action">
        Dodaj Nowy
    </a>
    
    <hr class="wp-header-end">
    
    <?php if (empty($calendars)): ?>
        <div class="notice notice-info">
            <p>Nie masz jeszcze żadnych kalendarzy. <a href="<?php echo admin_url('admin.php?page=advent-calendar-new'); ?>">Utwórz pierwszy kalendarz</a>!</p>
        </div>
    <?php else: ?>
        <div class="calendar-grid">
            <?php foreach ($calendars as $calendar): 
                $settings = json_decode($calendar->settings, true) ?: array();
                $doors = Advent_Calendar::get_calendar_doors($calendar->id);
                $configured_doors = count(array_filter($doors, function($door) {
                    return !empty($door->title) || !empty($door->content) || !empty($door->image_url);
                }));
            ?>
                <div class="calendar-card">
                    <div class="calendar-header">
                        <h3><?php echo esc_html($calendar->title); ?></h3>
                        <div class="calendar-actions">
                            <a href="<?php echo admin_url('admin.php?page=advent-calendar-new&calendar_id=' . $calendar->id); ?>" 
                               class="button button-small">
                                Edytuj
                            </a>
                            <button class="button button-small button-link-delete delete-calendar" 
                                    data-calendar-id="<?php echo $calendar->id; ?>">
                                Usuń
                            </button>
                        </div>
                    </div>
                    
                    <div class="calendar-info">
                        <div class="info-item">
                            <span class="label">Data utworzenia:</span>
                            <span class="value"><?php echo date('d.m.Y H:i', strtotime($calendar->created_at)); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Rozmiar:</span>
                            <span class="value"><?php echo esc_html($settings['columns'] ?? 6); ?>x<?php echo esc_html($settings['rows'] ?? 4); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Skonfigurowane drzwi:</span>
                            <span class="value"><?php echo $configured_doors; ?>/<?php echo ($settings['columns'] ?? 6) * ($settings['rows'] ?? 4); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Data rozpoczęcia:</span>
                            <span class="value"><?php echo esc_html($settings['start_date'] ?? 'Nie ustawiono'); ?></span>
                        </div>
                    </div>
                    
                    <div class="calendar-footer">
                        <div class="shortcode-container">
                            <code>[advent_calendar id="<?php echo $calendar->id; ?>"]</code>
                            <button class="button button-small copy-shortcode" 
                                    data-shortcode='[advent_calendar id="<?php echo $calendar->id; ?>"]'>
                                Kopiuj
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.calendar-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.calendar-header h3 {
    margin: 0;
    color: #23282d;
}

.calendar-actions {
    display: flex;
    gap: 5px;
}

.calendar-info {
    margin-bottom: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f1;
}

.info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-item .label {
    font-weight: 500;
    color: #646970;
}

.info-item .value {
    color: #23282d;
}

.calendar-footer {
    border-top: 1px solid #f0f0f1;
    padding-top: 15px;
}

.shortcode-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.shortcode-container code {
    flex: 1;
    background: #f6f7f7;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
}

.copy-shortcode {
    white-space: nowrap;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            const originalText = $(this).text();
            $(this).text('Skopiowano!');
            setTimeout(() => {
                $(this).text(originalText);
            }, 2000);
        }.bind(this));
    });
});
</script>
