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
            
            <div class="stats-details">
                <div class="stats-section">
                    <h3>Otwarcia według dni</h3>
                    <div id="daily-stats-chart" style="height: 300px; background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
                
                <div class="stats-section">
                    <h3>Top 5 najpopularniejszych drzwi</h3>
                    <div id="popular-doors-chart" style="height: 300px; background: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <canvas id="doorsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    let dailyChart = null;
    let doorsChart = null;
    
    function loadStats(calendarId) {
    console.log('Loading stats for calendar:', calendarId);
    
    $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'get_calendar_stats',
            nonce: '<?php echo wp_create_nonce('advent_calendar_nonce'); ?>',
            calendar_id: calendarId
        },
        success: function(response) {
            console.log('AJAX Response:', response);
            if (response.success) {
                updateStatsDisplay(response.data);
                updateCharts(response.data);
            } else {
                console.error('AJAX Error:', response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Request Failed:', error);
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
    
    function updateCharts(stats) {
        updateDailyChart(stats.daily_opens);
        updateDoorsChart(stats.popular_doors);
    }
    
    function updateDailyChart(dailyOpens) {
        const ctx = document.getElementById('dailyChart').getContext('2d');
        
        if (dailyChart) {
            dailyChart.destroy();
        }
        
        const labels = dailyOpens ? dailyOpens.map(item => item.date) : [];
        const data = dailyOpens ? dailyOpens.map(item => item.count) : [];
        
        dailyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Otwarcia dzienne',
                    data: data,
                    borderColor: '#c41e3a',
                    backgroundColor: 'rgba(196, 30, 58, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Liczba otwarć'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Data'
                        }
                    }
                }
            }
        });
    }
    
    function updateDoorsChart(popularDoors) {
        const ctx = document.getElementById('doorsChart').getContext('2d');
        
        if (doorsChart) {
            doorsChart.destroy();
        }
        
        const labels = popularDoors ? popularDoors.map(item => 'Drzwi ' + item.door_number) : [];
        const data = popularDoors ? popularDoors.map(item => item.open_count) : [];
        const backgroundColors = [
            '#c41e3a', '#165b33', '#ffd700', '#74b9ff', '#2d3436'
        ];
        
        doorsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Liczba otwarć',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Liczba otwarć'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Numer drzwi'
                        }
                    }
                }
            }
        });
    }
    
    $('#stats-calendar-select').on('change', function() {
        loadStats($(this).val());
    });
    
    if ($('#stats-calendar-select').val()) {
        loadStats($('#stats-calendar-select').val());
    }
});
</script>

<style>
.stats-details {
    margin-top: 30px;
}

.stats-section {
    margin-bottom: 40px;
}

.stats-section h3 {
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #c41e3a;
    padding-bottom: 5px;
}
</style>
