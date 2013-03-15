<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Intl\Tests\Globals\Verification;

use Symfony\Component\Intl\Tests\Globals\AbstractIntlGlobalsTest;

/**
 * Verifies that {@link AbstractIntlGlobalsTest} matches the behavior of the
 * intl functions with a specific version of ICU.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IntlGlobalsTest extends AbstractIntlGlobalsTest
{
    protected function setUp()
    {
        $this->skipIfIntlExtensionNotLoaded();
        $this->skipIfInsufficientIcuVersion();

        parent::setUp();
    }

    protected function getIntlErrorName($errorCode)
    {
        return intl_error_name($errorCode);
    }
}
