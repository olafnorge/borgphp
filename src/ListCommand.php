<?php
namespace olafnorge\borgphp;

class ListCommand extends BorgExecutable {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [
        '--short' => null,
        '--format' => '.*',
        '--list-format' => '.*',
        '--json' => null,
        '--json-lines' => null,
        '-P' => '.*',
        '--prefix' => '.*',
        '-a' => '.*',
        '--glob-archives' => '.*',
        '--sort-by' => '.*',
        '--first' => '[0-9]+',
        '--last' => '[0-9]+',
        '-e' => '.*',
        '--exclude' => '.*',
        '--exclude-from' => '.*',
        '--pattern' => '.*',
        '--patterns-from' => '.*',
    ];
}
