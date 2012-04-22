<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class ObjectChoiceListTest_ObjectWithToString
{
    private $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function __toString()
    {
        return $this->property;
    }
}

class ChoiceListTest extends \PHPUnit_Framework_TestCase
{
    private $obj1;

    private $obj2;

    private $obj3;

    private $obj4;

    private $list;

    protected function setUp()
    {
        parent::setUp();

        $this->obj1 = (object) array('name' => 'Alpha', 'group' => 'Group 1', 'preferred' => false);
        $this->obj2 = (object) array('name' => 'Beta', 'group' => array('Group 1', 'Subgroup 1.1'), 'preferred' => true);
        $this->obj3 = (object) array('name' => 'Gamma', 'group' => 'Group 2', 'preferred' => false);
        $this->obj4 = (object) array('name' => 'Delta', 'group' => null, 'preferred' => false);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->obj1 = null;
        $this->obj2 = null;
        $this->obj3 = null;
        $this->obj4 = null;
        $this->list = null;
    }

    public function testInitArray()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D')
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'A'), 2 => new ChoiceView('2', 'C'), 3 => new ChoiceView('3', 'D')), $this->list->getRemainingViews());
    }

    public function testInitWitoutLabelsUseChoices()
    {
        $this->list = new ChoiceList(
            array('a', 'b', 'c', 'd'),
            array('b')
        );

        $this->assertSame(array('a', 'b', 'c', 'd'), $this->list->getChoices());
        $this->assertSame(array('a', 'b', 'c', 'd'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('b', 'b')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('a', 'a'), 2 => new ChoiceView('c', 'c'), 3 => new ChoiceView('d', 'd')), $this->list->getRemainingViews());
    }

    public function testInitWithoutLabelsCallsToString()
    {
        $obj1 = new ObjectChoiceListTest_ObjectWithToString('A');
        $obj2 = new ObjectChoiceListTest_ObjectWithToString('B');
        $obj3 = new ObjectChoiceListTest_ObjectWithToString('C');
        $obj4 = new ObjectChoiceListTest_ObjectWithToString('D');

        $this->list = new ChoiceList(
            array($obj1, $obj2, $obj3, $obj4),
            array($obj2)
        );

        $this->assertSame(array($obj1, $obj2, $obj3, $obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'A'), 2 => new ChoiceView('2', 'C'), 3 => new ChoiceView('3', 'D')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\StringCastException
     */
    public function testInitWithoutLabelsThrowsExceptionIfNoToString()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2)
        );
    }

    public function testInitLabelsCastedToStrings()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array(1, 2, 3, 4)
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', '2')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', '1'), 2 => new ChoiceView('2', '3'), 3 => new ChoiceView('3', '4')), $this->list->getRemainingViews());
    }

    public function testInitWithPreferredCallable()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            function ($choice) { return $choice->preferred; },
            array('A', 'B', 'C', 'D')
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'A'), 2 => new ChoiceView('2', 'C'), 3 => new ChoiceView('3', 'D')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithPreferredCallableReturnsNoBoolean()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            function ($choice) { return $choice->name; },
            array('A', 'B', 'C', 'D')
        );
    }

    public function testInitWithPreferredPath()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            'preferred',
            array('A', 'B', 'C', 'D')
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'A'), 2 => new ChoiceView('2', 'C'), 3 => new ChoiceView('3', 'D')), $this->list->getRemainingViews());
    }

    public function testInitWithNonExistingPreferredPathAssumesFalse()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            'foobar',
            array('A', 'B', 'C', 'D')
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'A'), 1 => new ChoiceView('1', 'B'), 2 => new ChoiceView('2', 'C'), 3 => new ChoiceView('3', 'D')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithPreferredPathNoObjects()
    {
        new ChoiceList(
            array('a', 'b', 'c', 'd'),
            'preferred',
            array('A', 'B', 'C', 'D')
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testInitWithInvalidPreferredChoices()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            1.346,
            array('A', 'B', 'C', 'D')
        );
    }

    public function testInitWithLabelCallable()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            function ($object) { return $object->name; }
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'Beta')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'Alpha'), 2 => new ChoiceView('2', 'Gamma'), 3 => new ChoiceView('3', 'Delta')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithLabelCallableReturnsNoString()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            function ($object) { return 1.23; }
        );
    }

    public function testInitWithLabelPath()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            'name'
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'Beta')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'Alpha'), 2 => new ChoiceView('2', 'Gamma'), 3 => new ChoiceView('3', 'Delta')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithLabelPathNoObjects()
    {
        new ChoiceList(
            array('a', 'b', 'c', 'd'),
            array('b'),
            'name'
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testInitWithInvalidLabels()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            1.346
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithInvalidLabelsStructureDiffers()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C')
        );
    }

    public function testInitWithValues()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            array('VA', 'VB', 'VC', 'VD')
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('VA', 'VB', 'VC', 'VD'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('VB', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('VA', 'A'), 2 => new ChoiceView('VC', 'C'), 3 => new ChoiceView('VD', 'D')), $this->list->getRemainingViews());
    }

    public function testInitWithValuesCastedToStrings()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            array(1, 2, 3, 4)
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('1', '2', '3', '4'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('2', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('1', 'A'), 2 => new ChoiceView('3', 'C'), 3 => new ChoiceView('4', 'D')), $this->list->getRemainingViews());
    }

    public function testInitWithValueCallable()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            function ($choice) { return 'V' . $choice->name; }
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('VAlpha', 'VBeta', 'VGamma', 'VDelta'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('VBeta', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('VAlpha', 'A'), 2 => new ChoiceView('VGamma', 'C'), 3 => new ChoiceView('VDelta', 'D')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithValueCallableReturnsNoString()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            function ($choice) { return 1.23; }
        );
    }

    public function testInitWithValuePath()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            'name'
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('Alpha', 'Beta', 'Gamma', 'Delta'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('Beta', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('Alpha', 'A'), 2 => new ChoiceView('Gamma', 'C'), 3 => new ChoiceView('Delta', 'D')), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithValuePathNoObjects()
    {
        new ChoiceList(
            array('a', 'b', 'c', 'd'),
            array('b'),
            array('A', 'B', 'C', 'D'),
            'name'
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testInitWithInvalidValues()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            1.346
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithInvalidValuesStructureDiffers()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            array('VA', 'VB', 'VC')
        );
    }

    public function testInitNestedArray()
    {
        $this->list = $this->getNestedList();

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(
            'Group 1' => array(1 => new ChoiceView('1', 'B')),
            'Group 2' => array(2 => new ChoiceView('2', 'C'))
        ), $this->list->getPreferredViews());
        $this->assertEquals(array(
            'Group 1' => array(0 => new ChoiceView('0', 'A')),
            'Group 2' => array(3 => new ChoiceView('3', 'D'))
        ), $this->list->getRemainingViews());
    }

    public function testInitWithGroupByCallable()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            // labels and values should be restructured as well
            array('A', 'B', 'C', 'D'),
            array('a', 'b', 'c', 'd'),
            function ($choice) { return $choice->group; }
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('a', 'b', 'c', 'd'), $this->list->getValues());
        $this->assertEquals(array(
            'Group 1' => array('Subgroup 1.1' => array(1 => new ChoiceView('b', 'B'))),
        ), $this->list->getPreferredViews());
        $this->assertEquals(array(
            'Group 1' => array(0 => new ChoiceView('a', 'A')),
            'Group 2' => array(2 => new ChoiceView('c', 'C')),
            3 => new ChoiceView('d', 'D')
        ), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithGroupByCallableReturnsNoString()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            null,
            function ($choice) { return 1.23; }
        );
    }

    public function testInitWithGroupPath()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            null,
            'group'
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(
            'Group 1' => array('Subgroup 1.1' => array(1 => new ChoiceView('1', 'B'))),
        ), $this->list->getPreferredViews());
        $this->assertEquals(array(
            'Group 1' => array(0 => new ChoiceView('0', 'A')),
            'Group 2' => array(2 => new ChoiceView('2', 'C')),
            3 => new ChoiceView('3', 'D')
        ), $this->list->getRemainingViews());
    }

    // see https://github.com/symfony/symfony/commit/d9b7abb7c7a0f28e0ce970afc5e305dce5dccddf
    public function testInitWithNonExistingGroupPathAssumesNoGroup()
    {
        $this->list = new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            null,
            'foobar'
        );

        $this->assertSame(array($this->obj1, $this->obj2, $this->obj3, $this->obj4), $this->list->getChoices());
        $this->assertSame(array('0', '1', '2', '3'), $this->list->getValues());
        $this->assertEquals(array(
            1 => new ChoiceView('1', 'B'),
        ), $this->list->getPreferredViews());
        $this->assertEquals(array(
            0 => new ChoiceView('0', 'A'),
            2 => new ChoiceView('2', 'C'),
            3 => new ChoiceView('3', 'D')
        ), $this->list->getRemainingViews());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInitWithGroupPathNoObjects()
    {
        $this->list = new ChoiceList(
            array('a', 'b', 'c', 'd'),
            array('b'),
            array('A', 'B', 'C', 'D'),
            null,
            'group'
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testInitWithInvalidGroupBy()
    {
        new ChoiceList(
            array($this->obj1, $this->obj2, $this->obj3, $this->obj4),
            array($this->obj2),
            array('A', 'B', 'C', 'D'),
            null,
            1.346
        );
    }

    public function testGetIndicesForChoices()
    {
        $this->list = $this->getNestedList();

        $choices = array($this->obj2, $this->obj3);
        $this->assertSame(array(1, 2), $this->list->getIndicesForChoices($choices));
    }

    public function testGetIndicesForChoicesIgnoresNonExistingChoices()
    {
        $this->list = $this->getNestedList();

        $choices = array($this->obj2, $this->obj3, 'foobar');
        $this->assertSame(array(1, 2), $this->list->getIndicesForChoices($choices));
    }

    public function testGetIndicesForValues()
    {
        $this->list = $this->getNestedList();

        // values and indices are always the same
        $values = array('1', '2');
        $this->assertSame(array(1, 2), $this->list->getIndicesForValues($values));
    }

    public function testGetIndicesForValuesIgnoresNonExistingValues()
    {
        $this->list = $this->getNestedList();

        $values = array('1', '2', '5');
        $this->assertSame(array(1, 2), $this->list->getIndicesForValues($values));
    }

    public function testGetChoicesForValues()
    {
        $this->list = $this->getNestedList();

        $values = array('1', '2');
        $this->assertSame(array($this->obj2, $this->obj3), $this->list->getChoicesForValues($values));
    }

    public function testGetChoicesForValuesCorrectOrderingOfResult()
    {
        $this->list = $this->getNestedList();

        $values = array('2', '1');
        $this->assertSame(array($this->obj3, $this->obj2), $this->list->getChoicesForValues($values));
    }

    public function testGetChoicesForValuesIgnoresNonExistingValues()
    {
        $this->list = $this->getNestedList();

        $values = array('1', '2', '5');
        $this->assertSame(array($this->obj2, $this->obj3), $this->list->getChoicesForValues($values));
    }

    public function testGetValuesForChoices()
    {
        $this->list = $this->getNestedList();

        $choices = array($this->obj2, $this->obj3);
        $this->assertSame(array('1', '2'), $this->list->getValuesForChoices($choices));
    }

    public function testGetValuesForChoicesIgnoresNonExistingChoices()
    {
        $this->list = $this->getNestedList();

        $choices = array($this->obj2, $this->obj3, 'foobar');
        $this->assertSame(array('1', '2'), $this->list->getValuesForChoices($choices));
    }

    protected function getNestedList()
    {
        return new ChoiceList(
                array(
                        'Group 1' => array($this->obj1, $this->obj2),
                        'Group 2' => array($this->obj3, $this->obj4),
                ),
                array($this->obj2, $this->obj3),
                array(
                        'Group 1' => array('A', 'B'),
                        'Group 2' => array('C', 'D'),
                )
        );
    }
}
