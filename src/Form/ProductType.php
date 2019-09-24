<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\Exception\ProductException;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class ProductType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('sku', SkuType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('price', PriceType::class);

        $builder->setDataMapper($this);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'removeSkuOnPreexistingProduct']);
    }

    public function removeSkuOnPreexistingProduct(PreSetDataEvent $event): void
    {
        /** @var Product $product */
        $product = $event->getData();
        $form = $event->getForm();

        if (null === $product) {
            return;
        }

        if (0 !== $product->getId()) {
            $form->remove('sku');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Product::class);
        $resolver->setDefault('empty_data', static function (FormInterface $form) {
            $sku = $form->get('sku')->getData();

            if (null === $sku) {
                return null;
            }

            try {
                return new Product(
                    $form->get('name')->getData(),
                    $sku,
                    $form->get('category')->getData(),
                    $form->get('price')->getData()
                );
            } catch (ProductException $exception) {
                $form->get('name')->addError(new FormError($exception->getMessage()));
            }
        });
    }

    /**
     * @param FormInterface[]|Traversable $forms
     */
    public function mapDataToForms($viewData, $forms): void
    {
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof Product) {
            throw new Exception\UnexpectedTypeException($viewData, Product::class);
        }

        $forms = iterator_to_array($forms);

        $forms['name']->setData($viewData->getName());
        $forms['category']->setData($viewData->getCategory());
        $forms['price']->setData($viewData->getPrice());

        if (array_key_exists('sku', $forms)) {
            $forms['sku']->setData($viewData->getSku());
        }
    }

    /**
     * @param FormInterface[]|Traversable $forms
     */
    public function mapFormsToData($forms, &$viewData): void
    {
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof Product) {
            throw new Exception\UnexpectedTypeException($viewData, Product::class);
        }

        $forms = iterator_to_array($forms);

        $price = $forms['price']->getData();
        if (!$viewData->getPrice()->equals($price)) {
            $viewData->costs($price);
        }

        $viewData->rename($forms['name']->getData());

        $category = $forms['category']->getData();
        if ($viewData->getCategory() !== $category) {
            $viewData->categorize($category);
        }
    }
}
