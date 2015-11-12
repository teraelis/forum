<?php
$counter = rand();
$filter = $this->getFilter();
$show = 'Afficher';?>

<div class="decoda-spoiler">
    <button class="decoda-spoiler-button" type="button" onclick="<?php echo str_replace('{id}', $counter, $spoilerToggle); ?>"><?php echo $show; ?></button>

    <div class="decoda-spoiler-content" id="spoiler-content-<?php echo $counter; ?>" style="display: none">
        <?php echo $content; ?>
    </div>
</div>