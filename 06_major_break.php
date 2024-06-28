<?php

abstract class Whut
{
    abstract public function foo(/*?string $categoryId = null*/);
}

class Core extends Whut
{
    public function foo()
    {
        $categoryId = count(func_get_args()) > 0 ? func_get_arg(0) : null;

        return 'foo ' . $categoryId;
    }
}

class Extension extends Whut
{
    public function __construct(private Whut $core)
    {
    }
    public function foo()
    {
        $categoryId = count(func_get_args()) > 0 ? func_get_arg(0) : null;

        return $this->core->foo($categoryId) .  '   bar';
    }
}

$core = new Core();
echo $core->foo(' 1 ');

echo PHP_EOL;

$extension = new Extension(new Extension(new Core()));
echo $extension->foo('  1 ');
