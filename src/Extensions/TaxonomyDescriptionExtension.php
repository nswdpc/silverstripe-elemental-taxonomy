<?php

namespace NSWDPC\Elemental\Extensions\Taxonomy;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Taxonomy\TaxonomyTerm;

/**
 * Decorate {@link SilverStripe\Taxonomy\TaxonomyTerm} with Textarea to provide a description of the term
 * @author James
 */
class TaxonomyDescriptionExtension extends DataExtension {

    /**
     * @inheritdoc
     */
    private static $db = [
        'Description' => 'Text',
    ];

    /**
     * Return title with optional description suffixed
     */
    public function TitleDescription() {
        $title = $this->owner->Title;
        if($this->owner->Description) {
            $title .= " - " . $this->owner->Description;
        }
        return $title;
    }

    /**
     * @inheritdoc
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.Main',
            TextField::create(
                'Description',
                _t(
                    __CLASS__ . ".TAXONOMY_DESCRIPTON",
                    'A short description of the term'
                )
            )
        );
    }

}
