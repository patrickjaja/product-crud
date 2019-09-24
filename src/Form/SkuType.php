<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Exception\SkuException;
use App\Entity\Sku;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SkuType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof Sku) {
            throw new TransformationFailedException('Unexpected value, expected Sku');
        }

        return $value->toString();
    }

    public function reverseTransform($value): ?Sku
    {
        if (null === $value) {
            return null;
        }

        try {
            return new Sku($value);
        } catch (SkuException $exception) {
            throw new TransformationFailedException($exception->getMessage(), 0, $exception);
        }
    }
}
