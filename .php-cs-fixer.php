<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('tests')
    ->exclude('vendor')
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        'declare_strict_types' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'general_phpdoc_annotation_remove' => ['annotations' => ['author'], 'case_sensitive' => false],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
    ])
    ->setFinder($finder);
