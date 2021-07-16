<?php

declare(strict_types=1);

namespace Qossmic\Deptrac;

use Qossmic\Deptrac\AstRunner\AstMap;
use Qossmic\Deptrac\AstRunner\AstMap\ClassToken\ClassLikeName;
use Qossmic\Deptrac\Collector\Registry;
use Qossmic\Deptrac\Configuration\Configuration;
use Qossmic\Deptrac\Configuration\ParameterResolver;

class TokenLayerResolver implements TokenLayerResolverInterface
{
    private Configuration $configuration;
    private AstMap $astMap;
    private Registry $collectorRegistry;
    private ParameterResolver $parameterResolver;

    public function __construct(
        Configuration $configuration,
        AstMap $astMap,
        Registry $collectorRegistry,
        ParameterResolver $parameterResolver
    ) {
        $this->configuration = $configuration;
        $this->astMap = $astMap;
        $this->collectorRegistry = $collectorRegistry;
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * @return string[]
     */
    public function getLayersByTokenName(AstMap\TokenName $tokenName): array
    {
        /** @var array<string, bool> $layers */
        $layers = [];

        if ($tokenName instanceof ClassLikeName) {
            if (!$astTokenReference = $this->astMap->getClassReferenceByClassName($tokenName)) {
                $astTokenReference = new AstMap\ClassToken\AstClassReference($tokenName);
            }
        } elseif ($tokenName instanceof AstMap\FunctionToken\FunctionName) {
            if (!$astTokenReference = $this->astMap->getFunctionReferenceByFunctionName($tokenName)) {
                $astTokenReference = new AstMap\FunctionToken\AstFunctionReference($tokenName);
            }
        } elseif ($tokenName instanceof AstMap\File\FileName) {
            if (!$astTokenReference = $this->astMap->getFileReferenceByFileName($tokenName)) {
                $astTokenReference = new AstMap\File\AstFileReference($tokenName->getFilepath(), [], [], []);
            }
        } elseif ($tokenName instanceof AstMap\SuperGlobalName) {
            $astTokenReference = new AstMap\AstVariableReference($tokenName);
        } else {
            throw new ShouldNotHappenException();
        }

        foreach ($this->configuration->getLayers() as $configurationLayer) {
            foreach ($configurationLayer->getCollectors() as $configurationCollector) {
                $collector = $this->collectorRegistry->getCollector($configurationCollector->getType());

                $configuration = $this->parameterResolver->resolve(
                    $configurationCollector->getArgs(),
                    $this->configuration->getParameters()
                );

                if ($collector->satisfy($configuration, $astTokenReference, $this->astMap, $this->collectorRegistry)) {
                    $layers[$configurationLayer->getName()] = true;
                }
            }
        }

        /** @var string[] $layerNames */
        $layerNames = array_keys($layers);

        return $layerNames;
    }
}
