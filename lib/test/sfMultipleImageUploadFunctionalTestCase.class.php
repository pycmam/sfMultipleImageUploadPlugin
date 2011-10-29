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


    /**
     * Ссылка на галерею
     */
    abstract protected function getGalleryUrl($object);

    protected
        $images = null,
        $type = null,
        $config = null,
        $imagesRelation = null;


    protected function setUp()
    {
        parent::setUp();

        $this->type = $this->getTypeName();
        $types = sfConfig::get('app_sf_image_uploader_types');
        $this->config = $types[$this->type];

        $this->imagesRelation = isset($this->config['images_relation'])
          ? $this->config['images_relation']
          : 'Images';

        $this->images = $this->getImages();
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

        $params = sfConfig::get('sf_csrf_secret') ? array($form->getCsrfFieldName() => $form->getCsrfToken()) : array();
        $this->browser
            ->uploadFiles(array($form->getName() => array('path' => $this->images[0])))
            ->post($url, array($form->getName() => $params))
                ->with('request')->checkModuleAction('sfMultipleImageUpload', 'upload')
                ->with('form')->hasErrors(false)
                ->with('response')->isStatusCode(200)
                ->with('response')->checkElement('li .uploaded_preview img');

        $object->refresh();
        $object->refreshRelated($this->imagesRelation);

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
            ->call($this->generateUrl($this->type.'_image_delete', $images[0]), 'delete', array('_with_csrf' => 1))
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

            $object->refreshRelated($this->imagesRelation);

            $this->assertEquals($params, $object->{'get'.$this->imagesRelation}()->getPrimaryKeys());
        }
    }


    /**
     * Галерея
     */
    public function testAutoGallery()
    {
        $object = $this->getObject();
        $images = $this->_makeFixtures($object);

        $tester = $this->browser
            ->get($this->getGalleryUrl($object))
            ->with('response')->begin()
                ->checkElement('#image_upload_gallery_'.$this->type, true)
                ->checkElement('#uploaded_image_list_'.$this->type.' li', count($images));

        foreach ($images as $image) {
            $tester->checkElement('#uploaded_image_'.$this->type.'_'.$image->getId(), 1);
        }

        $tester->end();
    }


    protected function copyFileAndGetPath($source)
    {
        $filename = basename($source);
        $dir = sfConfig::get('sf_cache_dir') . DIRECTORY_SEPARATOR .
                       $this->app . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR .
                       'fixtures' . DIRECTORY_SEPARATOR . 'files';

        if (! file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        copy($source, $destination = $dir . DIRECTORY_SEPARATOR . $filename);

        return $destination;
    }
}
