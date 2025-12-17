<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/ecs.php',
    ])
    ->withSkip([
        __DIR__ . '/src/Kernel.php',
    ])
    ->withPreparedSets(
        psr12: true,
        strict: true,
        common: true,
        cleanCode: true,
    )
    ->withPhpCsFixerSets(
        symfony: true,
        symfonyRisky: true,
    )
    ->withRules([
        // Import rules
        NoUnusedImportsFixer::class,
        OrderedImportsFixer::class,

        // Strict type rules
        IsNullFixer::class,
        DeclareStrictTypesFixer::class,
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ])
    ->withConfiguredRule(MultilineWhitespaceBeforeSemicolonsFixer::class, [
        'strategy' => 'new_line_for_chained_calls',
    ])
    ->withConfiguredRule(PhpUnitMethodCasingFixer::class, [
        'case' => 'snake_case',
    ])
    ->withParallel()
;
