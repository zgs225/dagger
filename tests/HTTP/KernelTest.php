<?php
use Youplus\Http\Kernel;
use Youplus\Http\Request;

class KernelTest extends PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $kernel   = new Kernel('admin.user.view');
        $request  = Request::capture();
        $response = $kernel->handle($request);

        $this->assertInstanceOf('Youplus\Http\Response', $response);
    }
}
