<?php

use Brickhouse\Routing\Tests;

pest()
    ->extend(Tests\TestCase::class)
    ->in('Unit', 'Feature');
