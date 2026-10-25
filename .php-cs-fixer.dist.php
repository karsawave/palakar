<?php

declare(strict_types=1);

// PER Coding Style 2.0 only; strict_types stays manual because some
// pass-through files must not be strict (see CLAUDE.md).
$finder = (new PhpCsFixer\Finder())
    ->in(['panada', 'app', 'tests', 'public']);

return (new PhpCsFixer\Config())
    ->setRules(['@PER-CS2.0' => true])
    ->setFinder($finder);
