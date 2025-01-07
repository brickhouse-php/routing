<?php

namespace Brickhouse\Routing\Benchmarks;

use Brickhouse\Routing\Dispatcher;
use Brickhouse\Routing\RouteCollector;
use Brickhouse\Routing\RouteParser;
use PhpBench\Attributes as Bench;

#[Bench\Groups(["compare"])]
#[Bench\BeforeMethods(['createDispatcher'])]
class MicroDynamicBenchmark
{
    protected Dispatcher $dispatcher;
    protected string $lastRoute = '';
    protected string $args = '';

    protected const int N_ROUTES = 100;

    protected const int N_REVS = 20000;

    protected const int N_ARGS = 9;

    public function createDispatcher(): void
    {
        $this->args = implode('/', array_map(fn(int $i) => ':arg' . $i, range(1, self::N_ARGS)));

        $parser = new RouteParser();
        $collector = new RouteCollector($parser);

        for ($i = 0, $str = 'a'; $i < self::N_ROUTES; $i++, $str++) {
            $collector->addRoute('GET', '/' . $str . '/' . $this->args, 'handler' . $i);
            $this->lastRoute = $str;
        }

        $this->dispatcher = new Dispatcher($collector);
    }

    #[Bench\Subject]
    #[Bench\Revs(self::N_REVS)]
    public function benchDispatchFirst()
    {
        $this->dispatcher->dispatch('GET', '/a/' . $this->args);
    }

    #[Bench\Subject]
    #[Bench\Revs(self::N_REVS)]
    public function benchDispatchLast()
    {
        $this->dispatcher->dispatch('GET', '/' . $this->lastRoute . '/' . $this->args);
    }

    #[Bench\Subject]
    #[Bench\Revs(self::N_REVS)]
    public function benchDispatchUnknown()
    {
        $this->dispatcher->dispatch('GET', '/foorbar/' . $this->args);
    }
}
