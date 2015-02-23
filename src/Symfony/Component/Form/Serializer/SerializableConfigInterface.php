<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Serializer;

use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;

/**
 * Marks form configurations that can be serialized and unserialized.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface SerializableConfigInterface extends FormConfigBuilderInterface, SerializableInterface
{
    /**
     *0Restores the state of the config after unserialization.
     *
     * @param FormFactoryInterface  $factory  The form factory.
     * @param FormRegistryInterface $registry The form type registry.
     */
    public function postUnserialize(FormFactoryInterface $factory, FormRegistryInterface $registry);
}
