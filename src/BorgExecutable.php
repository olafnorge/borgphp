<?php
namespace olafnorge\borgphp;

use olafnorge\borgphp\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * @method validateMandatoryOptions(array $command)
 */
abstract class BorgExecutable extends Process {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [];

    /**
     * @var array
     */
    private $command = ['borg'];

    /**
     * @var array
     */
    private $stdoutBuffer = [];

    /**
     * @var array
     */
    private $stderrBuffer = [];

    /**
     * All Borg commands share these options
     *
     * @var array
     */
    protected static $commonOptions = [
        '-h' => null,
        '--help' => null,
        '--critical' => null,
        '--error' => null,
        '--warning' => null,
        '--info' => null,
        '-v' => null,
        '--verbose' => null,
        '--debug' => null,
        '--debug-topic' => '.*',
        '-p' => null,
        '--progress' => null,
        '--log-json' => null,
        '--lock-wait' => '[0-9]+',
        '--bypass-lock' => null,
        '--show-version' => null,
        '--show-rc' => null,
        '--umask' => '0[0-7]{1}[0-7]{1}[0-7]{1}',
        '--remote-path' => '.*',
        '--remote-ratelimit' => '[0-9]+',
        '--consider-part-files' => null,
        '--debug-profile' => '.*',
        '--rsh' => '.*',
    ];


    /**
     * Borg constructor.
     *
     * @param array $arguments list of arguments passed to borg
     * @param string|null $cwd
     * @param array|null $env
     * @param null $input
     * @param float|null $timeout
     */
    public function __construct(array $arguments = [], string $cwd = null, array $env = null, $input = null, ?float $timeout = null) {
        // ensure borg base dir is set to CWD
        if ($cwd && empty($env['BORG_BASE_DIR'])) {
            $env['BORG_BASE_DIR'] = $cwd;
        }

        // set proper locale
        if (empty($env['LANG'])) {
            $env['LANG'] = 'en_US.UTF-8';
        }

        parent::__construct($this->buildCommand($arguments), $cwd, $env, $input, $timeout);
    }


    /**
     * @param callable|null $callback
     * @param array $env
     * @return int 0 - success (logged as INFO)
     *             1 - warning (operation reached its normal end, but there were warnings – you should check the log, logged as WARNING)
     *             2 - error (like a fatal error, a local or remote exception, the operation did not reach its normal end, logged as ERROR)
     *             128+N - killed by signal N (e.g. 137 == kill -9)
     * @see https://borgbackup.readthedocs.io/en/stable/usage/general.html#return-codes
     *
     *
     */
    public function run(callable $callback = null, array $env = []): int {
        if (in_array('--json-lines', $this->command, true)) {
            $this->disableOutput();

            return parent::run(function ($type, $buffer) use ($callback) {
                if ($callback !== null) {
                    $callback($type, $buffer);
                }

                $this->runCallback($type, $buffer);
            }, $env);
        }

        return parent::run($callback, $env);
    }


    /**
     * @param callable|null $callback
     * @param array $env
     * @return Process
     */
    public function mustRun(callable $callback = null, array $env = []): self {
        $result = $this->run($callback, $env);

        // 0 - success (logged as INFO)
        // 1 - warning (operation reached its normal end, but there were warnings – you should check the log, logged as WARNING)
        if (0 !== $result && 1 !== $result) {
            throw new ProcessFailedException($this);
        }

        return $this;
    }


    /**
     * @param $type
     * @param $buffer
     */
    public function runCallback($type, $buffer): void {
        foreach (array_filter(array_map('trim', explode(PHP_EOL, $buffer))) as $line) {
            if (Process::OUT === $type) {
                $this->stdoutBuffer[] = json_decode($line, true) ?: $line;
            } else {
                $this->stderrBuffer[] = json_decode($line, true) ?: $line;
            }
        }
    }


    /**
     * @return array|null
     */
    public function getOutput(): ?array {
        return $this->isOutputDisabled()
            ? $this->stdoutBuffer
            : json_decode(parent::getOutput(), true);
    }


    /**
     * @return array|null
     */
    public function getErrorOutput(): ?array {
        if ($this->isOutputDisabled()) {
            return $this->stderrBuffer;
        }

        return array_map(function ($item) {
            return json_decode($item, true) ?: $item;
        }, array_values(array_filter(explode(PHP_EOL, parent::getErrorOutput()))));
    }


    /**
     * @param string $new
     * @return BorgExecutable
     */
    public function setMemoryLimit(string $new): self {
        $old = ini_get('memory_limit');

        if (ini_set('memory_limit', $new) === false) {
            throw new RuntimeException(sprintf('Failed to set memory limit from %s to %s', $old, $new));
        }

        return $this;
    }


    /**
     * @return array
     */
    public static function getCommonOptions(): array {
        return array_keys(self::$commonOptions);
    }


    /**
     * @return array
     */
    public static function getCommandOptions(): array {
        return array_keys(static::$commandOptions);
    }


    /**
     * @param array $arguments
     * @return array
     */
    private function buildCommand(array $arguments): array {
        // append common options of the borg executable
        foreach ($this->extractCommonOptions($arguments) as $commonOption) {
            $this->command[] = $commonOption;
        }

        // ensure logging as json
        if (!in_array('--log-json', $this->command, true)) {
            $this->command[] = '--log-json';
        }

        // generate command from class name
        $this->command[] = static::getCommandName();

        // append options of the command
        foreach ($this->extractCommandOptions($arguments) as $commandOption) {
            $this->command[] = $commandOption;
        }

        // ensure we receive json as command output
        if (in_array('--json', static::getCommandOptions(), true) && !array_filter($this->command, function ($item) {
            return mb_strpos($item, '--json') !== false;
        })) {
            $this->command[] = '--json';
        }

        // append arguments of the command
        foreach ($this->extractCommandArguments($arguments) as $commandArgument) {
            $this->command[] = $commandArgument;
        }

        // ensure mandatory options are set
        if (is_callable([$this, 'validateMandatoryOptions'])) {
            $this->validateMandatoryOptions($this->command);
        }

        return $this->command;
    }


    /**
     * @param array $arguments
     * @return array
     */
    private function extractCommonOptions(array &$arguments): array {
        return $this->extractOptions($arguments, self::$commonOptions);
    }


    /**
     * @param array $arguments
     * @return array
     */
    private function extractCommandOptions(array &$arguments): array {
        return $this->extractOptions($arguments, static::$commandOptions);
    }


    /**
     * @param array $arguments
     * @return array
     */
    private function extractCommandArguments(array &$arguments): array {
        $arguments = array_values(array_filter(array_map('trim', $arguments), function ($item) {
            return mb_strlen($item);
        }));
        $options = [];

        foreach ($arguments as $argument) {
            // not a command argument, skip it
            if (array_key_exists($argument, self::$commonOptions) || array_key_exists($argument, static::$commandOptions) || $argument === 'borg') {
                continue;
            }

            $options[] = $argument;
        }

        return $options;
    }


    /**
     * @param array $givenOptions
     * @param array $availableOptions
     * @return array
     */
    private function extractOptions(array &$givenOptions, array $availableOptions): array {
        $givenOptions = array_values(array_filter(array_map('trim', $givenOptions), function ($item) {
            return mb_strlen($item);
        }));
        $length = count($givenOptions);
        $options = [];

        for ($i = 0; $i < $length; $i++) {
            // not an available option, skip it
            if (!array_key_exists($givenOptions[$i], $availableOptions)) {
                continue;
            }

            // add option with parameters
            if ($availableOptions[$givenOptions[$i]] !== null) {
                if (array_key_exists($givenOptions[$i + 1], $availableOptions) || !preg_match(sprintf('/^%s$/', $availableOptions[$givenOptions[$i]]), $givenOptions[$i + 1])) {
                    throw new LogicException(sprintf(
                        'Option %s does not match expected parameter expression. Expecting %s but got %s.',
                        $givenOptions[$i],
                        $availableOptions[$givenOptions[$i]],
                        $givenOptions[$i + 1]
                    ));
                }

                $options[] = $givenOptions[$i];
                $options[] = $givenOptions[$i + 1];
                unset($givenOptions[$i], $givenOptions[++$i]);
            } else {
                $options[] = $givenOptions[$i];
                unset($givenOptions[$i]);
            }
        }

        return $options;
    }


    /**
     * Returns name of the command based on class name
     *
     * @return string
     */
    protected static function getCommandName(): string {
        return strtolower(
            ltrim(
                str_replace(
                    'Command',
                    '',
                    str_replace(
                        __NAMESPACE__,
                        '',
                        static::class
                    )
                ), '\\')
        );
    }
}
