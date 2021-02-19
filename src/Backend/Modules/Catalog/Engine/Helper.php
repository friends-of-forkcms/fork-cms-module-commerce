<?php

namespace Backend\Modules\Catalog\Engine;

/**
 * In this file we store all generic functions that we will be using in the catalog module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Helper
{

    /**
     * Get the modules that have a slideshows hook
     *
     * @return array
     */
    public static function getModules()
    {
        return BackendModel::getModuleSetting('slideshows', 'modules');
    }
}
