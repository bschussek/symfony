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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;

/**
 * Serializes and unserializes form trees.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormSerializer
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var FormRegistryInterface
     */
    private $registry;

    /**
     * Creates the serializer.
     *
     * @param FormFactoryInterface  $factory  The form factory.
     * @param FormRegistryInterface $registry The form type registry.
     */
    public function __construct(FormFactoryInterface $factory, FormRegistryInterface $registry)
    {
        $this->factory = $factory;
        $this->registry = $registry;
    }

    /**
     * Returns the serialized representation of a form.
     *
     * @param FormInterface $form The form to serialize.
     *
     * @return string The serialized form.
     */
    public function serialize(FormInterface $form)
    {
        return serialize($form);
    }

    /**
     * Unserializes a serialized form.
     *
     * @param string $string The serialized form.
     *
     * @return FormInterface The unserialized form.
     */
    public function unserialize($string)
    {
        $form = unserialize($string);

        $this->postUnserialize($form);

        return $form;
    }

    private function postUnserialize(FormInterface $form)
    {
        $config = $form->getConfig();

        // Inject factory and type into the config
        if ($config instanceof SerializableConfigInterface) {
            $config->postUnserialize($this->factory, $this->registry);
        }

        $type = $config->getType();

        // Let the type inject other services
        if ($type instanceof SerializationListenerInterface && $config instanceof FormConfigBuilderInterface) {
            $type->postUnserialize($config);
        }

        // Do the same for the whole form tree
        foreach ($form as $child) {
            $this->postUnserialize($child);
        }
    }
}
