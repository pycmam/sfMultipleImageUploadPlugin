<?php

/**
 * Базовый тестер мультизагрузки картинок
 */
abstract class sfMultipleImageUploadFunctionalTestCase extends myFunctionalTestCase
{
    /**
     * Тип
     */
    abstract protected function getTypeName();

    /**
     * Картинки для загрузки
     */
    abstract protected function getImages();

    /**
     * Объект-владелец для которого загружаются картинки
     */
    abstract protected function getObject();

    protected
        $images = null,
        $type = null,
        $config = null;


    protected function _start()
    {
        $this->images = $this->getImages();
        $this->type = $this->getTypeName();

        $types = sfConfig::get('app_sf_image_uploader_types');
        $this->config = $types[$this->type];
    }

    /**
     * Сгенерировать фикстуры
     */
    protected function _makeFixtures($object)
    {
        $fixtures = array();
        foreach ($this->images as $image) {
            $imageObject = new $this->config['image_model'];
            $imageObject->setObjectId($object->getId());
            $imageObject->setType($image['type']);
            $imageObject->setSize($image['size']);
            $imageObject->setPath($image['name']);
            $imageObject->save();

            $fixtures[] = $imageObject;
        }

        return $fixtures;
    }

    /**
     * Загрузка картинок
     */
    public function testAutoImageUpload()
    {
        $object = $this->getObject();
        $formClass = $this->config['image_model'] .'Form';

        $form = new $formClass;
        $url = $this->generateUrl($this->type .'_image_upload', $object);

        $this->browser
            ->uploadFiles(array($form->getName() => array('path' => $this->images[0])))
            ->post($url, array($form->getName() => array(
                $form->getCsrfFieldName() => $form->getCsrfToken(),
            )))
                ->with('request')->checkModuleAction('sfMultipleImageUpload', 'upload')
                ->with('form')->hasErrors(false)
                ->with('response')->isStatusCode(200)
                ->with('response')->checkElement('li .uploaded_preview img');

        $object->refresh();
        $object->refreshRelated('Images');

        $this->browser
            ->with('model')->check($this->config['image_model'], array(
                'object_id' => $object->getId(),
                'type'      => $this->images[0]['type'],
                'size'      => $this->images[0]['size'],
            ), 1);
    }


    /**
     * Удаление
     */
    public function testAutoImageDelete()
    {
        $object = $this->getObject();
        $images = $this->_makeFixtures($object);

        $this->browser
            ->with('model')->check($this->config['image_model'], array(
                'object_id' => $object->getId(),
            ), count($images));

        $this->browser
            ->setHttpHeader('X-Requested-With', 'XMLHttpRequest')
            ->call($this->generateUrl($this->type.'_image_delete', $images[0]), 'post', array('_with_csrf' => 1))
            ->with('response')->isStatusCode(200)
            ->with('model')->begin()
                ->check($this->config['image_model'], array(
                    'id' => $images[0]->getId(),
                ), 0)
                ->check($this->config['image_model'], array(
                    'object_id' => $object->getId(),
                ), count($images)-1)
            ->end();
    }


    /**
     * Сортировка
     */
    public function testAutoImageSort()
    {
        $object = $this->getObject();
        $images = $this->_makeFixtures($object);

        $plan = array();
        // 0 - сортируем в обратном порядке
        // 1 - возвращаем первоначальный порядок
        foreach ($images as $image) {
            $plan[1][] = $image->getId();
        }
        $plan[0] = array_reverse($plan[1]);

        foreach($plan as $params) {
            $this->browser
                ->post($this->generateUrl($this->type.'_image_sort', array(
                    'id' => $object->getId(),
                    'order' => $params,
                )))
                ->with('response')->isStatusCode(200);

            $object->refreshRelated('Images');

            $this->assertEquals($params, $object->getImages()->getPrimaryKeys());
        }
    }
}