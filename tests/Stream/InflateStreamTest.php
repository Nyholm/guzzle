<?php

namespace GuzzleHttp\Tests\Stream;

use GuzzleHttp\Stream\InflateStream;
use PHPUnit\Framework\TestCase;

class InflateStreamTest extends TestCase
{
    public function testInflatesStreams()
    {
        $content = gzencode('test');
        $resource = \fopen('php://temp', 'rw+');
        fwrite($resource, $content);
        fseek($resource, 0);

        $b = new InflateStream($resource);
        self::assertSame('test', (string) $b);
    }

    public function testInflatesStreamsWithFilename()
    {
        $content = $this->getGzipStringWithFilename('test');
        $resource = \fopen('php://temp', 'w+');
        fwrite($resource, $content);
        fseek($resource, 0);

        $b = new InflateStream($resource);
        self::assertSame('test', (string) $b);
    }

    private function getGzipStringWithFilename($original_string)
    {
        $gzipped = bin2hex(gzencode($original_string));

        $header = substr($gzipped, 0, 20);
        // set FNAME flag
        $header[6] = 0;
        $header[7] = 8;
        // make a dummy filename
        $filename = '64756d6d7900';
        $rest = substr($gzipped, 20);

        return hex2bin($header . $filename . $rest);
    }
}
