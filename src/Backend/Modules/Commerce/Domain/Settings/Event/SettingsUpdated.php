<?php

namespace Backend\Modules\Commerce\Domain\Settings\Event;

final class SettingsUpdated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.settings.updated';
}
