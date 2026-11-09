Feature: Handle failures when generating snippets
  In order to avoid problems when a step cannot be automatically converted to a snippet pattern
  As a feature developer
  I need Behat to report any steps that need to be created manually or with a different pattern type

  Background:
    Given I initialise the working directory from the SnippetGenerationFailures fixtures folder
    And I provide the following options for all behat invocations:
      | option            | value              |
      | --no-colors       |                    |
      | --format-settings | '{"paths": false}' |
      | --format          | progress           |
      | --snippets-for    | Feature_Context     |

  Scenario: See warnings when printing snippets
    When I run behat with the following additional options:
      | option          | value  |
      | --snippets-type | turnip |
    Then it should pass with:
    """
    UU

    1 scenario (1 undefined)
    2 steps (2 undefined)

    --- Feature_Context has missing steps. Define them with these snippets:

        #[Given('a step with :arg1 inside a quoted parameter')]
        public function aStepWithInsideAQuotedParameter($arg1): void
        {
            throw new Pending_Exception();
        }

    --- Don't forget these 2 use statements:

        use Behat\Behat\Tester\Exception\Pending_Exception;
        use Behat\Step\Given;

    --- Could not automatically generate snippets matching the following steps:
        (try using --snippets-type=regex, or manually define the step)

        - a step with (Parentheses) in the actual step text
    """

  Scenario: See warnings when appending snippets
    When I run behat with the following additional options:
      | option            | value  |
      | --snippets-type   | turnip |
      | --append-snippets |        |
    Then it should pass with:
    """
    UU

    1 scenario (1 undefined)
    2 steps (2 undefined)

    u features/bootstrap/Feature_Context.php - `a step with "(parentheses)" inside a quoted parameter` definition added

    --- Could not automatically generate snippets matching the following steps:
        (try using --snippets-type=regex, or manually define the step)

        - a step with (Parentheses) in the actual step text
    """
    And  "features/bootstrap/Feature_Context.php" file should contain:
    """
    <?php

    use Behat\Step\Given;
    use Behat\Behat\Tester\Exception\Pending_Exception;
    use Behat\Behat\Context\Context;

    class Feature_Context implements Context
    {

        #[Given('a step with :arg1 inside a quoted parameter')]
        public function aStepWithInsideAQuotedParameter($arg1): void
        {
            throw new Pending_Exception();
        }
    }
    """
