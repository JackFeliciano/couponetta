<?php

declare(strict_types=1);

namespace Couponetta\App;

use Couponetta\Home\Sample;

/**
 * The front controller. Routes, then renders. The health route answers with a
 * count taken from the sample so a probe can tell a working process from a live
 * one.
 */
final class Front
{
    public function __construct(private readonly string $base)
    {
    }

    public function run(): void
    {
        $router = new Router($this->base);
        $views = new Views($this->base);
        $sample = new Sample();

        $router->get('/', static fn (): array => [200, [
            'html' => $views->front($sample),
        ]]);
        $router->get('/health', static fn (): array => [200, ['json' => [
            'ok' => true,
            'deals' => $sample->deals(),
            'sold' => $sample->couponsSold(),
            'used' => $sample->couponsUsed(),
            'takings' => $sample->takings(),
            'platform' => $sample->toPlatform(),
        ]]]);

        [$status, $body] = $router->handle(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
        );

        http_response_code($status);
        if (isset($body['json'])) {
            header('Content-Type: application/json');
            echo json_encode($body['json'], JSON_UNESCAPED_SLASHES), "\n";

            return;
        }
        if (isset($body['html'])) {
            header('Content-Type: text/html; charset=utf-8');
            echo $body['html'];

            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $views->missing();
    }
}
