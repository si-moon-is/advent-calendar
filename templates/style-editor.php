<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendar_id = isset($_GET['calendar_id']) ? intval($_GET['calendar_id']) : 0;
$calendar = $calendar_id ? Advent_Calendar::get_calendar($calendar_id) : null;

if (!$calendar) {
    echo '<div class="error"><p>Kalendarz nie znaleziony.</p></div>';
    return;
}

$styles = Advent_Calendar_Styles::get_calendar_styles($calendar_id);
$current_style = $styles ? $styles[0] : null;
$style_data = $current_style ? json_decode($current_style->styles_data, true) : array();
?>

<div class="wrap advent-calendar-admin">
    <h1>Edytor Stylów - <?php echo esc_html($calendar->title); ?></h1>
    
    <div class="style-editor-container">
        <div class="style-preview-section">
            <h3>Podgląd na żywo</h3>
            <div class="style-preview-calendar" style="background: linear-gradient(135deg, <?php echo $style_data['primary'] ?? '#c41e3a'; ?> 0%, <?php echo $style_data['secondary'] ?? '#165b33'; ?> 100%); border: 3px solid <?php echo $style_data['accent'] ?? '#ffd700'; ?>; padding: 20px; border-radius: 10px;">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <div class="style-preview-door" style="aspect-ratio: 1; background: <?php echo $style_data['primary'] ?? '#c41e3a'; ?>; border: 2px solid <?php echo $style_data['accent'] ?? '#ffd700'; ?>; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                            <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <div class="style-controls-section">
            <h3>Presety Stylów</h3>
            <div id="style-presets" class="presets-grid">
                <div class="preset-item active" data-preset="christmas">
                    <div class="preset-preview" style="background: #c41e3a;"></div>
                    <span class="preset-name">Świąteczny</span>
                </div>
                <div class="preset-item" data-preset="winter">
                    <div class="preset-preview" style="background: #74b9ff;"></div>
                    <span class="preset-name">Zimowy</span>
                </div>
                <div class="preset-item" data-preset="elegant">
                    <div class="preset-preview" style="background: #2d3436;"></div>
                    <span class="preset-name">Elegancki</span>
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Kolory</h3>
            <div class="color-picker-group">
                <div class="color-picker-item">
                    <label for="primary-color">Kolor główny</label>
                    <input type="text" id="primary-color" class="color-picker" value="<?php echo $style_data['primary'] ?? '#c41e3a'; ?>">
                </div>
                <div class="color-picker-item">
                    <label for="secondary-color">Kolor drugoplanowy</label>
                    <input type="text" id="secondary-color" class="color-picker" value="<?php echo $style_data['secondary'] ?? '#165b33'; ?>">
                </div>
                <div class="color-picker-item">
                    <label for="accent-color">Kolor akcentowy</label>
                    <input type="text" id="accent-color" class="color-picker" value="<?php echo $style_data['accent'] ?? '#ffd700'; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="custom-css">Niestandardowy CSS</label>
                <textarea id="custom-css" class="form-control" rows="10" placeholder="Dodaj własne style CSS..."><?php echo $current_style ? esc_textarea($current_style->custom_css) : ''; ?></textarea>
                <p class="description">Możesz dodać dodatkowe style CSS, które zostaną zastosowane do tego kalendarza.</p>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" id="save-styles" class="btn btn-primary">Zapisz Style</button>
                <button type="button" id="apply-preset" class="btn btn-secondary">Zastosuj Preset</button>
                <button type="button" id="reset-styles" class="btn btn-danger">Resetuj</button>
                <a href="<?php echo admin_url('admin.php?page=advent-calendar-new&calendar_id=' . $calendar_id); ?>" class="btn btn-secondary">Powrót</a>
            </div>
        </div>
    </div>
</div>

<div id="style-editor-messages"></div>

<style>
.presets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.preset-item {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.preset-item:hover {
    border-color: #0073aa;
}

.preset-item.active {
    border-color: #0073aa;
    background: #f0f8ff;
}

.preset-preview {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto 10px;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preset-name {
    font-weight: 600;
    color: #333;
}

.style-editor-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 20px;
}

@media (max-width: 1024px) {
    .style-editor-container {
        grid-template-columns: 1fr;
    }
}

.style-preview-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.style-controls-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
