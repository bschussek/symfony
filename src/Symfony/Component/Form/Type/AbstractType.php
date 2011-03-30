<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Renderer\ThemeRendererInterface;

abstract class AbstractType implements FormTypeInterface
{
    public function buildForm(FormBuilder $builder, array $options)
    {
    }

    public function buildRenderer(ThemeRendererInterface $renderer, FormInterface $form)
    {
    }

    public function buildRendererBottomUp(ThemeRendererInterface $renderer, FormInterface $form)
    {
    }

    public function createBuilder(array $options)
    {
        return null;
    }

    public function getDefaultOptions(array $options)
    {
        return array();
    }

    public function getParent(array $options)
    {
        return 'form';
    }

    /**
     * Is used to determain the block used for rendering.
     *
     * @return string
     */
    public function getName()
    {
        return current(array_reverse(explode('\\', get_class($this))));
    }
}
