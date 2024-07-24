<?php

namespace NSWDPC\Elemental\Models\Taxonomy;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Taxonomy\TaxonomyType;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Content block used to list taxonomy terms linked to a selected TaxonomyType
 *
 * @author Mark
 * @author James
 */
class ElementTaxonomyList extends BaseElement {

    /**
     * @inheritdoc
     */
    private static $table_name = 'ElementTaxonomyList';

    /**
     * @inheritdoc
     */
    private static $icon = 'font-icon-tags';

    /**
     * @inheritdoc
     */
    private static $inline_editable = true;

    /**
     * @inheritdoc
     */
    private static $singular_name = 'Taxonomy list';

    /**
     * @inheritdoc
     */
    private static $plural_name = 'Taxonomy lists';

    /**
     * @inheritdoc
     */
    private static $db = [
        'TermsSort' => 'Varchar(8)',
        'ShowTypeName' => 'Boolean',
        'UseAllTerms' => 'Boolean',
    ];

    /**
     * @inheritdoc
     */
    private static $has_one = [
        'TaxonomyType' => TaxonomyType::class,
    ];

    /**
     * Many_many relationship
     * @var array
     */
    private static $many_many = [
        'Terms' => TaxonomyTerm::class,
    ];

    /**
     * @inheritdoc
     */
    private static $defaults = [
        'UseAllTerms' => 1 // use all terms in the type
    ];

    /**
     * @inheritdoc
     */
    private static $title = 'Taxonomy list';

    /**
     * @inheritdoc
     */
    private static $description = 'Display a list of terms under a Taxonomy Type';

    /**
     * @var string
     */
    const TERMS_SORT_NAME = 'Name';

    /**
     * @var string
     */
    const TERMS_SORT_POSITION = 'Sort';//TaxonomyTerm.Sort

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Editable taxonomy term list');
    }

    /**
     * Apply requirements when templating
     */
    public function forTemplate($holder = true)
    {
        return parent::forTemplate($holder);
    }

    /**
     * @inheritdoc
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Main', [
                DropdownField::create(
                    'TaxonomyTypeID',
                    _t(__CLASS__ . '.TAXONOMY_TYPE', 'Select a taxonomy type'),
                    TaxonomyType::get()->sort('Name ASC')->map("ID","Name")
                )->setEmptyString(''),
                CheckboxField::create(
                    'UseAllTerms',
                    _t(__CLASS__ . '.SHOW_TYPE_NAME', 'Display all terms in this taxonomy type (overrides term selection)')
                ),
                CheckboxField::create(
                    'ShowTypeName',
                    _t(__CLASS__ . '.SHOW_TYPE_NAME', 'Display the taxonomy type name')
                ),
                OptionsetField::create(
                    'TermsSort',
                    _t(__CLASS__ . '.TERMS_SORT', 'Select a terms sort order'),
                    [ self::TERMS_SORT_NAME => 'Name', self::TERMS_SORT_POSITION => 'Position' ],
                    'Name'
                )
            ]
        );

        if ($this->exists()) {
            if (!$this->TaxonomyTypeID) {
                // no terms !
                $fields->removeByName('Terms');
            } else {
                $list = TaxonomyTerm::get()->filter('TypeID', $this->TaxonomyTypeID);
                $list = $list->sort('Name ASC');
                $fields->addFieldToTab(
                    'Root.Main',
                    CheckboxSetField::create(
                        'Terms',
                        _t(__CLASS__ . '.TERMS_SELECTION', 'Check terms to display (applied if \'Display all terms\' is unchecked)'),
                        $list->map("ID","TitleDescription")
                    )
                );
            }
        } else {
            $fields->removeByName('Terms');
        }
        return $fields;
    }

    /**
     * Part of schema.org support
     */
    public function DefinedTermSet() {
        return $this->getAnchor() . "-definedtermset";
    }

    /**
     * Event handler called after writing to the database.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $stage = Versioned::get_stage();
        if ($stage == Versioned::DRAFT) {
            // if the TYPE is no longer availble or changed, remove all selected terms
            $type = $this->TaxonomyType();
            $changed = $this->isChanged('TaxonomyTypeID', DataObject::CHANGE_VALUE);
            if ($changed || !$type || !$type->exists()) {
                $this->Terms()->removeAll();
            }
        }
    }

    /**
     * Get selected/sorted terms
     * @returns DataList|null
     */
    public function getSelectedTerms() : ?DataList {
        $type = $this->TaxonomyType();
        $terms = null;
        if($type) {
            $sort = $this->TermsSort;
            if($sort != self::TERMS_SORT_POSITION) {
                // ensure a sane sort
                $sort = self::TERMS_SORT_NAME;
            }
            // get all terms, sorted
            $terms = TaxonomyTerm::get()->filter([
                        'TypeID' => $type->ID
                    ])->sort($sort,"ASC");
            // filtered by selected Terms if set
            $selected_terms = $this->Terms()->column('ID');
            if (!$this->UseAllTerms) {
                if(!empty($selected_terms)) {
                    // use the selected terms
                    $terms = $terms->filter('ID', $selected_terms);
                } else {
                    // no terms selected!
                    return null;
                }
            }
        }
        return $terms;
    }

}
