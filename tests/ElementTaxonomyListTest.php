<?php

namespace NSWDPC\Elemental\Models\Taxonomy\Tests;

use NSWDPC\Elemental\Models\Taxonomy\ElementTaxonomyList;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\Taxonomy\TaxonomyType;

class ElementTaxonomyListTest extends SapphireTest
{
    /**
     * @inheritdoc
     */
    protected $usesDatabase = true;

    /**
     * @inheritdoc
     */
    protected static $fixture_file = './ElementTaxonomyListTest.yml';

    public function testSelectAllChildren(): void
    {
        $typeWithChildren = $this->objFromFixture(TaxonomyType::class, 'haschildren');
        $terms = TaxonomyTerm::get()->filter(['TypeID' => $typeWithChildren->ID]);
        $this->assertEquals(3, $terms->count());

        $element = ElementTaxonomyList::create([
            'TaxonomyTypeID' => $typeWithChildren->ID,
            'UseAllTerms' => 1
        ]);
        $element->write();
        $element->publishSingle();

        $selectedTerms = $element->getSelectedTerms();
        $this->assertEquals($terms->count(), $selectedTerms->count());
    }

    public function testSelectSomeChildren(): void
    {
        $typeWithChildren = $this->objFromFixture(TaxonomyType::class, 'haschildren');
        $child1 = $this->objFromFixture(TaxonomyTerm::class, 'child1');
        $child2 = $this->objFromFixture(TaxonomyTerm::class, 'child2');

        $element = ElementTaxonomyList::create([
            'TaxonomyTypeID' => $typeWithChildren->ID,
            'UseAllTerms' => 0
        ]);
        $element->write();
        $element->Terms()->add($child1);
        $element->Terms()->add($child2);
        $termsCount = $element->Terms()->count();
        $element->publishSingle();

        $selectedTerms = $element->getSelectedTerms();
        $this->assertEquals(2, $selectedTerms->count());
    }

    public function testTypeWithNoTerms(): void
    {
        $typeWithNoChildren = $this->objFromFixture(TaxonomyType::class, 'hasnochildren');
        $terms = TaxonomyTerm::get()->filter(['TypeID' => $typeWithNoChildren->ID]);
        $this->assertEquals(0, $terms->count());

        $element = ElementTaxonomyList::create([
            'TaxonomyTypeID' => $typeWithNoChildren->ID,
            'UseAllTerms' => 1
        ]);
        $element->write();
        $element->publishSingle();

        $selectedTerms = $element->getSelectedTerms();
        $this->assertEquals(0, $selectedTerms->count());
    }
}
