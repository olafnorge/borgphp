<?php
namespace olafnorge\borgphp;

use Symfony\Component\Process\Exception\LogicException;

class InitCommand extends BorgExecutable {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [
        '-e' => '.*',
        '--encryption' => '.*',
        '--append-only' => null,
        '--storage-quota' => '.*',
        '--make-parent-dirs' => null,
    ];


    /**
     * @param array $command
     */
    protected function validateMandatoryOptions(array $command): void {
        if (!in_array('-e', $command, true) && !in_array('--encryption', $command, true)) {
            throw new LogicException(sprintf(
                'Neither \'%s\' nor \'%s\' are passed to the command.',
                '-e MODE',
                '--encryption MODE'
            ));
        }
    }
}
