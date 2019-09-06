<?php

class HelloController extends ControllerAbstract
{
    public function sayHello($name)
    {
        return 'hello '. $name;
    }
}