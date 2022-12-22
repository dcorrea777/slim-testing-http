<?php

declare(strict_types=1);

namespace App;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

class AssertHttpResponse
{
    use WrapHttpResponse;

    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public static function create(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }

    public function assertCreated(): self
    {
        return $this->assertStatus(201);
    }

    public function assertNoContent(): self
    {
        return $this->assertStatus(204);
    }

    public function assertNotFound(): self
    {
        return $this->assertStatus(404);
    }

    public function assertForbidden(): self
    {
        return $this->assertStatus(403);
    }

    public function assertUnauthorized(): self
    {
        return $this->assertStatus(401);
    }

    public function assertUnprocessable(): self
    {
        return $this->assertStatus(422);
    }

    public function assertBadRequest(): self
    {
        return $this->assertStatus(400);
    }

    public function assertServerError(): self
    {
        Assert::assertTrue(
            $this->isServerError(),
            $this->statusMessageWithDetails('>=500, < 600', (string)$this->response->getStatusCode())
        );

        return $this;
    }

    public function assertStatus(int $status): self
    {
        $message = $this->statusMessageWithDetails((string)$status, (string)$actual = $this->getStatusCode());

        Assert::assertSame($actual, $status, $message);

        return $this;
    }

    public function assertHasHeader(string $name): self
    {
        Assert::assertTrue(
            $this->hasHeader($name),
            sprintf('Header [%s] not present on response.', $name)
        );

        return $this;
    }

    public function assertHeader(string $name, ?string $value = null): self
    {
        Assert::assertTrue(
            $this->hasHeader($name),
            sprintf('Header [%s] not present on response.', $name)
        );

        $actual = $this->getHeader($name);

        if (!is_null($value)) {
            Assert::assertEquals(
                $value,
                $actual,
                sprintf('Header [%s] was found, but value [%s] does not match [%s].', $name, $actual, $value)
            );
        }

        return $this;
    }

    public function assertHeaderMissing(string $name): self
    {
        Assert::assertFalse(
            $this->hasHeader($name),
            sprintf('Unexpected header [%s] is present on response.', $name)
        );

        return $this;
    }

    public function assertCookie(string $name, ?string $value = null): self
    {
        Assert::assertNotNull(
            $cookie = $this->getCookie($name),
            sprintf('Cookie [%s] not present on response.', $name),
        );

        if (is_null($value)) {
            return $this;
        }

        $cookieValue = (string)$cookie;

        Assert::assertEquals(
            $value,
            $cookieValue,
            sprintf('Cookie [%s] was found, but value [%s] does not match [%s].', $name, $cookieValue, $value),
        );

        return $this;
    }

    public function assertHasCookie(string $name): self
    {
        Assert::assertNotNull(
            $this->getCookie($name),
            sprintf('Cookie [%s] not present on response.', $name),
        );

        return $this;
    }

    public function assertContent(string $value): self
    {
        Assert::assertSame($value, $this->getContent());

        return $this;
    }

    /**
     * @param mixed[] $value
     */
    public function assertJson(array $value): self
    {
        $decoded = $this->decodeJson();

        Assert::assertSame($value, $decoded);

        return $this;
    }

    /**
     * @param mixed[] $headers
     * @param mixed[] $methods
     */
    public function assertCors(array $headers, array $methods, string $origin, string $credentials = 'true'): void
    {
        Assert::assertSame(
            $this->getHeader('Access-Control-Allow-Credentials'),
            $credentials
        );

        Assert::assertSame(
            $this->getHeader('Access-Control-Allow-Origin'),
            $origin,
        );

        Assert::assertSame(
            $this->getHeader('Access-Control-Allow-Headers'),
            implode(', ', $headers),
        );

        Assert::assertSame(
            $this->getHeader('Access-Control-Allow-Methods'),
            implode(', ', $methods),
        );
    }

    private function statusMessageWithDetails(string $expected, string $actual): string
    {
        return sprintf('Expected response status code [%s] but received %s.', $expected, $actual);
    }
}
