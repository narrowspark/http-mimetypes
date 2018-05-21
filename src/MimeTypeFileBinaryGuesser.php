<?php
declare(strict_types=1);
namespace Narrowspark\MimeType;

use Narrowspark\MimeType\Contract\MimeTypeGuesser as MimeTypeGuesserContract;
use Narrowspark\MimeType\Exception\AccessDeniedException;
use Narrowspark\MimeType\Exception\FileNotFoundException;

class MimeTypeFileBinaryGuesser implements MimeTypeGuesserContract
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported(): bool
    {
        static $supported = null;

        if ($supported !== null) {
            return (bool) $supported;
        }

        if (DIRECTORY_SEPARATOR === '\\' || ! \function_exists('passthru') || ! \function_exists('escapeshellarg')) {
            return $supported = false;
        }

        \ob_start();
        \passthru('command -v file', $exitStatus);

        $binPath = \trim(\ob_get_clean());

        return $supported = $exitStatus === 0 && '' !== $binPath;
    }

    /**
     * Guesses the mime type with the binary "file".
     *
     * @param string $path
     * @param string $cmd  The command to run to get the mime type of a file.
     *                     The $cmd pattern must contain a "%s" string that will be replaced
     *                     with the file name to guess.
     *                     The command output must start with the mime type of the file.
     *                     Like: text/plain; charset=us-ascii
     *
     * @throws \Narrowspark\MimeType\Exception\AccessDeniedException If the file could not be read
     *
     * @return null|string
     */
    public static function guess(string $path, string $cmd = null): ?string
    {
        if (! \is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (! \is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        if ($cmd === null) {
            $cmd = 'file -b --mime %s';

            if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
                $cmd .= ' 2>/dev/null';
            }
        }

        \ob_start();

        // need to use --mime instead of -i.
        \passthru(\sprintf($cmd, \escapeshellarg($path)), $return);

        if ($return > 0) {
            \ob_end_clean();

            return null;
        }

        $type = \trim(\ob_get_clean());

        if (! \preg_match('#^([a-z0-9\-]+/[a-z0-9\-\.]+)#i', $type, $match)) {
            // it's not a type, but an error message
            return null;
        }

        return $match[1];
    }
}
