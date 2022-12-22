<?php

declare(strict_types=1);

namespace App;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use PHPUnit\Framework\Assert;

use function is_null;
use function json_decode;

trait WrapHttpResponse
{
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function getCookies(): SetCookies
    {
        return SetCookies::fromSetCookieStrings($this->response->getHeader('Set-Cookie'));
    }

    public function getContent(): string
    {
        return (string)$this->response->getBody();
    }

    public function getCookie(string $name): ?SetCookie
    {
        return $this->getCookies()->get($name);
    }

    public function decodeJson(): array
    {
        /** @var array|null|false $decoded */
        $decoded = json_decode($this->getContent(), true);

        if (is_null($decoded) || $decoded === false) {
            Assert::fail('Invalid JSON was returned from the route.');
        }

        return $decoded;
    }
}
