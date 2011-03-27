<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Validator;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Validator\DelegatingValidator;
use Symfony\Component\Form\DataTransformer\TransformationFailedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ExecutionContext;

class DelegatingValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $dispatcher;

    private $builder;

    private $delegate;

    private $validator;

    private $message;

    private $params;

    protected function setUp()
    {
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->delegate = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $this->validator = new DelegatingValidator($this->delegate);
        $this->message = 'Message';
        $this->params = array('foo' => 'bar');
    }

    protected function getMockGraphWalker()
    {
        return $this->getMockBuilder('Symfony\Component\Validator\GraphWalker')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getMockMetadataFactory()
    {
        return $this->getMock('Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface');
    }

    protected function getMockTransformer()
    {
        return $this->getMock('Symfony\Component\Form\DataTransformer\DataTransformerInterface', array(), array(), '', false, false);
    }

    protected function getConstraintViolation($propertyPath)
    {
        return new ConstraintViolation($this->message, $this->params, null, $propertyPath, null);
    }

    protected function getFormError()
    {
        return new FormError($this->message, $this->params);
    }

    protected function getBuilder($name, $propertyPath = null)
    {
        $builder = new FormBuilder($this->dispatcher);
        $builder->setName($name);
        $builder->setAttribute('property_path', new PropertyPath($propertyPath ?: $name));
        $builder->setAttribute('error_mapping', array());

        return $builder;
    }

    protected function getForm($name, $propertyPath = null)
    {
        return $this->getBuilder($name, $propertyPath)->getForm('author');
    }

    public function testFormErrorsOnForm()
    {
        $form = $this->getForm('author');

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('constrainedProp')
            )));

        $this->validator->validate($form);

        $this->assertEquals(array($this->getFormError()), $form->getErrors());
    }

    public function testFormErrorsOnChild()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('firstName');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[firstName].constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
    }

    public function testFormErrorsOnChildLongPropertyPath()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('street', 'address.street');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[address].data.street.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
    }

    public function testFormErrorsOnGrandChild()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('address');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[address].children[street].constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testFormErrorsOnChildWithChildren()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('address');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[address].constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
        $this->assertFalse($grandChild->hasErrors());
    }

    public function testFormErrorsOnParentIfNoChildFound()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('firstName');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[lastName].constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertEquals(array($this->getFormError()), $parent->getErrors());
        $this->assertFalse($child->hasErrors());
    }

    public function testDataErrorsOnForm()
    {
        $form = $this->getForm('author');

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.constrainedProp')
            )));

        $this->validator->validate($form);

        $this->assertEquals(array($this->getFormError()), $form->getErrors());
    }

    public function testDataErrorsOnChild()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('firstName');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.firstName.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
    }

    public function testDataErrorsOnChildLongPropertyPath()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('street', 'address.street');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.address.street.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
    }

    public function testDataErrorsOnChildWithChildren()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('address');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.address.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
        $this->assertFalse($grandChild->hasErrors());
    }

    public function testDataErrorsOnGrandChild()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('address');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.address.street.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testDataErrorsOnGrandChild2()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('address');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[address].data.street.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testDataErrorsOnParentIfNoChildFound()
    {
        $parent = $this->getForm('author');
        $child = $this->getForm('firstName');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.lastName.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertEquals(array($this->getFormError()), $parent->getErrors());
        $this->assertFalse($child->hasErrors());
    }

    public function testMappedError()
    {
        $parent = $this->getBuilder('author')
            ->setAttribute('error_mapping', array(
                'passwordPlain' => 'password',
            ))
            ->getForm('author');
        $child = $this->getForm('password');

        $parent->add($child);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.passwordPlain.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertEquals(array($this->getFormError()), $child->getErrors());
    }

    public function testMappedNestedError()
    {
        $parent = $this->getBuilder('author')
            ->setAttribute('error_mapping', array(
                'address.streetName' => 'address.street',
            ))
            ->getForm('author');
        $child = $this->getForm('address');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.address.streetName.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testNestedMappingUsingForm()
    {
        $parent = $this->getForm('author');
        $child = $this->getBuilder('address')
            ->setAttribute('error_mapping', array(
                'streetName' => 'street',
            ))
            ->getForm('author');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('children[address].data.streetName.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testNestedMappingUsingData()
    {
        $parent = $this->getForm('author');
        $child = $this->getBuilder('address')
            ->setAttribute('error_mapping', array(
                'streetName' => 'street',
            ))
            ->getForm('author');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.address.streetName.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testNestedMappingVirtualForm()
    {
        $parent = $this->getBuilder('author')
            ->setAttribute('error_mapping', array(
                'streetName' => 'street',
            ))
            ->getForm('author');
        $child = $this->getBuilder('address')
            ->setAttribute('virtual', true)
            ->getForm('author');
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->delegate->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(array(
                $this->getConstraintViolation('data.streetName.constrainedProp')
            )));

        $this->validator->validate($parent);

        $this->assertFalse($parent->hasErrors());
        $this->assertFalse($child->hasErrors());
        $this->assertEquals(array($this->getFormError()), $grandChild->getErrors());
    }

    public function testValidateFormData()
    {
        $graphWalker = $this->getMockGraphWalker();
        $metadataFactory = $this->getMockMetadataFactory();
        $context = new ExecutionContext('Root', $graphWalker, $metadataFactory);
        $object = $this->getMock('\stdClass');
        $form = $this->getBuilder('author')
            ->setAttribute('validation_groups', array('group1', 'group2'))
            ->getForm('author');

        $graphWalker->expects($this->at(0))
            ->method('walkReference')
            ->with($object, 'group1', 'data', true);
        $graphWalker->expects($this->at(1))
            ->method('walkReference')
            ->with($object, 'group2', 'data', true);

        $form->setData($object);

        DelegatingValidator::validateFormData($form, $context);
    }

    public function testValidateFormDataAppendsPropertyPath()
    {
        $graphWalker = $this->getMockGraphWalker();
        $metadataFactory = $this->getMockMetadataFactory();
        $context = new ExecutionContext('Root', $graphWalker, $metadataFactory);
        $context->setPropertyPath('path');
        $object = $this->getMock('\stdClass');
        $form = $this->getForm('author');

        $graphWalker->expects($this->once())
            ->method('walkReference')
            ->with($object, 'Default', 'path.data', true);

        $form->setData($object);

        DelegatingValidator::validateFormData($form, $context);
    }

    public function testValidateFormDataSetsCurrentPropertyToData()
    {
        $graphWalker = $this->getMockGraphWalker();
        $metadataFactory = $this->getMockMetadataFactory();
        $context = new ExecutionContext('Root', $graphWalker, $metadataFactory);
        $object = $this->getMock('\stdClass');
        $form = $this->getForm('author');
        $test = $this;

        $graphWalker->expects($this->once())
            ->method('walkReference')
            ->will($this->returnCallback(function () use ($context, $test) {
                $test->assertEquals('data', $context->getCurrentProperty());
            }));

        $form->setData($object);

        DelegatingValidator::validateFormData($form, $context);
    }

    public function testValidateFormDataDoesNotWalkScalars()
    {
        $graphWalker = $this->getMockGraphWalker();
        $metadataFactory = $this->getMockMetadataFactory();
        $context = new ExecutionContext('Root', $graphWalker, $metadataFactory);
        $clientTransformer = $this->getMockTransformer();

        $form = $this->getBuilder('author')
            ->setClientTransformer($clientTransformer)
            ->getForm('author');

        $graphWalker->expects($this->never())
            ->method('walkReference');

        $clientTransformer->expects($this->atLeastOnce())
            ->method('reverseTransform')
            ->will($this->returnValue('foobar'));

        $form->bind(array('foo' => 'bar')); // reverse transformed to "foobar"

        DelegatingValidator::validateFormData($form, $context);
    }
}