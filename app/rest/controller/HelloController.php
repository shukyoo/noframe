<?php

class HelloController extends ControllerAbstract
{
    public function sayHello($name)
    {
        return $this->success(['data' => 'hello '. $name]);
    }
}