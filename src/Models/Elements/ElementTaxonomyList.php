<?php

namespace NSWDPC\Elemental\Models\Taxonomy;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Taxonomy\TaxonomyType;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\Versioned\Versioned;

/**
 * Content block used to list taxonomy terms linked to a selected TaxonomyType
 *
 * @author Mark
 * @author James
 * @property ?string $TermsSort
 * @property bool $ShowTypeName
 * @property bool $UseAllTerms
 * @property int $TaxonomyTypeID
 * @method \SilverStripe\Taxonomy\TaxonomyType TaxonomyType()
 * @method \SilverStripe\ORM\ManyManyList<\SilverStripe\Taxonomy\TaxonomyTerm> Terms()
 */
class ElementTaxonomyList extends BaseElement
{
    /**
     * @inheritdoc
     */
    private static string $table_name = 'ElementTaxonomyList';

    /**
     * @inheritdoc
     */
    private static string $icon = 'font-icon-tags';

    /**
     * @inheritdoc
     */
    private static bool $inline_editable = true;

    /**
     * @inheritdoc
     */
    private static string $singular_name = 'Taxonomy list';

    /**
     * @inheritdoc
     */
    private static string $plural_name = 'Taxonomy lists';

    /**
     * @inheritdoc
     */
    private static array $db = [
        'TermsSort' => 'Varchar(8)',
        'ShowTypeName' => 'Boolean',
        'UseAllTerms' => 'Boolean',
    ];

    /**
     * @inheritdoc
     */
    private static array $has_one = [
        'TaxonomyType' => TaxonomyType::class,
    ];

    /**
     * Many_many relationship
     */
    private static array $many_many = [
        'Terms' => TaxonomyTerm::class,
    ];

    /**
     * @inheritdoc
     */
    private static array $defaults = [
        'UseAllTerms' => 1 // use all terms in the type
    ];

    /**
     * @inheritdoc
     */
    private static string $title = 'Taxonomy list';

    /**
     * @inheritdoc
     */
    private static string $class_description = 'Display a list of terms under a Taxonomy Type';

    /**
     * @var string
     */
    public const TERMS_SORT_NAME = 'Name';

    /**
     * @var string
     */
    public const TERMS_SORT_POSITION = 'Sort';//TaxonomyTerm.Sort

    /**
     * @inheritdoc
     */
    #[\Override]
    public function getType()
    {
        return _t(self::class . '.BlockType', 'Editable taxonomy term list');
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Main',
            [
                DropdownField::create(
                    'TaxonomyTypeID',
                    _t(self::class . '.TAXONOMY_TYPE', 'Select a taxonomy type'),
                    TaxonomyType::get()->sort('Name ASC')->map("ID", "Name")
                )->setEmptyString(''),
                CheckboxField::create(
                    'UseAllTerms',
                    _t(self::class . '.SHOW_TYPE_NAME', 'Display all terms in this taxonomy type (overrides term selection)')
                ),
                CheckboxField::create(
                    'ShowTypeName',
                    _t(self::class . '.SHOW_TYPE_NAME', 'Display the taxonomy type name')
                ),
                OptionsetField::create(
                    'TermsSort',
                    _t(self::class . '.TERMS_SORT', 'Select a terms sort order'),
                    [
                        self::TERMS_SORT_NAME => 'Name',
                        self::TERMS_SORT_POSITION => 'Position'
                    ],
                    'Name'
                )
            ]
        );

        if ($this->exists()) {
            if (!$this->TaxonomyTypeID) {
                // no terms !
                $fields->removeByName('Terms');
            } else {
                $list = TaxonomyTerm::get()->filter(['TypeID' => $this->TaxonomyTypeID]);
                $list = $list->sort(['Name' => 'ASC']);
                $fields->addFieldToTab(
                    'Root.Main',
                    CheckboxSetField::create(
                        'Terms',
                        _t(
                            self::class . '.TERMS_SELECTION',
                            "Check terms to display (applied if 'Display all terms' is unchecked)"
                        ),
                        $list->map("ID", "TitleDescription")
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
    public function DefinedTermSet(): string
    {
        return $this->getAnchor() . "-definedtermset";
    }

    /**
     * Event handler called after writing to the database.
     */
    #[\Override]
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $stage = Versioned::get_stage();
        if ($stage == Versioned::DRAFT) {
            // if the TYPE is no longer available or changed, remove all selected terms from the relation
            $type = $this->TaxonomyType();
            $changed = $this->isChanged('TaxonomyTypeID', DataObject::CHANGE_VALUE);
            if ($changed || !$type || !$type->exists()) {
                $this->Terms()->removeAll();
            }
        }
    }

    /**
     * Get selected/sorted terms
     */
    public function getSelectedTerms(): ?DataList
    {
        $type = $this->TaxonomyType();
        $terms = null;
        if ($type) {
            $sort = $this->TermsSort;
            if ($sort != self::TERMS_SORT_POSITION) {
                // ensure a sane sort
                $sort = self::TERMS_SORT_NAME;
            }

            // get all terms, sorted
            $terms = TaxonomyTerm::get()->filter([
                'TypeID' => $type->ID
            ])->sort([$sort => "ASC"]);
            // filtered by selected Terms if set
            $selectedTerms = $this->Terms()->column('ID');
            if (!$this->UseAllTerms) {
                if ($selectedTerms !== []) {
                    // use the selected terms
                    $terms = $terms->filter('ID', $selectedTerms);
                } else {
                    // no terms selected!
                    return null;
                }
            }
        }

        return $terms;
    }

}
