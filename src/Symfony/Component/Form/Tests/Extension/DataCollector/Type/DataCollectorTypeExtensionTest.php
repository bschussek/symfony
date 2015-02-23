<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\DataCollector\Type;

use Symfony\Component\Form\Extension\DataCollector\DataCollectorExtension;
use Symfony\Component\Form\Extension\DataCollector\FormDataCollectorInterface;
use Symfony\Component\Form\Serializer\FormSerializer;
use Symfony\Component\Form\Test\TypeTestCase;

class DataCollectorTypeExtensionTest extends TypeTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormDataCollectorInterface
     */
    private $dataCollector;

    public function setUp()
    {
        $this->dataCollector = $this->getMock('Symfony\Component\Form\Extension\DataCollector\FormDataCollectorInterface');

        parent::setUp();
    }

    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), [
            new DataCollectorExtension($this->dataCollector),
        ]);
    }

    public function testSerialize()
    {
        $serializer = new FormSerializer($this->factory, $this->registry);
        $form = $this->factory->create('form');

        $unserialized = $serializer->unserialize($serializer->serialize($form));

        $this->assertEquals($form, $unserialized);
    }
}
