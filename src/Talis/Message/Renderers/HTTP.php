<?php

declare(strict_types=1);

namespace Talis\Message\Renderers;

/**
 * Sends a Response as HTTP: status line, headers, then a JSON body.
 */
class HTTP implements \Talis\commons\iEmitter
{
    /**
     * @var \Closure(string): void
     */
    private readonly \Closure $sendHeader;

    /**
     * @param (\Closure(string): void)|null $sendHeader defaults to header()
     */
    public function __construct(?\Closure $sendHeader = null)
    {
        $this->sendHeader = $sendHeader ?? header(...);
    }

    #[\Override]
    public function emit(\Talis\Message\Response $message): void
    {
        // Encode first: if this throws, no header or output has been sent.
        $body = json_encode($message->getBody(), JSON_THROW_ON_ERROR);

        $status = $message->getStatus();
        $headers = [
            "HTTP/1.1 {$status->getCode()} {$status->getMsg()}",
            'Content-Type: application/json; charset=utf-8',
            ...$message->getHeaders(),
        ];

        \Talis\TalisMain::logger()->debug('SENDING HEADERS');
        \Talis\TalisMain::logger()->debug($headers);

        foreach ($headers as $header) {
            ($this->sendHeader)($header);
        }

        \Talis\TalisMain::logger()->debug('SENDING BODY');
        \Talis\TalisMain::logger()->debug($body);

        echo $body;
    }
}
