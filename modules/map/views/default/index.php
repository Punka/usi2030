<!--<div class="map-default-index"></div>-->
<div id="map" <?php if(isset($width)) : ?> style="width: <?php echo $width; ?>px;" <?php endif; ?> ></div>
<?php
use app\components\MapWidget;
echo MapWidget::widget();
?>