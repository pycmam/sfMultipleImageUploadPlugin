<?php

/**
 * PluginImage form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginImageForm extends BaseImageForm
{
    /**
     * SetUp
     */
    public function setup()
    {
        $this->setWidgets(array(
            'path' => new sfWidgetFormInputFile(),
        ));

        $this->setValidators(array(
            'path' => new sfValidatorFile(array(
                'mime_types' => 'web_images',
                'max_size' => sfConfig::get('app_sf_image_uploader_max_upload_size', 1024*1024*15),
            )),
        ));

        $this->widgetSchema->setNameFormat('image[%s]');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }


    /**
     * Все свойства изображения инициализируются здесь
     *
     * @param  string $field    - Название поля к которому прикреплен sfValidatorFile
     * @param  string $filename
     * @param  array  $values
     * @return string           - Возвращает пусть к изображению для предачи его в объект
     */
    protected function processUploadedFile($field, $filename = null, $values = null)
    {
        $image = $this->getObject();
        $file  = $this->getValue($field);

        $image->setType($file->getType());
        $image->setSize($file->getSize());

        return $this->saveUploadedFile($file);
    }


    /**
     * Сохранить загруженный файл
     *
     * @param sfValidatedFile $file
     */
    protected function saveUploadedFile(sfValidatedFile $file)
    {
        $filename = md5($this->getModelName() . microtime()) . $file->getExtension();

        $relative_dir = $this->getModelName() . DIRECTORY_SEPARATOR .
                        substr($filename, 0, 2) . DIRECTORY_SEPARATOR .
                        substr($filename, 2, 2);

        $this->checkDir($dir = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $relative_dir);

        if (! rename($file->getTempName(), $dir . DIRECTORY_SEPARATOR . $filename)) {
            copy($file->getTempName(), $dir . DIRECTORY_SEPARATOR . $filename);
        }

        return $relative_dir . DIRECTORY_SEPARATOR . $filename;
    }


    /**
     * Проверить существование директории и создать ее, если нет
     *
     * @param string $dir
     */
    protected function checkDir($dir)
    {
        if (! file_exists($dir)) {
            if (! mkdir($dir, 0777, true)) {
                throw new Exception(__METHOD__.": Failed to create directory `{$dir}`");
            }
        }
    }

}
