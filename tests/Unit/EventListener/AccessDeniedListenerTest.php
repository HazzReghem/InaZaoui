<?php

namespace App\Tests\Unit\EventListener;

use App\EventListener\AccessDeniedListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AccessDeniedListenerTest extends TestCase
{
    public function testOnKernelExceptionWithAccessDeniedException(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')
            ->with('security/access_denied.html.twig')
            ->willReturn('Access Denied');

        $listener = new AccessDeniedListener($twig);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $exception = new AccessDeniedException();

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $listener->onKernelException($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals('Access Denied', $response->getContent());
    }

    public function testOnKernelExceptionWithOtherExceptionDoesNothing(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->never())->method('render');

        $listener = new AccessDeniedListener($twig);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $exception = new \RuntimeException('Some other exception');

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }
}
