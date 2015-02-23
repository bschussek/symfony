<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

use Symfony\Component\Form\Serializer\FormSerializer;
use Symfony\Component\Form\Test\TypeTestCase as TestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

class IntegerTypeTest extends TestCase
{
    protected function setUp()
    {
        IntlTestHelper::requireIntl($this);

        parent::setUp();
    }

    public function testSubmitCastsToInteger()
    {
        $form = $this->factory->create('integer');

        $form->submit('1.678');

        $this->assertSame(1, $form->getData());
        $this->assertSame('1', $form->getViewData());
    }

    public function testSerialize()
    {
        $serializer = new FormSerializer($this->factory, $this->registry);
        $form = $this->factory->create('integer');

        $unserialized = $serializer->unserialize($serializer->serialize($form));

        $this->assertEquals($form, $unserialized);
    }
}
