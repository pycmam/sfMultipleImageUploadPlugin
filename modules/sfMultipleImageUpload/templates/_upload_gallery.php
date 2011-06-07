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
?>

<?php include_partial('sfMultipleImageUpload/upload_init.js', array(
    'object' => $object,
    'routePrefix' => $type,
    'form' => new $formClass,
)); ?>

<div id="image_upload_gallery">
    <?php if ($object->isNew()): ?>
        <p>Загрузка изображений будет доступна после сохранения.</p>

    <?php else: ?>
        <div id="upload_button_cont">
            <div id="upload_button"></div>
            <div class="help">Для добавления щелкните кнопку.<br />Вы можете выбрать сразу несколько изображений. Форматы: JPG, PNG, GIF.</div>
        </div>

        <div id="upload_progress">
            Загрузка: <span class="count">0</span> (<span class="percents">0%</span>) из <span class="total">0</span>
            <a href="#" onclick="upload.cancelQueue(); return false;">Отмена</a>
        </div>
        <ul id="error_list">
        </ul>

        <h2>Добавленные изображения</h2>

        <?php echo javascript_tag("
        $(function(){
            $('#uploaded_image_list').sortable({
                update: function(e, ui) {
                    var serial = $(e.target).sortable('serialize', { key: 'order[]' });
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

        <ul id="uploaded_image_list">
        <?php foreach ($object->getImages() as $image): ?>
            <?php include_partial('sfMultipleImageUpload/upload_preview', array(
                'image'   => $image,
                'routePrefix' => $type,
                'object'  => $object,
            )); ?>
        <?php endforeach; ?>
        </ul>
    <?php endif ?>
</div>
