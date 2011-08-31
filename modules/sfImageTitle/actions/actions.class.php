<?php

/**
 * Редактирование заголовков изображений
 */
class sfImageTitleActions extends sfActions
{
    /**
     * Форма
     */
    public function executeIndex(sfWebRequest $request)
    {
        $object = $this->getRoute()->getObject();
        $this->form = new sfImageTitleBatchForm($object);
        $this->conf = $request->getParameter('conf');
    }

    /**
     * Сохранение
     */
    public function executeSave(sfWebRequest $request)
    {
        $this->executeIndex($request);

        $this->form->bind($request->getParameter($this->form->getName()));
        if ($this->form->isValid()) {
            $this->form->save();

            $this->getUser()->setFlash('notice', 'Заголовки изображений успешно сохранены.');
            return $this->redirect($this->conf.'_image_title', $this->form->getObject());
        }

        $this->getUser()->getFlash('error', 'Заголовки изображений не сохранены из-за ошибок.');
        $this->setTemplate('index');
    }
}