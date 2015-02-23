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

/**
 * Marks serializable objects.
 *
 * This interface should be implemented by data transformers and event
 * subscribers that support serialization. Transformers/listeners that don't
 * support serialization must be restored manually after unserialization
 * by implementing {@link SerializationListenerInterface} in the form type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface SerializableInterface
{
}
