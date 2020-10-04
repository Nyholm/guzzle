<?php

namespace GuzzleHttp\Tests\Stream;

use GuzzleHttp\Stream\LazyOpenStream;
use GuzzleHttp\Utils;
use PHPUnit\Framework\TestCase;

class LazyOpenStreamTest extends TestCase
{
    private $fname;

    /**
     * @before
     */
    public function setUpTest()
    {
        $this->fname = tempnam(sys_get_temp_dir(), 'tfile');

        if (file_exists($this->fname)) {
            unlink($this->fname);
        }
    }

    /**
     * @after
     */
    public function tearDownTest()
    {
        if (file_exists($this->fname)) {
            unlink($this->fname);
        }
    }

    public function testOpensLazily()
    {
        $l = new LazyOpenStream($this->fname, 'w+');
        $l->write('foo');
        self::assertIsArray($l->getMetadata());
        self::assertFileExists($this->fname);
        self::assertSame('foo', file_get_contents($this->fname));
        self::assertSame('foo', (string) $l);
    }

    public function testProxiesToFile()
    {
        file_put_contents($this->fname, 'foo');
        $l = new LazyOpenStream($this->fname, 'r');
        self::assertSame('foo', $l->read(4));
        self::assertTrue($l->eof());
        self::assertSame(3, $l->tell());
        self::assertTrue($l->isReadable());
        self::assertTrue($l->isSeekable());
        self::assertFalse($l->isWritable());
        $l->seek(1);
        self::assertSame('oo', $l->getContents());
        self::assertSame('foo', (string) $l);
        self::assertSame(3, $l->getSize());
        self::assertIsArray($l->getMetadata());
        $l->close();
    }

    public function testDetachesUnderlyingStream()
    {
        file_put_contents($this->fname, 'foo');
        $l = new LazyOpenStream($this->fname, 'r');
        $r = $l->detach();
        self::assertIsResource($r);
        fseek($r, 0);
        self::assertSame('foo', stream_get_contents($r));
        fclose($r);
    }

    public function testOpensFilesSuccessfully()
    {
        $stream = new LazyOpenStream(__FILE__, 'r');
        $r = $stream->detach();
        self::assertIsResource($r);
        fclose($r);
    }

    public function testThrowsExceptionNotWarning()
    {
        $stream = new LazyOpenStream('/path/to/does/not/exist', 'r');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to open /path/to/does/not/exist using mode r');
        $stream->getContents();
    }
}
