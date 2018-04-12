<?php

namespace Backend\Modules\Catalog\Domain\Search;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class SearchDataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $query;

    /**
     * Handle the set query which has been set
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->query = $request->query->get('query');
    }
}
