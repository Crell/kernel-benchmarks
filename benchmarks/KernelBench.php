<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\AttributeUtils\Analyzer;
use Crell\AttributeUtils\MemoryCacheAnalyzer;
use Crell\EnvBench\Environment;
use Crell\EnvBench\EnvironmentNoFolding;
use Crell\EnvBench\EnvironmentPhpNames;
use Crell\EnvBench\ManualMap;
use Crell\EnvMapper\EnvMapper;
use Crell\KernelBench\Monad\MonadicKernel;
use Crell\Serde\Formatter\ArrayFormatter;
use Crell\Serde\SerdeCommon;
use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\RetryThreshold;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

/**
 * @Revs(100)
 * @Iterations(10)
 * @Warmup(2)
 * @BeforeMethods({"setUpMonadicKernel"})
 * @AfterMethods({"tearDown"})
 * @OutputTimeUnit("milliseconds", precision=4)
 * @RetryThreshold(10.0)
 */
class KernelBench
{

    private readonly MonadicKernel $monadicKernel;

    public function setUpMonadicKernel(): void
    {
        $kernel = new MonadicKernel();

        $this->monadicKernel = $kernel;
    }

    public function tearDown(): void {}


    public function bench_serde_using_field_rename(): void
    {
        /** @var Environment $env */
        $env = $this->serde->deserialize($_ENV, from: 'array', to: Environment::class);
    }

    public function bench_serde_using_manual_rename(): void
    {
        $envArray = array_combine(
            array_map(strtolower(...), array_keys($_ENV)),
            $_ENV,
        );

        $env = $this->serde->deserialize($envArray, from: 'array', to: EnvironmentNoFolding::class);
    }

    public function bench_manual_map_using_manual_rename(): void
    {
        $envArray = array_combine(
            array_map(strtolower(...), array_keys($_ENV)),
            $_ENV,
        );

        $env = $this->manualMapper->map($envArray, Environment::class);
    }

    public function bench_manual_map_using_auto_rename(): void
    {
        /** @var EnvironmentPhpNames $env */
        $env = $this->manualMapper->mapDynamicCaseFolding($_ENV, EnvironmentPhpNames::class);
    }

    public function bench_manual_map_using_auto_rename_optimized(): void
    {
        /** @var EnvironmentPhpNames $env */
        $env = $this->manualMapper->mapDynamicCaseFoldingOptimized($_ENV, EnvironmentPhpNames::class);
    }

    public function bench_envmapper(): void
    {
        /** @var EnvironmentPhpNames $env */
        $env = $this->manualMapper->mapDynamicCaseFoldingOptimized($_ENV, EnvironmentPhpNames::class);
    }
}
