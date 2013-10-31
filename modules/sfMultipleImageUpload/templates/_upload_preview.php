<?php
/**
 * Превью для просмотра и загруженного изображения.
 * Содержит контролы для работы с изображением.
 *
 * @param Image            $image
 * @param sfDoctrineRecord $object
 * @param string           $routePrefix
 * @param string           $display - none|block - CSS правило
 */
use_helper('jQuery', 'Replica');

$display = isset($display) ? (string) $display : 'block';
?>

<li id="uploaded_image_<?php echo $routePrefix; ?>_<?php echo $image->getId(); ?>" style="display: <?php echo $display; ?>">
    <div class="uploaded_preview">
        <?php echo thumbnail('uploaded_preview', new Replica_ImageProxy_FromFile(sfConfig::get('sf_upload_dir').'/'.$image->getPath())); ?>
    </div>

    <?php echo jq_link_to_remote('<i class="icon icon-trash icon-white"></i> удалить', array(
        'url'       => url_for($routePrefix . '_image_delete', $image),
        'csrf'      => 1,
        'sf_method' => 'delete',
        'success'   => jq_visual_effect('fadeOut', '#uploaded_image_'.$routePrefix.'_'.$image->getId(), array('speed' => 'slow')),
    ), array('class' => 'delete btn btn-danger btn-mini')); ?>
</li>
