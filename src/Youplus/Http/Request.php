<?php namespace Youplus\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyHttpRequest;

class Request extends SymfonyHttpRequest
{
    /**
     * 从$_SERVER中创建一个Request对象
     *
     * @return static
     **/
    public static function capture()
    {
        static::enableHttpMethodParameterOverride();

        return static::createFromBase(SymfonyHttpRequest::createFromGlobals());
    }

    /**
     * 从SymfonyHttpRequest中创建Request
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return Youplus\Http\Request
     **/
    public static function createFromBase(SymfonyHttpRequest $request)
    {
        if ($request instanceof static) {
            return $request;
        }

        $content = $request->content;

        $request = (new static)->dumplicate(
        );
    }
}
