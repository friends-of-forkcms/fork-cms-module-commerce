<?php

namespace Frontend\Modules\Commerce;

use Frontend\Core\Engine\Base\Config as FrontendBaseConfig;

/**
 * This is the configuration-object for the Commerce module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Config extends FrontendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'Index';

    /**
     * The disabled actions
     *
     * @var array
     */
    protected $disabledActions = array();
}
