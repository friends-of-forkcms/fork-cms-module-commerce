<?php

namespace Backend\Modules\Commerce;

use Backend\Core\Engine\Base\Config as BackendBaseConfig;

/**
 * This is the configuration-object for the Commerce module.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Config extends BackendBaseConfig
{
    /**
     * The default action.
     */
    protected string $defaultAction = 'Index';

    /**
     * The disabled actions.
     */
    protected array $disabledActions = [];
}
