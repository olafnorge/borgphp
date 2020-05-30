<?php
namespace olafnorge\borgphp;

class ConfigCommand extends BorgExecutable {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [
        '-c' => null,
        '--cache' => null,
        '-d' => null,
        '--delete' => null,
        '-l' => null,
        '--list' => null,
    ];
}
