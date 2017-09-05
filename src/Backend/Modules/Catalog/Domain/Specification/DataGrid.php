<?php

namespace Backend\Modules\Catalog\Domain\Specification;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale)
    {
        parent::__construct(
            'SELECT c.id, c.title AS specification, c.type, c.sequence
					 FROM catalog_specifications AS c
					 WHERE c.language = :language
					 GROUP BY c.id
					 ORDER BY c.sequence ASC',
            ['language' => $locale]
        );

        // sequence
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(array('data-action' => 'SequenceSpecifications'));
        $this->setColumnFunction([self::class, 'typeName'], ['[type]'], 'type');

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditSpecification')) {
            $editUrl = Model::createUrlForAction('EditSpecification', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('specification', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }

    public static function typeName(int $type): string
    {
        $name = null;
        switch ($type) {
            default:
            case Specification::TYPE_TEXTBOX:
                $name = ucfirst(Language::lbl('TextBox'));
                break;
        }

        return $name;
    }
}