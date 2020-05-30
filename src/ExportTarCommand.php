<?php
namespace olafnorge\borgphp;

class ExportTarCommand extends BorgExecutable {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [
        '--tar-filter' => '.*',
        '--list' => '.*',
        '-e' => '.*',
        '--exclude' => '.*',
        '--exclude-from' => '.*',
        '--pattern' => '.*',
        '--patterns-from' => '.*',
        '--strip-components' => '[0-9]+',
    ];


    /**
     * {@inheritDoc}
     */
    protected static function getCommandName(): string {
        return 'export-tar';
    }
}
