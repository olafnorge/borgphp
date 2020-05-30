<?php
namespace olafnorge\borgphp\Process\Exception;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class ProcessFailedException extends RuntimeException {

    private $process;


    public function __construct(Process $process) {
        if ($process->isSuccessful()) {
            throw new InvalidArgumentException('Expected a failed process, but the given process was successful.');
        }

        $error = sprintf('The command "%s" failed.' . "\n\nExit Code: %s(%s)\n\nWorking directory: %s",
            $process->getCommandLine(),
            $process->getExitCode(),
            $process->getExitCodeText(),
            $process->getWorkingDirectory()
        );

        // we expect json output
        $error .= sprintf("\n\nOutput:\n================\n%s\n\nError Output:\n================\n%s",
            is_array($process->getOutput()) ? json_encode($process->getOutput(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $process->getOutput(),
            is_array($process->getErrorOutput()) ? json_encode($process->getErrorOutput(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $process->getErrorOutput()
        );

        parent::__construct($error);
        $this->process = $process;
    }


    public function getProcess() {
        return $this->process;
    }
}
