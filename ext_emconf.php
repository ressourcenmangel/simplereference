<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Simple Reference',
    'description' => 'Create a simple reference element.',
    'category' => 'plugin',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '10.4.16-11.5.99',
                ],
            'suggests' =>
                [],
            'conflicts' =>
                [
                    'gridelements' => '*',
                ],
        ],
    'autoload' =>
        [
            'psr-4' =>
                [
                    'Ressourcenmangel\\Simplereference\\' => 'Classes',
                ],
        ],
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => 1,
    'author' => 'Matthias Kappenberg',
    'author_email' => 'matthias.kappenberg@ressourcenmangel.de',
    'author_company' => 'Ressourcenmangel',
    'version' => '1.0.0',
    'clearcacheonload' => true,
];

