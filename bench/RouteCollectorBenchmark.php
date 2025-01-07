<?php

namespace Brickhouse\Routing\Benchmarks;

use Brickhouse\Routing\Benchmarks\Datasets\RealExample;
use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods(['createCollector'])]
class RouteCollectorBenchmark
{
    protected RouteCollector $collector;

    public function createCollector(): void
    {
        $this->collector = new RouteCollector(new RouteParser());
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchRegisterStatic()
    {
        $this->collector->addRoute('GET', '/settings/admin', 'handle');
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchRegisterDynamic()
    {
        $this->collector->addRoute('GET', '/users/:user_id/photos/:photo_id', 'handle');
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchRegisterRealLife()
    {
        RealExample::register($this->collector);
    }
}
