<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('tests')
    ->exclude('vendor')
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
        'class_attributes_separation' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'line_ending' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => null,
        ],
        'general_phpdoc_annotation_remove' => [
            'annotations' => [
                'author'
            ],
        ],
    ])
    ->setFinder($finder);
