<?php
namespace olafnorge\borgphp;

class CreateCommand extends BorgExecutable {

    /**
     * Optional options of the command
     *
     * @var array
     */
    protected static $commandOptions = [
        '-n' => null,
        '--dry-run' => null,
        '-s' => null,
        '--stats' => null,
        '--list' => null,
        '--filter' => '.*',
        '--json' => null,
        '--no-cache-sync' => null,
        '--no-files-cache' => null,
        '--stdin-name' => '.*',
        '--stdin-user' => '.*',
        '--stdin-group' => '.*',
        '--stdin-mode' => '0[0-7]{1}[0-7]{1}[0-7]{1}',
        '-e' => '.*',
        '--exclude' => '.*',
        '--exclude-from' => '.*',
        '--pattern' => '.*',
        '--patterns-from' => '.*',
        '--exclude-caches' => null,
        '--exclude-if-present' => '.*',
        '--keep-exclude-tags' => null,
        '--keep-tag-files' => null,
        '--exclude-nodump' => null,
        '-X' => null,
        '--one-file-system' => null,
        '--numeric-owner' => null,
        '--noatime' => null,
        '--noctime' => null,
        '--nobirthtime' => null,
        '--nobsdflags' => null,
        '--ignore-inode' => null,
        '--files-cache' => '.*',
        '--read-special' => null,
        '--comment' => '.*',
        '--timestamp' => '.*',
        '-c' => '[0-9]+',
        '--checkpoint-interval' => '[0-9]+',
        '--chunker-params' => '.*',
        '-C' => '.*',
        '--compression' => '.*',
    ];


    /**
     * Extracts paths from a create command
     *
     * @param array $command
     * @param string $archiveName
     * @return array
     */
    public static function getPathsFromCommand(array $command, string $archiveName): array {
        $options = array_merge(static::$commonOptions, static::$commandOptions);
        $length = count($command);

        for ($i = 0; $i < $length; $i++) {
            // not an option, not our repo with archive name but most likely a path
            if (self::isPossiblyPath($command[$i], $options, $archiveName)) {
                continue;
            }

            // remove option with parameters
            if (array_key_exists($command[$i], $options) && $options[$command[$i]] !== null) {
                // value of option is set via equals sign
                if (mb_strpos($options[$command[$i]], '=') !== false) {
                    unset($command[$i]);
                } else {
                    unset($command[$i], $command[++$i]);
                }
            } // remove ordinary option
            else {
                unset($command[$i]);
            }
        }

        return array_values(array_filter(array_map('trim', $command)));
    }


    /**
     * @param string $subject
     * @param array $options
     * @param string $archiveName
     * @return bool
     */
    private static function isPossiblyPath(string $subject, array $options, string $archiveName): bool {
        // borg executable, command name, or archive name found
        if (mb_strpos($subject, 'borg') !== false || mb_strpos($subject, static::getCommandName()) !== false || mb_strpos($subject, $archiveName) !== false) {
            return false;
        }

        // this is an option
        if (array_key_exists($subject, $options) || array_filter(array_keys($options), function ($item) use ($subject) {
                return mb_strpos($subject, $item) !== false;
            })
        ) {
            return false;
        }

        return true;
    }
}
