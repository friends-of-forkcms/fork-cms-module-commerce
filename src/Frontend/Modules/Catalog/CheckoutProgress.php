<?php

namespace Frontend\Modules\Catalog;

use Common\Exception\RedirectException;
use Frontend\Modules\Catalog\CheckoutStep\Step;

class CheckoutProgress
{
    /**
     * @var Step[]
     */
    private $steps = [];

    /**
     * @var array
     */
    private $urls = [];

    public function addStep(Step $step): CheckoutProgress
    {
        $previousStep = end($this->steps);
        if ($previousStep) {
            $step->setPreviousStep($previousStep);
            $this->steps[$previousStep->getIdentifier()]->setNextStep($step);
        }

        $step->setCheckoutProgress($this);

        $this->steps[$step->getIdentifier()] = $step;
        $this->urls[$step->getUrl()] = $step->getIdentifier();

        return $this;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getFirstStep(): ?Step
    {
        return reset($this->steps);
    }

    /**
     * @param $url
     * @return bool|Step
     */
    public function getStepByUrl($url)
    {
        if (!array_key_exists($url, $this->urls)) {
            return false;
        }

        return $this->steps[$this->urls[$url]];
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function getUrlByIdentifier($identifier)
    {
        return $this->steps[$identifier]->getUrl();
    }

    /**
     * @param Step $step
     */
    public function setCurrentStep(Step $step): void
    {
        $this->steps[$step::$stepIdentifier]->setCurrent(true);
    }
}
