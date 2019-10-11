<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('tests')
    ->exclude('vendor');

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
        'class_attributes_separation' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'line_ending' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);