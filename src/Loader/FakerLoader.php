<?php

declare(strict_types=1);

namespace Plozmun\FakerExtension\Loader;

use Plozmun\FakerExtension\Parser\FeatureParser;
use Behat\Gherkin\Loader\LoaderInterface;

final class FakerLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var FeatureParser
     */
    private $featureParser;

    public function __construct(
        LoaderInterface $loader,
        FeatureParser $featureParser
    ) {
        $this->loader = $loader;
        $this->featureParser = $featureParser;
    }

    public function supports($resource)
    {
        return $this->loader->supports($resource);
    }

    public function load($resource)
    {
        $features = $this->loader->load($resource);

        $fakerFeatures = [];
        foreach ($features as $feature) {
            $fakerFeatures[] = $this->featureParser->parse($feature);
        }
        return $fakerFeatures;
    }
}
