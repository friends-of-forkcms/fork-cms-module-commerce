<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

class ChangeStepException extends \Exception
{
    /**
     * @var Step
     */
    private $step;

    /**
     * @var Step
     */
    private $trigger;

    public function __construct(Step $step, Step $trigger)
    {
        $this->step = $step;
        $this->trigger = $trigger;
    }

    public function getTrigger(): Step
    {
        return $this->trigger;
    }

    public function getStep(): Step
    {
        return $this->step;
    }
}
