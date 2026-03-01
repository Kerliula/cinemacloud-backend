<?php

declare(strict_types=1);

use PhpCsFixer\Finder;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // Base standard
        '@PSR12' => true,
        'declare_strict_types' => true,

        // Imports
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
        ],

        // Arrays
        'array_syntax' => ['syntax' => 'short'],         // [] instead of array()
        'trailing_comma_in_multiline' => true,            // last item has comma
        'no_whitespace_before_comma_in_array' => true,

        // Strings
        'single_quote' => true,                           // 'hello' instead of "hello"
        'explicit_string_variable' => true,               // "{$var}" instead of "$var"

        // Functions
        'no_unreachable_default_argument_value' => true,
        'nullable_type_declaration_for_default_null_value' => true, // ?string for null defaults
        'void_return' => true,                            // add :void when nothing returned

        // Classes
        'ordered_class_elements' => [                     // consistent class structure
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'self_accessor' => true,                          // use self:: instead of ClassName::
        'no_null_property_initialization' => true,        // remove = null from properties

        // Code style
        'no_empty_comment' => true,
        'no_superfluous_phpdoc_tags' => true,             // remove useless @param @return
        'phpdoc_align' => ['align' => 'left'],
        'yoda_style' => false,                            // $x === 1 instead of 1 === $x

        // Modern PHP
        'modernize_types_casting' => true,                // (int) instead of intval()
        'is_null' => true,                                // $x === null instead of is_null($x)
        'no_alias_functions' => true,                     // use modern function names
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude(['vendor', 'node_modules', 'storage', 'bootstrap/cache'])
    );