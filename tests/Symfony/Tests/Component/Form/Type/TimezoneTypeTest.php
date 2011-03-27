<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Type;

require_once __DIR__.'/TestCase.php';

use Symfony\Component\Form\TimezoneField;

class TimezoneTypeTest extends TestCase
{
    public function testTimezonesAreSelectable()
    {
        $this->markTestSkipped('Fix me');

        $form = $this->factory->create('timezone', 'timezone');
        $choices = $form->getRenderer()->getVar('choices');

        $this->assertArrayHasKey('Africa', $choices);
        $this->assertArrayHasKey('Africa/Kinshasa', $choices['Africa']);
        $this->assertEquals('Kinshasa', $choices['Africa']['Africa/Kinshasa']);

        $this->assertArrayHasKey('America', $choices);
        $this->assertArrayHasKey('America/New_York', $choices['America']);
        $this->assertEquals('New York', $choices['America']['America/New_York']);
    }
}
