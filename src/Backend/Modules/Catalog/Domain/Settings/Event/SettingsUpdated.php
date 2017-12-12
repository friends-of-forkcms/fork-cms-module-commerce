<?php

namespace Backend\Modules\Catalog\Domain\Settings\Event;

final class SettingsUpdated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.settings.updated';
}
