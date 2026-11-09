<?php

declare(strict_types=1);

namespace Talis\Message;

class Response implements \Stringable
{
    /**
     * @var list<string>
     */
    private array $headers = [];

    private ?aStatus $status = null;

    private string $message = '';

    private ResponseType $type = ResponseType::Response;

    private mixed $payload = null;

    /**
     * @return list<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeader(string $header): static
    {
        $this->headers[] = $header;
        return $this;
    }

    public function setStatus(aStatus $status): aStatus
    {
        return $this->status = $status;
    }

    /**
     * The status set with setStatus(), or a Code500 if none was set.
     * The Code500 is not stored; check hasStatus() to tell the two apart.
     */
    public function getStatus(): aStatus
    {
        return $this->status ?? new Status\Code500();
    }

    public function hasStatus(): bool
    {
        return $this->status !== null;
    }

    public function setMessage(string $message): string
    {
        return $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setPayload(mixed $payload): mixed
    {
        return $this->payload = $payload;
    }

    public function getPayload(): mixed
    {
        return $this->payload ??= new \stdClass();
    }

    public function markError(): void
    {
        $this->type = ResponseType::Error;
    }

    public function markDependency(): void
    {
        $this->type = ResponseType::Dependency;
    }

    public function markResponse(): void
    {
        $this->type = ResponseType::Response;
    }

    public function getResponseType(): ResponseType
    {
        return $this->type;
    }

    /**
     * Rebuilds the body from the status, type, message and payload each call.
     */
    public function getBody(): \stdClass
    {
        $status = $this->getStatus();

        $body = new \stdClass();
        $body->status = "{$status->getCode()} {$status->getMsg()}";
        $body->type = $this->type->value;
        $body->message = $this->getMessage();
        $body->payload = $this->getPayload();
        return $body;
    }

    /**
     * @throws \JsonException if the payload can't be encoded
     */
    #[\Override]
    public function __toString(): string
    {
        return json_encode($this->getBody(), JSON_THROW_ON_ERROR);
    }
}
