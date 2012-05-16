<?php

namespace Symfony\Component\Validator;

use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\MemberMetadata;
use Symfony\Component\Validator\MessageInterpolator\MessageInterpolatorInterface;

class GraphWalker
{
    protected $context;
    protected $validatorFactory;
    protected $metadataFactory;

    public function __construct($root, ClassMetadataFactoryInterface $metadataFactory, ConstraintValidatorFactoryInterface $factory, MessageInterpolatorInterface $messageInterpolator)
    {
        $this->context = new ValidationContext($root, $this, $metadataFactory, $messageInterpolator);
        $this->validatorFactory = $factory;
        $this->metadataFactory = $metadataFactory;
    }

    public function getViolations()
    {
        return $this->context->getViolations();
    }

    public function walkClass(ClassMetadata $metadata, $object, $group, $propertyPath)
    {
        $this->context->setCurrentClass($metadata->getClassName());

        foreach ($metadata->findConstraints($group) as $constraint) {
            $this->walkConstraint($constraint, $object, $group, $propertyPath);
        }

        if ($object !== null) {
            foreach ($metadata->getConstrainedProperties() as $property) {
                $localPropertyPath = empty($propertyPath) ? $property : $propertyPath.'.'.$property;

                $this->walkProperty($metadata, $property, $object, $group, $localPropertyPath);
            }
        }
    }

    public function walkProperty(ClassMetadata $metadata, $property, $object, $group, $propertyPath)
    {
        foreach ($metadata->getMemberMetadatas($property) as $member) {
            $this->walkMember($member, $member->getValue($object), $group, $propertyPath);
        }
    }

    public function walkPropertyValue(ClassMetadata $metadata, $property, $value, $group, $propertyPath)
    {
        foreach ($metadata->getMemberMetadatas($property) as $member) {
            $this->walkMember($member, $value, $group, $propertyPath);
        }
    }

    protected function walkMember(MemberMetadata $metadata, $value, $group, $propertyPath)
    {
        $this->context->setCurrentProperty($metadata->getPropertyName());

        foreach ($metadata->findConstraints($group) as $constraint) {
            $this->walkConstraint($constraint, $value, $group, $propertyPath);
        }
    }

    public function walkConstraint(Constraint $constraint, $value, $group, $propertyPath)
    {
        $validator = $this->validatorFactory->getInstance($constraint);

        $this->context->setPropertyPath($propertyPath);
        $this->context->setGroup($group);

        $validator->initialize($this->context);

        if (!$validator->isValid($value, $constraint)) {
            $this->context->addViolation(
                $validator->getMessageTemplate(),
                $validator->getMessageParameters(),
                $value
            );
        }
    }
}