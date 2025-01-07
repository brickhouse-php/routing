<?php

namespace Brickhouse\Routing\Benchmarks;

use Brickhouse\Routing\Benchmarks\Datasets\RealExample;
use Brickhouse\Routing\Dispatcher;
use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods(['createDispatcher'])]
class DispatcherBenchmark
{
    protected Dispatcher $dispatcher;

    public function createDispatcher(): void
    {
        $parser = new RouteParser();
        $collector = new RouteCollector($parser);

        foreach (RealExample::ROUTES as [$methods, $route]) {
            $collector->addRoute($methods, $route, '');
        }

        $this->dispatcher = new Dispatcher($collector);
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchDispatchStatic()
    {
        $this->dispatcher->dispatch('GET', '/settings/admin');
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchDispatchDynamic()
    {
        $this->dispatcher->dispatch('GET', '/users/:user_id/photos/:photo_id');
    }

    #[Bench\Subject]
    #[Bench\Revs(1000)]
    public function benchDispatchRealLife()
    {
        RealExample::dispatch($this->dispatcher);
    }
}
