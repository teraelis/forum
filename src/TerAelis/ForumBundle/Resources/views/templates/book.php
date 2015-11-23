<div class="book js-book" data-styles="<?php echo htmlentities((isset($styles) ? $styles : 'light,dark'), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="book-buttons js-book-buttons">
        <button type="button" class="btn js-book-button-open">
            Ouvrir la lecture confortable
        </button>
    </div>

    <div class="book-content">
        <div class="js-content js-book-content">
            <?php echo $content; ?>
        </div>
    </div>
</div>
