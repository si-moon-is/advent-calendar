<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendars = Advent_Calendar::get_calendars();
?>

<div class="wrap advent-calendar-admin">
    <h1>Eksport / Import Kalendarzy</h1>
    
    <div class="advent-tabs">
        <button class="advent-tab active" data-tab="export">Eksport</button>
        <button class="advent-tab" data-tab="import">Import</button>
    </div>
    
    <!-- Tab: Eksport -->
    <div id="export" class="tab-content active">
        <?php if (empty($calendars)): ?>
            <div class="notice notice-info">
                <p>Nie masz jeszcze żadnych kalendarzy do eksportu.</p>
            </div>
        <?php else: ?>
            <div class="calendar-grid">
                <?php foreach ($calendars as $calendar): ?>
                    <div class="calendar-card">
                        <h3><?php echo esc_html($calendar->title); ?></h3>
                        <p><strong>Data utworzenia:</strong> <?php echo date('d.m.Y', strtotime($calendar->created_at)); ?></p>
                        <p><strong>Liczba drzwi:</strong> <?php echo count(Advent_Calendar::get_calendar_doors($calendar->id)); ?></p>
                        
                        <div class="calendar-actions">
                            <a href="<?php echo admin_url('admin-post.php?action=export_calendar&calendar_id=' . $calendar->id); ?>" 
                               class="btn btn-primary">Eksportuj Kalendarz</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Tab: Import -->
    <div id="import" class="tab-content">
        <div class="notice notice-info">
            <p>Importuj kalendarz z pliku JSON wyeksportowanego z innej instalacji wtyczki.</p>
        </div>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import_calendar">
            <?php wp_nonce_field('import_calendar'); ?>
            
            <div class="form-group">
                <label for="import-file">Plik JSON do zaimportowania</label>
                <input type="file" id="import-file" class="form-control" name="import_file" accept=".json" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Importuj Kalendarz</button>
        </form>
    </div>
</div>
