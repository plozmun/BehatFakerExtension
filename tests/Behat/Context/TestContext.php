<?php

declare(strict_types=1);

namespace Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

final class TestContext implements Context
{
    /** @var string */
    private static $workingDir;

    /** @var Filesystem */
    private static $filesystem;

    /** @var Process */
    private $process;

    /** @var string */
    private static $phpBin;

    /** @var array */
    private $variables = [];

    /**
     * @BeforeFeature
     */
    public static function beforeFeature(): void
    {
        self::$workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('', true));
        self::$filesystem = new Filesystem();
        self::$phpBin = self::findPhpBinary();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
        self::$filesystem->mkdir(self::$workingDir, 0777);
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
    }

    /**
     * @Given a working Symfony application
     */
    public function workingSymfonyApplicationWithExtension(): void
    {
        // autoload
        $this->thereIsFile('vendor/autoload.php', sprintf(<<<'CON'
<?php

declare(strict_types=1);

$loader = require '%s';
$loader->addPsr4('Plozmun\\FakerExtension\\', '%s');
$loader->addPsr4('App\\Tests\\', __DIR__ . '/../tests/');

return $loader; 
CON
            , __DIR__ . '/../../../vendor/autoload.php', __DIR__ . '/../../../src'));
    }

    /**
     * @Given /^a Behat configuration containing(?: "([^"]+)"|:)$/
     */
    public function thereIsConfiguration(string $content): void
    {
        $mainConfigFile = sprintf('%s/behat.yml', self::$workingDir);
        $newConfigFile = sprintf('%s/behat-%s.yml', self::$workingDir, md5((string) $content));

        self::$filesystem->dumpFile($newConfigFile, (string) $content);

        if (!file_exists($mainConfigFile)) {
            self::$filesystem->dumpFile($mainConfigFile, Yaml::dump(['imports' => []]));
        }

        $mainBehatConfiguration = Yaml::parseFile($mainConfigFile);
        $mainBehatConfiguration['imports'][] = $newConfigFile;

        self::$filesystem->dumpFile($mainConfigFile, Yaml::dump($mainBehatConfiguration));
    }

    /**
     * @Given /^a (?:.+ |)file "([^"]+)" containing(?: "([^"]+)"|:)$/
     * @param object|string $content
     */
    public function thereIsFile(string $file, $content): string
    {
        $path = self::$workingDir . '/' . $file;

        self::$filesystem->dumpFile($path, (string) $content);

        return $path;
    }

    /**
     * @Given /^a feature file containing(?: "([^"]+)"|:)$/
     */
    public function thereIsFeatureFile($content): void
    {
        $this->thereIsFile(sprintf('features/%s.feature', md5(uniqid('', true))), $content);
    }

    /**
     * @When /^I run Behat$/
     */
    public function iRunBehat(): void
    {
        $executablePath = BEHAT_BIN_PATH;

        if ($this->variables !== []) {
            $content = '<?php ';

            foreach ($this->variables['server'] ?? [] as $name => $value) {
                $content .= sprintf('$_SERVER["%s"] = "%s"; ', $name, $value);
            }

            foreach ($this->variables['environment'] ?? [] as $name => $value) {
                $content .= sprintf('$_ENV["%s"] = "%s"; ', $name, $value);
            }

            $content .= sprintf('require_once("%s"); ', $executablePath);

            $executablePath = $this->thereIsFile('__executable.php', $content);
        }

        $this->process = new Process([self::$phpBin, $executablePath, '--strict', '-vvv', '--no-interaction', '--lang=en'], self::$workingDir);
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then /^it should pass$/
     */
    public function itShouldPass(): void
    {
        if (0 === $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to pass, but failed with the following output:' . \PHP_EOL . \PHP_EOL . $this->getProcessOutput()
        );
    }

    private function getProcessOutput(): string
    {
        $this->assertProcessIsAvailable();

        return $this->process->getErrorOutput() . $this->process->getOutput();
    }

    private function getProcessExitCode(): int
    {
        $this->assertProcessIsAvailable();

        return $this->process->getExitCode();
    }

    /**
     * @throws \BadMethodCallException
     */
    private function assertProcessIsAvailable(): void
    {
        if (null === $this->process) {
            throw new \BadMethodCallException('Behat proccess cannot be found. Did you run it before making assertions?');
        }
    }

    /**
     * @throws \RuntimeException
     */
    private static function findPhpBinary(): string
    {
        $phpBinary = (new PhpExecutableFinder())->find();
        if (false === $phpBinary) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        return $phpBinary;
    }
}
