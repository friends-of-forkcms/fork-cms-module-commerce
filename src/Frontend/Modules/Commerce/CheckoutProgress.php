<?php

namespace Frontend\Modules\Commerce;

use Frontend\Modules\Commerce\CheckoutStep\Step;

class CheckoutProgress
{
    /** @var array<int, Step> */
    private array $steps = [];
    /** @var array<string, string> */
    private array $urls = [];

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
     * @return bool|Step
     */
    public function getStepByUrl(string $url)
    {
        if (!array_key_exists($url, $this->urls)) {
            return false;
        }

        return $this->steps[$this->urls[$url]];
    }

    public function getUrlByIdentifier(string $identifier): string
    {
        return $this->steps[$identifier]->getUrl();
    }

    public function setCurrentStep(Step $step): void
    {
        $this->steps[$step::$stepIdentifier]->setCurrent(true);
    }
}
