<?php

namespace GuzzleHttp\Stream;

use GuzzleHttp\Utils;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Lazily reads or writes to a file that is opened only after an IO operation
 * take place on the stream.
 */
class LazyOpenStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * @var string File to open
     */
    private $filename;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param string $filename File to lazily open
     * @param string $mode     fopen mode to use when opening the stream
     */
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }

    /**
     * Creates the underlying stream lazily when required.
     *
     * @return StreamInterface
     */
    protected function createStream()
    {
        return Stream::create(self::tryFopen($this->filename, $this->mode));
    }


    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode     Mode used to open the file
     *
     * @return resource
     *
     * @throws \RuntimeException if the file cannot be opened
     *
     * @internal
     */
    private static function tryFopen($filename, $mode)
    {
        $e = null;
        set_error_handler(static function () use ($filename, $mode, &$e) {
            $e = new \RuntimeException(sprintf('Unable to open %s using mode %s: %s', $filename, $mode, func_get_args()[1]));
        });

        $handle = fopen($filename, $mode);
        restore_error_handler();

        if ($e) {
            /** @var $e \RuntimeException */
            throw $e;
        }

        return $handle;
    }
}
