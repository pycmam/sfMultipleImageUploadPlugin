<?php

/**
 * Форма редактирования заголовков изображений
 */
class sfImageTitleBatchForm extends sfForm
{
    protected
        $object = null,
        $images = null;

    public function __construct(Doctrine_Record $object, $options = array(), $CSRFSecret = null)
    {
        $this->object = $object;

        $images = $object->getImages();
        $this->images = myToolkit::arrayGroup($images, 'id', false, false);
        $defaults = myToolkit::arrayGroup($images, 'id', 'title', false);

        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function setup()
    {
        foreach (array_keys($this->images) as $id) {
            $this->widgetSchema[$id] = new sfWidgetFormInput();
            $this->validatorSchema[$id] = new sfValidatorString(array(
                'required' => false,
                'max_length' => 255,
            ));
        }

        $this->validatorSchema->setOption('allow_extra_fields', true);
        $this->widgetSchema->setNameFormat('sf_image_title[%s]');
    }

    public function save()
    {
        foreach ($this->values as $id => $title) {
            if (isset($this->images[$id])) {
                $this->images[$id]->setTitle($title);
                $this->images[$id]->save();
            }
        }
    }

    /**
     * @return Doctrine_Record
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }
}