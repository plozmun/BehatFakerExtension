<?php

declare(strict_types=1);

namespace Plozmun\FakerExtension\Loader;

use Behat\Gherkin\Cache\CacheInterface;
use Behat\Gherkin\Loader\AbstractFileLoader;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;
use Faker\Factory;

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

        if ($this->cache) {
            if ($this->cache->isFresh($path, filemtime($path))) {
                $feature = $this->cache->read($path);
            } elseif (null !== $feature = $this->parseFeature($path)) {
                $this->cache->write($path, $feature);
            }
        } else {
            $feature = $this->parseFeature($path);
        }

        return null !== $feature ? array($feature) : array();
    }

    /**
     * Parses feature at provided absolute path.
     *
     * @param string $path Feature path
     *
     * @return FeatureNode
     */
    protected function parseFeature($path)
    {
        $content = file_get_contents($path);
        $faker = Factory::create($this->locale);
        $content = $faker->parse($content);
        $feature = $this->parser->parse($content, $path);

        return $feature;
    }
}
