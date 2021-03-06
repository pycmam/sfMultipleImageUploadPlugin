<?php
/**
 * Форма редактирования заголовков картинок
 *
 * @param ImageTitleBatchForm $form
 */
use_helper('Replica');
?>

<style type="text/css">
#sf_admin_container .sf_admin_form_row {
  overflow: hidden;
  }
#sf_admin_container .sf_admin_form_row .content {
  padding-left: 10em;
}
#sf_admin_container .sf_admin_form_row .content input[type=text] {
  width: 400px;
  }
</style>

<div id="sf_admin_container">
    <h1>Изображения для "<?php echo $form->getObject(); ?>"</h1>

    <?php include_partial('sfImageTitle/flashes'); ?>

    <div id="sf_admin_header">
        <?php include_partial('sfImageTitle/form_header', array(
            'object' => $form->getObject(),
            'conf' => $conf,
        )); ?>
    </div>

    <div id="sf_admin_content">
        <?php include_partial('sfMultipleImageUpload/upload_gallery', array(
            'object' => $form->getObject(),
            'type' => $conf,
        )); ?>

        <h1>Названия изображений</h1>

        <div class="sf_admin_form">
            <form action="<?php echo url_for($conf.'_image_title_save', $form->getObject()); ?>" method="post">
                <?php echo $form->renderHiddenFields(); ?>
                <fieldset id="sf_fieldset_none">
                <?php foreach ($form->getImages() as $id => $image): ?>
                    <div class="sf_admin_form_row sf_admin_foreignkey sf_admin_form_field_region_id">
                        <?php echo $form[$id]->renderError(); ?>
                        <div>
                            <label>
                                <?php echo thumbnail('preview_for_title', new myUploadProxy($image->getPath())); ?>
                            </label>
                            <div class="content">
                                <?php echo $form[$id]; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </fieldset>
                <ul class="sf_admin_actions">
                    <li class="sf_admin_action_save">
                        <input type="submit" value="Сохранить">
                    </li>
                </ul>
            </form>
        </div>
    </div>
</div>