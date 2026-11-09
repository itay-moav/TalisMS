<?php

declare(strict_types=1);

namespace Talis\Router;

use Talis\Chain\aChainLink;
use Talis\Exception\BadUri;
use Talis\Message\Request;
use Talis\Message\Response;
use Talis\TalisMain;

/**
 * Routes /a/b/c[/key/value...] to the class Api\abc in <appPath>/api/a/b/c.php.
 * Path parts after the third one are read as key/value GET params.
 *
 * @author itay
 */
class DefaultRouter extends aRouter
{
    /**
     * Allowed chars in the three route segments. Blocks '..' and the like.
     * D makes $ match only at the very end, so a trailing newline fails.
     */
    private const string SEGMENT_PATTERN = '/^[A-Za-z0-9_]+$/D';

    /**
     * @var array{file: string, className: string}
     */
    protected array $route = ['file' => '', 'className' => ''];

    #[\Override]
    public function generateRoute(): void
    {
        if (count($this->requestParts) < 4) { // one root part + three parts for the API
            throw new BadUri(implode('/', $this->requestParts));
        }

        [, $p1, $p2, $p3] = $this->requestParts;
        foreach ([$p1, $p2, $p3] as $segment) {
            if (preg_match(self::SEGMENT_PATTERN, $segment) !== 1) {
                throw new BadUri($segment);
            }
        }

        $this->route = [
            'file' => TalisMain::$appPath . "/api/{$p1}/{$p2}/{$p3}.php",
            'className' => "Api\\{$p1}{$p2}{$p3}",
        ];
        TalisMain::logger()->debug("Doing route [{$this->route['file']}]");
    }

    #[\Override]
    public function generateQuery(): array
    {
        $count = count($this->requestParts);
        $params = [];
        for ($i = 4; $i < $count; $i += 2) {
            $params[$this->requestParts[$i]] = $this->requestParts[$i + 1] ?? '';
        }

        if ($this->getParams !== []) {
            // array_replace keeps numeric keys; query-string values win
            $params = array_replace($params, $this->getParams);
            TalisMain::logger()->debug('extra with HTTP GET params');
            TalisMain::logger()->debug($params);
        }
        return $params;
    }

    #[\Override]
    public function getChainHead(Request $Request, Response $Response): aChainLink
    {
        $file = $this->route['file'];
        TalisMain::logger()->debug("TRYING TO INCLUDE: {$file}");
        if (!file_exists($file)) {
            throw new BadUri($file);
        }
        require_once $file;

        $className = $this->route['className'];
        if (!class_exists($className, false) || !is_subclass_of($className, aChainLink::class)) {
            throw new BadUri($className);
        }
        return new $className($Request, $Response);
    }
}
