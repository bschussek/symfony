<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsParser\Options;

abstract class DoctrineType extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeDoctrineCollectionListener())
                ->prependClientTransformer(new CollectionToArrayTransformer())
            ;
        }
    }

    public function getDefaultOptions()
    {
        $registry = $this->registry;
        $type = $this;

        $loader = function (Options $options) use ($type, $registry) {
            if (null !== $options['query_builder']) {
                $manager = $registry->getManager($options['em']);

                return $type->getLoader($manager, $options['query_builder'], $options['class']);
            }

            return null;
        };

        // For BC until Symfony 2.3
        $choiceLabels = function (Options $options) {
            return $options['property'];
        };

        $choiceList = function (Options $options) use ($registry) {
            $manager = $registry->getManager($options['em']);

            return new EntityChoiceList(
                $manager,
                $options['class'],
                $options['choice_labels'],
                $options['loader'],
                $options['choices'],
                $options['group_by'],
                $options['preferred_choices']
            );
        };

        return array(
            'em'                => null,
            'class'             => null,
            'property'          => null,
            'query_builder'     => null,
            'loader'            => $loader,
            'choices'           => null,
            'choice_labels'     => $choiceLabels,
            'choice_list'       => $choiceList
        );
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param array $options
     * @return EntityLoaderInterface
     */
    abstract public function getLoader(ObjectManager $manager, $queryBuilder, $class);

    public function getParent(array $options)
    {
        return 'choice';
    }
}
