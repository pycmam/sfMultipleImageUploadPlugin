<?php

/**
 * Контроллер загрузки картинок
 */
class sfMultipleImageUploadActions extends sfActions
{
    /**
     * PreExecute
     */
    public function preExecute()
    {
        sfConfig::set('sf_web_debug', false);
    }

    /**
     * Загрузить изображение в галерею
     */
    public function executeUpload(sfWebRequest $request)
    {
        $prefix = $request->getParameter('prefix');
        $configs = sfConfig::get('app_sf_image_uploader_types');
        $config = $configs[$prefix];

        $imagesRelationName = isset($config['images_relation'])
          ? $config['images_relation']
          : 'Images';

        $object = $this->getRoute()->getObject();
        $relation = $object->getTable()->getRelation($imagesRelationName);
        $objectSetter = 'set' . get_class($object);

        $imageClass = $relation->getClass();
        $image = new $imageClass;
        $image->$objectSetter($object);

        $imageFormClass = $relation->getClass() . 'Form';
        $form = new $imageFormClass($image);

        $this->setVar('form', $form); // тестам
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

        // Сохранить изображение и вернуть его превью
        if ($form->isValid()) {
            $image = $form->save();

            return $this->renderPartial('upload_preview', array(
                'image'   => $image,
                'object'  => $object,
                'routePrefix' => $request->getParameter('prefix'),
                'display' => 'none', // CSS, чтобы JS плавно показал
            ));

        // Вернуть ошибку
        } else {
            $errors = '';
            foreach ($form->getErrorSchema() as $name => $e) {
                $errors .= $name. ": ". $e->getMessage(). PHP_EOL;
            }

            $this->getContext()->getLogger()->log($errors, sfLogger::INFO);

            $this->getResponse()->setStatusCode(400);
            return sfView::HEADER_ONLY;
        }
    }


    /**
     * Сортировка
     */
    public function executeSort(sfRequest $request)
    {
        $object = $this->getRoute()->getObject();
        $options = $this->getRoute()->getOptions();

        Doctrine::getTable($options['image_model'])
            ->setSortOrder($request->getParameter('order'), array(
                'object_id' => $object->getId(),
            ));

        return sfView::NONE;
    }


    /**
     * Удалить изображение
     */
    public function executeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();
        $this->forward404Unless($request->isXmlHttpRequest());

        $image = $this->getRoute()->getObject();
        $image->delete();

        return sfView::HEADER_ONLY;
    }
}
