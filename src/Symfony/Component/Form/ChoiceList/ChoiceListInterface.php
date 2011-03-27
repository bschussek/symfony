<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\ChoiceList;

interface ChoiceListInterface
{
    function getLabel($choice);

    function getChoices();

    function getOtherChoices();

    function getPreferredChoices();

    function isChoiceGroup($choice);

    function isChoiceSelected($choice, $displayedData);
}