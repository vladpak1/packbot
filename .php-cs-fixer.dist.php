<?php

$finder = PhpCsFixer\Finder::create()
->ignoreDotFiles(false)
->ignoreVCS(true)
->in(__DIR__)
->exclude('vendor');

$config = new PhpCsFixer\Config();

$config
->setRules([
    '@PSR2'                               => true,
    'no_unused_imports'                   => true,
    'no_useless_else'                     => true,
    'no_useless_return'                   => true,
    'ordered_imports'                     => true,
    'phpdoc_align'                        => true,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_order'                        => true,
    'phpdoc_scalar'                       => true,
    'phpdoc_summary'                      => true,
    'phpdoc_types'                        => true,
    'concat_space'                        => [
        'spacing' => 'one',
    ],
    '@PSR12'                      => true,
    'single_quote'                => true,
    'trailing_comma_in_multiline' => true,
    'yoda_style'                  => true,
    'no_superfluous_phpdoc_tags'  => true,
    'no_empty_phpdoc'             => true,
    'no_empty_statement'          => true,
    'no_extra_blank_lines'        => true,
    'binary_operator_spaces'      => [
        'operators' => [
            '=>' => 'align_single_space_minimal',
            '='  => 'align_single_space_minimal',
        ],
    ],
    'align_multiline_comment'     => true,
    'class_attributes_separation' => [
        'elements' => [
            'method'   => 'one',
            'property' => 'one',
        ],
    ],
    'array_syntax' => [
        'syntax' => 'short',
    ],
    'blank_line_before_statement' => [
        'statements' => [
            'break',
            'continue',
            'declare',
            'do',
            'for',
            'foreach',
            'goto',
            'if',
            'return',
            'switch',
            'throw',
            'try',
            'while',
            'yield',
        ],
    ],
])
->setFinder($finder);

return $config;
