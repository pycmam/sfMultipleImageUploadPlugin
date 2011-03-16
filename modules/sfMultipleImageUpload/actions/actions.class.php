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
        $object = $this->getRoute()->getObject();
        $relation = $object->getTable()->getRelation('Images');
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
            foreach ($form->getErrorSchema() as $name => $e) {
                echo $name, ":", $e->getMessage(), PHP_EOL;
            }

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
        $this->forward404Unless($request->isXmlHttpRequest());

        $image = $this->getRoute()->getObject();
        $image->delete();

        return sfView::HEADER_ONLY;
    }
}
