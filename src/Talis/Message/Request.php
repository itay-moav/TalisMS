<?php

declare(strict_types=1);

namespace Talis\Message;

class Request
{
    /**
     * @var list<string>
     */
    private array $headers = [];

    private \stdClass $body;

    /**
     * @param array<array-key, mixed> $getParams
     */
    public function __construct(
        private readonly string $fullUri,
        private readonly array $getParams = [],
        ?\stdClass $body = null,
    ) {
        $this->body = $body ?? new \stdClass();
    }

    /**
     * @return array<string, string|list<string>>
     */
    public function __serialize(): array
    {
        return [
            'headers' => $this->headers,
            'fullUri' => $this->fullUri,
            'body' => serialize($this->body),
            'getParams' => serialize($this->getParams),
        ];
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $headers = $data['headers'] ?? null;
        if (!is_array($headers) || !array_is_list($headers) || array_filter($headers, static fn (mixed $header): bool => !is_string($header)) !== []) {
            throw new \UnexpectedValueException('Bad serialized Request data: headers');
        }

        $fullUri = $data['fullUri'] ?? null;
        if (!is_string($fullUri)) {
            throw new \UnexpectedValueException('Bad serialized Request data: fullUri');
        }

        $bodyRaw = $data['body'] ?? null;
        if (!is_string($bodyRaw)) {
            throw new \UnexpectedValueException('Bad serialized Request data: body');
        }
        $body = unserialize($bodyRaw, ['allowed_classes' => [\stdClass::class]]);
        if (!$body instanceof \stdClass) {
            throw new \UnexpectedValueException('Bad serialized Request data: body');
        }

        $getParamsRaw = $data['getParams'] ?? null;
        if (!is_string($getParamsRaw)) {
            throw new \UnexpectedValueException('Bad serialized Request data: getParams');
        }
        $getParams = unserialize($getParamsRaw, ['allowed_classes' => false]);
        if (!is_array($getParams)) {
            throw new \UnexpectedValueException('Bad serialized Request data: getParams');
        }

        /** @var list<string> $headers */
        $this->headers = $headers;
        $this->fullUri = $fullUri;
        $this->body = $body;
        /** @var array<array-key, mixed> $getParams */
        $this->getParams = $getParams;
    }

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

    /**
     * The json decoded body (an empty stdClass if none was given).
     */
    public function getBody(): \stdClass
    {
        return $this->body;
    }

    public function setBody(\stdClass $body): \stdClass
    {
        return $this->body = $body;
    }

    /**
     * Full URI of this request (API + get params).
     */
    public function getUri(): string
    {
        return $this->fullUri;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAllGetParams(): array
    {
        return $this->getParams;
    }

    /**
     * The url/param/value or null.
     *
     * @throws \Talis\Exception\InvalidParam if the value is not a single scalar
     */
    public function getParamNull(string $key): ?string
    {
        $value = $this->getParams[$key] ?? null;
        if ($value === null) {
            return null;
        }
        return $this->paramToString($key, $value);
    }

    /**
     * The url/param/value or a default.
     *
     * @throws \Talis\Exception\InvalidParam if the value is not a single scalar
     */
    public function getParamDefault(string $key, string $default): string
    {
        $value = $this->getParams[$key] ?? null;
        if ($value === null) {
            return $default;
        }
        return $this->paramToString($key, $value);
    }

    /**
     * Same as getParamOrFail(); kept for existing callers.
     *
     * @throws \Talis\Exception\ParamNotFound
     * @throws \Talis\Exception\InvalidParam if the value is not a single scalar
     */
    public function getParamExists(string $key): string
    {
        return $this->getParamOrFail($key);
    }

    /**
     * The url/param/value. Fails if no param found.
     *
     * @throws \Talis\Exception\ParamNotFound
     * @throws \Talis\Exception\InvalidParam if the value is not a single scalar
     */
    public function getParamOrFail(string $key): string
    {
        if (!isset($this->getParams[$key])) {
            throw new \Talis\Exception\ParamNotFound($key);
        }
        return $this->paramToString($key, $this->getParams[$key]);
    }

    private function paramToString(string $key, mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        throw new \Talis\Exception\InvalidParam($key, 'must be a single value, got ' . get_debug_type($value));
    }

    /**
     * The body->params object. Creates it if missing or casts an array to an object.
     *
     * @throws \Talis\Exception\InvalidParam if params is a scalar
     */
    public function getBodyParams(): \stdClass
    {
        $params = $this->body->params ?? null;

        if ($params === null) {
            $params = new \stdClass();
            $this->body->params = $params;
            return $params;
        }

        if (is_array($params)) {
            $params = (object) $params;
            $this->body->params = $params;
            return $params;
        }

        if (!$params instanceof \stdClass) {
            throw new \Talis\Exception\InvalidParam('params', 'must be an object');
        }

        return $params;
    }

    /**
     * The body->params->key or a default value | null.
     */
    public function getBodyParam(string $key, mixed $default = null): mixed
    {
        return $this->getBodyParams()->$key ?? $default;
    }

    /**
     * The body->params->key. This can be called if you are sure the param exists.
     *
     * @throws \Talis\Exception\ParamNotFound
     */
    public function getBodyParamExists(string $key): mixed
    {
        $params = $this->getBodyParams();
        if (!property_exists($params, $key)) {
            throw new \Talis\Exception\ParamNotFound($key);
        }
        return $params->$key;
    }

    /**
     * The body->params->key. Fails if no param found.
     *
     * @throws \Talis\Exception\ParamNotFound
     */
    public function getBodyParamOrFail(string $key): mixed
    {
        $params = $this->getBodyParams();
        if (!isset($params->$key)) {
            throw new \Talis\Exception\ParamNotFound($key);
        }
        return $params->$key;
    }

    /**
     * Add values/keys to the parameter section of body->params.
     */
    public function addToBodyParams(string $key, mixed $value): mixed
    {
        return $this->getBodyParams()->$key = $value;
    }
}
