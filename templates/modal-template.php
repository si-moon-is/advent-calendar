<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="advent-modal" id="advent-modal-template" style="display: none;">
    <div class="advent-modal-content">
        <button class="advent-modal-close">&times;</button>
        <div class="door-content-wrapper">
            <div class="door-loading" style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
                <p>Ładowanie zawartości...</p>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="door-content-template">
    <div class="door-content-container">
        {{#image}}
        <div class="door-image">
            <img src="{{image}}" alt="{{title}}">
        </div>
        {{/image}}
        
        {{#title}}
        <h3 class="door-title">{{title}}</h3>
        {{/title}}
        
        <div class="door-content-text">
            {{{content}}}
        </div>
        
        {{#hasLink}}
        <div class="door-actions" style="text-align: center; margin-top: 20px;">
            <a href="{{linkUrl}}" class="btn btn-primary" target="_blank">Przejdź do strony</a>
        </div>
        {{/hasLink}}
    </div>
</script>

<style>
.advent-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    animation: modalFadeIn 0.3s ease;
}

.advent-modal.active {
    display: flex;
}

.advent-modal-content {
    background: white;
    padding: 40px;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: modalContentSlideIn 0.3s ease;
}

.advent-modal-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.advent-modal-close:hover {
    color: #000;
}

.door-content-container {
    max-width: 100%;
}

.door-image {
    text-align: center;
    margin-bottom: 20px;
}

.door-image img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.door-title {
    font-size: 2em;
    margin-bottom: 15px;
    color: #c41e3a;
    text-align: center;
}

.door-content-text {
    font-size: 1.1em;
    line-height: 1.6;
    color: #333;
}

.door-content-text img {
    max-width: 100%;
    height: auto;
}

.door-content-text iframe {
    max-width: 100%;
}

.door-actions {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #c41e3a;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #a0182e;
    color: white;
}

@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalContentSlideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@media (max-width: 768px) {
    .advent-modal-content {
        padding: 25px;
        margin: 20px;
    }
    
    .door-title {
        font-size: 1.5em;
    }
}
</style>
