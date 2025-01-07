<?php

namespace Brickhouse\Routing\Benchmarks;

use Brickhouse\Routing\Dispatcher;
use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;
use PhpBench\Attributes as Bench;

#[Bench\Groups(["compare"])]
#[Bench\BeforeMethods(['createDispatcher'])]
class MicroStaticBenchmark
{
    protected Dispatcher $dispatcher;
    protected string $lastRoute = '';

    protected const int N_ROUTES = 100;

    protected const int N_REVS = 30000;

    public function createDispatcher(): void
    {
        $parser = new RouteParser();
        $collector = new RouteCollector($parser);

        for ($i = 0, $str = 'a'; $i < self::N_ROUTES; $i++, $str++) {
            $collector->addRoute('GET', '/' . $str . '/:arg', 'handler' . $i);
            $this->lastRoute = $str;
        }

        $this->dispatcher = new Dispatcher($collector);
    }

    #[Bench\Subject]
    #[Bench\Revs(self::N_REVS)]
    public function benchDispatchFirst()
    {
        $this->dispatcher->dispatch('GET', '/a/foo');
    }

    #[Bench\Subject]
    #[Bench\Revs(self::N_REVS)]
    public function benchDispatchLast()
    {
        $this->dispatcher->dispatch('GET', $this->lastRoute);
    }

    #[Bench\Subject]
    #[Bench\Revs(self::N_REVS)]
    public function benchDispatchUnknown()
    {
        $this->dispatcher->dispatch('GET', '/foorbar/bar');
    }
}
