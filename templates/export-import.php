<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendars = Advent_Calendar::get_calendars();
?>

<div class="wrap advent-calendar-admin">
    <h1>Eksport / Import Kalendarzy</h1>
    
    <div class="advent-tabs">
        <button type="button" class="advent-tab active" data-tab="export">Eksport</button>
        <button type="button" class="advent-tab" data-tab="import">Import</button>
    </div>
    
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
    
    <div id="import" class="tab-content">
        <div class="import-instructions">
            <div class="notice notice-info">
                <p><strong>Instrukcja importu:</strong></p>
                <ul>
                    <li>Importuj kalendarz z pliku JSON wyeksportowanego z innej instalacji wtyczki</li>
                    <li>Plik powinien mieć rozszerzenie .json</li>
                    <li>Import utworzy nowy kalendarz z zaimportowanymi ustawieniami i drzwiami</li>
                </ul>
            </div>
        </div>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" class="import-form">
            <input type="hidden" name="action" value="import_calendar">
            <?php wp_nonce_field('import_calendar'); ?>
            
            <div class="form-group">
                <label for="import-file">Plik JSON do zaimportowania</label>
                <input type="file" id="import-file" class="form-control" name="import_file" accept=".json" required>
                <p class="description">Wybierz plik .json wyeksportowany z kalendarza adwentowego</p>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="import_settings" value="1" checked>
                    Importuj ustawienia kalendarza
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="import_doors" value="1" checked>
                    Importuj drzwi i ich zawartość
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="import_styles" value="1" checked>
                    Importuj style i ustawienia wizualne
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Importuj Kalendarz</button>
        </form>
    </div>
</div>

<style>
.import-instructions {
    margin-bottom: 30px;
}

.import-instructions ul {
    margin-left: 20px;
    margin-top: 10px;
}

.import-instructions li {
    list-style-type: disc;
    margin-bottom: 5px;
}

.import-form {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 600px;
}

.import-form .form-group {
    margin-bottom: 20px;
}

.import-form .description {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}
</style>
