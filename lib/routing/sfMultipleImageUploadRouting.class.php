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
            $routing->prependRoute($name .'_image_upload', new sfDoctrineRoute(sprintf('/%s/image/upload/:id', $name),
                array('module' => 'sfMultipleImageUpload', 'action' => 'upload', 'prefix' => $name),
                array('id' => '\d+', 'sf_method' => 'post'),
                array('model' => $config['object_model'], 'type' => 'object')
            ));

            // delete
            $routing->prependRoute($name .'_image_delete', new sfDoctrineRoute(sprintf('/%s/image/delete/:id', $name),
                array('module' => 'sfMultipleImageUpload', 'action' => 'delete'),
                array('id' => '\d+', 'sf_method' => 'post'),
                array('model' => $config['image_model'], 'type' => 'object')
            ));

            // sort
            $routing->prependRoute($name .'_image_sort', new sfDoctrineRoute(sprintf('/%s/image/sort/:id', $name),
                array('module' => 'sfMultipleImageUpload', 'action' => 'sort'),
                array('id' => '\d+', 'sf_method' => 'post'),
                array('model' => $config['object_model'], 'type' => 'object', 'image_model' => $config['image_model'])
            ));

        }
    }
}
