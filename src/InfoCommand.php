<?php
namespace olafnorge\borgphp;

class InfoCommand extends BorgExecutable {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [
        '--json' => null,
        '-P' => '.*',
        '--prefix' => '.*',
        '-a' => '.*',
        '--glob-archives' => '.*',
        '--sort-by' => '.*',
        '--first' => '[0-9]+',
        '--last' => '[0-9]+',
    ];
}
