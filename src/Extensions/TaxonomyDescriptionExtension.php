<?php

namespace NSWDPC\Elemental\Extensions\Taxonomy;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\Extension;

/**
 * Decorate {@link SilverStripe\Taxonomy\TaxonomyTerm} with Textarea to provide a description of the term
 * @author James
 * @property ?string $Description
 * @extends \SilverStripe\Core\Extension<(\SilverStripe\Taxonomy\TaxonomyTerm & static)>
 */
class TaxonomyDescriptionExtension extends Extension
{
    /**
     * @inheritdoc
     */
    private static array $db = [
        'Description' => 'Text',
    ];

    /**
     * Return title with optional description suffixed
     */
    public function TitleDescription()
    {
        $title = $this->getOwner()->Title;
        if ($this->getOwner()->Description) {
            $title .= " - " . $this->getOwner()->Description;
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
                    self::class . ".TAXONOMY_DESCRIPTON",
                    'A short description of the term'
                )
            )
        );
    }

}
