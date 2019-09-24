<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidationTypeExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (false === $options['browser_validation']) {
            $view->vars['attr']['novalidate'] = 'novalidate';
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('browser_validation', false);
        $resolver->setAllowedTypes('browser_validation', 'boolean');
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
