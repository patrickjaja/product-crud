<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\Exception\CategoryException;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class CategoryType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['empty_data' => '']);
        $builder->add('parent', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'placeholder' => 'no parent',
            'query_builder' => static function (EntityRepository $repository) use ($options) {
                if (!array_key_exists('data', $options)) {
                    return null;
                }

                $qb = $repository->createQueryBuilder('category');
                $qb
                    ->where('category != :category')
                    ->setParameter('category', $options['data']);

                return $qb;
            },
        ]);
        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Category::class);
        $resolver->setDefault('empty_data', static function (FormInterface $form) {
            try {
                return new Category(
                    $form->get('name')->getData(),
                    $form->get('parent')->getData()
                );
            } catch (CategoryException $exception) {
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

        if (!$viewData instanceof Category) {
            throw new UnexpectedTypeException($viewData, Category::class);
        }

        $forms = iterator_to_array($forms);

        $forms['name']->setData($viewData->getName());

        if ($viewData->hasParent()) {
            $forms['parent']->setData($viewData->getParent());
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

        if (!$viewData instanceof Category) {
            throw new UnexpectedTypeException($viewData, Category::class);
        }

        $forms = iterator_to_array($forms);

        try {
            $viewData->rename($forms['name']->getData());
        } catch (CategoryException $exception) {
            $forms['name']->addError(new FormError($exception->getMessage()));
        }

        if ($viewData->hasParent() && null === $forms['parent']->getData()) {
            $viewData->removeParent();
        } elseif (null !== $forms['parent']->getData()) {
            $viewData->moveTo($forms['parent']->getData());
        }
    }
}
