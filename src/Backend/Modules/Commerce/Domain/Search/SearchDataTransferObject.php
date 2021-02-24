<?php

namespace Backend\Modules\Commerce\Domain\Search;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class SearchDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $query;

    /**
     * Handle the set query which has been set.
     */
    public function __construct(Request $request)
    {
        $this->query = $request->query->get('query');
    }
}
