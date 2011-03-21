<?php

/**
 * sfMultipleImageUploadPlugin configuration.
 */
class sfMultipleImageUploadPluginConfiguration extends sfPluginConfiguration
{
    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        if (sfConfig::get('app_sf_image_uploader_routes_register', true)) {
            $enabledModules = sfConfig::get('sf_enabled_modules', array());
            if (in_array('sfMultipleImageUpload', $enabledModules)) {
                $this->dispatcher->connect('routing.load_configuration', array('sfMultipleImageUploadRouting', 'listenToRoutingLoadConfigurationEvent'));
            }
        }
    }
}
