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

/**
 * Executes logic after unserializing a form.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface SerializationListenerInterface
{
    /**
     * Restores the state of a form configuration after unserialization.
     *
     * @param FormConfigBuilderInterface $config The form configuration.
     */
    public function postUnserialize(FormConfigBuilderInterface $config);
}
