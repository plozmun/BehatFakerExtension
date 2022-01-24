# Behat Faker Extension

Faker PHP integration with Behat Gherkin language

## Instalation

1. Require this extension using Composer:
```shell script
composer require --dev plozmun/behat-faker-extension
```

2. Enable it within your Behat configuration:

```yaml
# behat.yaml.dist / behat.yaml

default:
    extensions:
        Plozmun\FakerExtension:
            locale: 'es_es' # Optional to enable locale functions
```

## Usage 

Add your PHP Faker function between braces: Ej: `{{firsName}}`

[Faker PHP Documentation](https://fakerphp.github.io/)

```gherkin
Feature: Create a Book
  In order to create a new book
  As a admin user
  I need to be able to create a book

  Scenario: Send post to create a new book
    When I add "Content-Type" header equal to "application/json"
    When I send a "POST" request to "/api/v1/book/{{ean13}}" with body:
    """
    {
      "author": {
          "firsName": "{{firstName}}",
          "lastName": "{{lastName}}"
      },
      "title": "{{sentence}}",
      "createdAt": "{{year}}-{{month}}-{{dayOfMonth}}
    }
    """
    And the response status code should be 200
```