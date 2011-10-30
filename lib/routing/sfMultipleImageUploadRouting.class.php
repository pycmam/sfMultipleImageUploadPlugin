<?php

/**
 * sfMultipleImageUploadRouting
 */
class sfMultipleImageUploadRouting
{
    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     * @static
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
    {
        $routing = $event->getSubject();
        $types = sfConfig::get('app_sf_image_uploader_types', array());

        foreach ($types as $name => $config) {

            // upload
            $routing->prependRoute($name .'_image_upload', new sfDoctrineRoute(sprintf('/%s/:id/images/upload', $name),
                array('module' => 'sfMultipleImageUpload', 'action' => 'upload', 'prefix' => $name),
                array('id' => '\d+', 'sf_method' => 'post'),
                array('model' => $config['object_model'], 'type' => 'object', 'image_model' => $config['image_model'])
            ));

            // delete
            $routing->prependRoute($name .'_image_delete', new sfDoctrineRoute(sprintf('/%s/image/:id/delete', $name),
                array('module' => 'sfMultipleImageUpload', 'action' => 'delete'),
                array('id' => '\d+', 'sf_method' => 'delete'),
                array('model' => $config['image_model'], 'type' => 'object', 'image_model' => $config['image_model'])
            ));

            // sort
            $routing->prependRoute($name .'_image_sort', new sfDoctrineRoute(sprintf('/%s/:id/images/sort', $name),
                array('module' => 'sfMultipleImageUpload', 'action' => 'sort'),
                array('id' => '\d+', 'sf_method' => 'post'),
                array('model' => $config['object_model'], 'type' => 'object', 'image_model' => $config['image_model'])
            ));

            // titles
            $routing->prependRoute($name .'_image_title', new sfDoctrineRoute(sprintf('/%s/:id/images/title', $name),
                array('module' => 'sfImageTitle', 'action' => 'index'),
                array('id' => '\d+', 'sf_method' => 'get'),
                array('model' => $config['object_model'], 'type' => 'object', 'config' => $name)
            ));

            // save titles
            $routing->prependRoute($name .'_image_title_save', new sfDoctrineRoute(sprintf('/%s/:id/images/title', $name),
                array('module' => 'sfImageTitle', 'action' => 'save'),
                array('id' => '\d+', 'sf_method' => 'post'),
                array('model' => $config['object_model'], 'type' => 'object', 'config' => $name)
            ));

        }
    }
}
