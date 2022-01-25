Feature: Running application with faker parameters

  Background:
    Given a working Symfony application
    And a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - App\Tests\FakeContext
            extensions:
                Plozmun\FakerExtension:
                    locale: es_es
        """
    And a feature file containing:
        """
        Feature:
            Scenario:
                Then the passed faker parameter "{{firstName}}" should not contains "firstName"

            Scenario:
                Then the passed faker parameter "{{dateTimeBetween('-1 week', '+1 week').format('Y-m-d')}}" should be a string date
        """
    And a context file "tests/FakeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class FakeContext implements Context {

            /** @Then the passed faker parameter :foo should not contains :bar */
            public function parameterShouldNotContains(string $foo, string $bar): void {
                assert(strpos($foo, $bar) === false);
            }

            /** @Then the passed faker parameter :date should be a string date */
            public function parameterShouldBeDate(string $date): void {
                $object = \DateTime::createFromFormat('Y-m-d', $date);
                assert($object->format("Y-m-d") === $date);
            }
        }
        """

  Scenario: Running application
    When I run Behat
    Then it should pass
