<?php

namespace GuzzleHttp\Stream;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Uses PHP's zlib.inflate filter to inflate deflate or gzipped content.
 *
 * This stream decorator skips the first 10 bytes of the given stream to remove
 * the gzip header, converts the provided stream to a PHP stream resource,
 * then appends the zlib.inflate filter. The stream is then converted back
 * to a Guzzle stream resource to be used as a Guzzle stream.
 *
 * @link http://tools.ietf.org/html/rfc1952
 * @link http://php.net/manual/en/filters.compression.php
 */
class InflateStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        // read the first 10 bytes, ie. gzip header
        $header = \fread($resource, 10);
        $this->readHeaders($resource, $header);

        $trimmedResource = $this->cloneResource($resource);
        fseek($trimmedResource, 0);
        stream_filter_append($trimmedResource, 'zlib.inflate', \STREAM_FILTER_READ);
        $inflated = $this->cloneResource($trimmedResource);
        fclose($trimmedResource);

        $this->stream = Stream::create($inflated);
    }

    private function cloneResource($resource)
    {
        $copy = \fopen('php://temp', 'rw+');
        while (!feof($resource)) {
            if (!fwrite($copy, fread($resource, 8192))) {
                break;
            }
        }

        return $copy;
    }

    /**
     * @param resource $stream
     * @param $header
     *
     * @return int
     */
    private function readHeaders($stream, $header)
    {
        $filename_header_length = 0;

        if (substr(bin2hex($header), 6, 2) === '08') {
            // we have a filename, read until nil
            $filename_header_length = 1;
            while (fread($stream, 1) !== chr(0)) {
                $filename_header_length++;
            }
        }

        return $filename_header_length;
    }
}
