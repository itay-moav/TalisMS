<?php

declare(strict_types=1);

namespace Talis\Chain;

/**
 * Head of a request chain. It does no work itself; it only lines up the
 * chain in this order: filters, then dependencies, then the BL links.
 *
 * @author Itay Moav
 * @Date  2017-05-19
 *
 * @phpstan-import-type ChainEntry from ChainContainer
 */
abstract class aFilteredValidatedChainLink extends aChainLink
{
    /**
     * @var list<array{0: class-string<\Talis\Chain\Filters\aFilter>, 1: array<mixed>}>
     */
    protected array $filters = [];

    /**
     * @var list<array{0: class-string<\Talis\Chain\Dependencies\aDependency>, 1: array<mixed>}>
     */
    protected array $dependencies = [];

    /**
     * @param array<mixed> $params
     */
    public function __construct(\Talis\Message\Request $Request, \Talis\Message\Response $Response, array $params = [])
    {
        parent::__construct($Request, $Response, $params);
        $this->loadChainContainer();
    }

    final protected function loadChainContainer(): void
    {
        $this->setChainContainer(new ChainContainer([...$this->filters, ...$this->dependencies, ...$this->getNextBl()]));
    }

    #[\Override]
    final public function process(): aChainLink
    {
        return $this;
    }

    /**
     * The BL links that run after the filters and dependencies.
     *
     * @return list<ChainEntry>
     */
    abstract protected function getNextBl(): array;
}
