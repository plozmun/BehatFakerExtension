<?php

declare(strict_types=1);

namespace Plozmun\FakerExtension\Loader;

use Behat\Gherkin\Cache\CacheInterface;
use Behat\Gherkin\Loader\AbstractFileLoader;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;
use Faker\Factory;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class FakerFileLoader extends AbstractFileLoader
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var string
     */
    private $locale;

    public function __construct(
        Parser $parser,
        CacheInterface $cache,
        string $locale
    ) {
        $this->parser = $parser;
        $this->cache = $cache;
        $this->locale = $locale;
    }

    /**
     * Checks if current loader supports provided resource.
     *
     * @param mixed $path Resource to load
     *
     * @return bool
     */
    public function supports($path)
    {
        return is_string($path)
            && is_file($absolute = $this->findAbsolutePath($path))
            && 'feature' === pathinfo($absolute, PATHINFO_EXTENSION);
    }

    /**
     * Loads features from provided resource.
     *
     * @param string $path Resource to load
     *
     * @return FeatureNode[]
     */
    public function load($path)
    {
        $path = $this->findAbsolutePath($path);
        if (false === $filetime = filemtime($path)) {
            throw new \RuntimeException(sprintf('Error getting the modification time for file %s', $path));
        }

        if ($this->cache->isFresh($path, $filetime)) {
            $feature = $this->cache->read($path);
        } elseif (null !== $feature = $this->parseFeature($path)) {
            $this->cache->write($path, $feature);
        }

        return null !== $feature ? [$feature]: [];
    }

    /**
     * Parses feature at provided absolute path.
     */
    protected function parseFeature(string $path): ?FeatureNode
    {
        if (false === $content = file_get_contents($path)) {
            throw new \RuntimeException(sprintf('Error reading file %s', $path));
        }
        $faker = Factory::create($this->locale);
        $expressionLanguage = new ExpressionLanguage();

        $callback = function ($matches) use ($faker, $expressionLanguage) {
            $expressionLanguage->evaluate("faker.".$matches[0], ['faker' => $faker]);
        };

        $content = preg_replace_callback('#\{\{(.*?)\}\}#', $callback, $content);

        return $this->parser->parse($content, $path);
    }
}
