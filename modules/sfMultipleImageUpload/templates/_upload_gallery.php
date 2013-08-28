<?php
/**
 * Виджет для загрузки галереи изображений.
 * Выводит форму и список превью изображений
 *
 * @param sfDoctrineRecord $object
 * @param string    $routePrefix
 * @param ImageForm $form
 */

use_helper('JavascriptBase');

use_stylesheet('/sfMultipleImageUploadPlugin/css/style.css');
use_javascript('/sfMultipleImageUploadPlugin/js/jquery/jquery-ui.min.js');
use_javascript('/sfMultipleImageUploadPlugin/js/swfupload/swfupload.js');
use_javascript('/sfMultipleImageUploadPlugin/js/swfupload/swfupload.queue.js');

$types = sfConfig::get('app_sf_image_uploader_types');
$config = $types[$type];
$formClass = $config['image_model'] .'Form';
$imagesRelation = isset($config['images_relation']) ? $config['images_relation'] : 'Images';
?>

<?php include_partial('sfMultipleImageUpload/upload_init.js', array(
    'object' => $object,
    'routePrefix' => $type,
    'form' => new $formClass,
)); ?>

<div class="image_upload_gallery" id="image_upload_gallery_<?php echo $type; ?>">
    <div class="title">Загрузка изображений</div>

    <?php if ($object->isNew()): ?>
        <p>Загрузка изображений будет доступна после сохранения.</p>

    <?php else: ?>
        <div class="upload_button_cont" id="upload_button_cont_<?php echo $type; ?>">
            <div id="upload_button_<?php echo $type; ?>"></div>
            <div class="help">Для добавления щелкните кнопку.<br />Вы можете выбрать сразу несколько изображений. Форматы: JPG, PNG, GIF.</div>
        </div>

        <div class="upload_progress" id="upload_progress_<?php echo $type; ?>">
            Загрузка: <span class="count">0</span> (<span class="percents">0%</span>) из <span class="total">0</span>
            <a href="#" onclick="upload.cancelQueue(); return false;">Отмена</a>
        </div>
        <ul class="error_list" id="error_list_<?php echo $type; ?>">
        </ul>

        <div class="title">Добавленные изображения</div>

        <?php echo javascript_tag("
        $(function(){
            $('#uploaded_image_list_".$type."').sortable({
                update: function(e, ui) {
                    var serial = $('#uploaded_image_list_".$type."').sortable('serialize', { key: 'order[]' });

                    var options = {
                        url: '". url_for($type.'_image_sort', $object) ."',
                        type: 'POST',
                        data: serial
                    };
                    $.ajax(options);
                }
            });
        });
        "); ?>

        <ul class="uploaded_image_list" id="uploaded_image_list_<?php echo $type; ?>">
        <?php foreach ($object->{'get'.$imagesRelation}() as $image): ?>
            <?php include_partial('sfMultipleImageUpload/upload_preview', array(
                'image'   => $image,
                'routePrefix' => $type,
                'object'  => $object,
            )); ?>
        <?php endforeach; ?>
        </ul>

        <?php if ($sf_params->get('module') != 'sfImageTitle' && in_array('sfImageTitle', sfConfig::get('sf_enabled_modules'))): ?>
          <?php echo link_to('Редактировать названия изображений', $type.'_image_title', $object); ?>
        <?php endif; ?>

    <?php endif ?>
</div>
