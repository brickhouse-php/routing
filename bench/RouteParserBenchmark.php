<?php

namespace Brickhouse\Routing\Benchmarks;

use Brickhouse\Routing\Benchmarks\Datasets\RealExample;
use Brickhouse\Routing\RouteParser;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods(['createParser'])]
class RouteParserBenchmark
{
    protected RouteParser $parser;

    public function createParser(): void
    {
        $this->parser = new RouteParser;
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchParseStatic()
    {
        $this->parser->parse('/settings/admin');
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchParseDynamic()
    {
        $this->parser->parse('/users/:user_id/photos/:photo_id');
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchParseRealLife()
    {
        RealExample::parse($this->parser);
    }
}
