<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Renderer\Theme;

use Symfony\Component\Form\Renderer\Theme\PhpThemeFactory;

class PhpThemeFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    protected function setUp()
    {
        $this->factory = new PhpThemeFactory();
    }

    public function testCreate()
    {
        $theme = $this->factory->create();

        $this->assertInstanceOf('Symfony\Component\Form\Renderer\Theme\PhpTheme', $theme);
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testCreateDoesNotAcceptParams()
    {
        $this->factory->create('foobar');
    }
}