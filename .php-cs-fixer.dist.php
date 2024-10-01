<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\FixerRunner\Runner;
use PhpCsFixer\Runner\Parallel\ParallelConfig;

$finder = Finder::create()
    ->exclude(['wiki', 'documentation', '.php-cs-fixer.dist.php', '.php-cs-fixer.cache'])
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setRules([
        '@Symfony'                            => true,
        // Override @Symfony rules
        'increment_style'                     => ['style' => 'post'],
        'blank_line_before_statement'         => ['statements' => ['return', 'try', 'throw']],
        'phpdoc_align'                        => ['tags' => ['param', 'throws']],
        'method_argument_space'               => ['on_multiline' => 'ensure_fully_multiline', 'keep_multiple_spaces_after_comma' => false],
        'binary_operator_spaces'              => [
            'operators' => [
                '=>' => 'align',
                '='  => 'single_space',
            ],
        ],
        'phpdoc_annotation_without_dot'       => false,
        'no_superfluous_phpdoc_tags'          => false,
        'single_line_throw'                   => false,
        'yoda_style'                          => [
            'equal'            => false,
            'identical'        => false,
            'less_and_greater' => false,
        ],
        'is_null'                             => true,
        // Prevent removing leading backslash for class names
        'global_namespace_import'             => [
            'import_classes' => false,  // Do not remove leading backslash for class names
            'import_constants' => false,
            'import_functions' => false,
        ],
        // Custom rules
        'align_multiline_comment'             => true,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'ordered_imports'                     => ['sort_algorithm' => 'alpha'],
        'phpdoc_order'                        => true,
        'array_syntax'                        => ['syntax' => 'short'],
    ])
    ->setParallelConfig(new ParallelConfig(10, 120, 3600));
