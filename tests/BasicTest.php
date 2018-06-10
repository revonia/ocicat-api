<?php

class BasicTest extends TestCase
{
    //test what you want!
    public function testFoo()
    {

    }

    public function testIndex()
    {
        $this->get($this->u('index'))
            ->seeJson([
                'message' => 'Ocicat-api-pre1',
            ]);
    }
}
