Feature: Suites
  In order to use specific set of contexts against specific set of features in single run
  As a feature tester
  I need to be able to use suites

  Background:
    Given I initialise the working directory from the "Suite" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: One feature, two contexts
    When I run "behat --config=behat-two-suites.php"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # First_Context::iHaveApples()
          When I ate 1 apple          # First_Context::iAteApples()
          Then I should have 2 apples # First_Context::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # Second_Context::iHaveApples()
          When I ate 1 apple          # Second_Context::iAteApples()
          Then I should have 2 apples # Second_Context::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Two contexts, two features
    When I run "behat --config=behat-two-features.php"
    Then it should pass with:
      """
      Feature: Apples story #1
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/first/my.feature:6
          Given I have 3 apples       # First_Context::iHaveApples()
          When I ate 1 apple          # First_Context::iAteApples()
          Then I should have 2 apples # First_Context::iShouldHaveApples()

      Feature: Apples story #2
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry    # features/second/their.feature:6
          Given I have 30 apples       # Second_Context::iHaveApples()
          When I ate 10 apple          # Second_Context::iAteApples()
          Then I should have 20 apples # Second_Context::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Suite with `paths` set to string instead of an array
    When I run "behat --config=behat-invalid-paths.yml"
    Then it should fail with:
      """
      `paths` setting of the "first" suite is expected to be an array, string given.
      """

  Scenario: Role-based suites
    When I run "behat --config=behat-role-filters.php"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/little_kid.feature:6
          Given I have 3 apples       # Little_Kid_Context::iHaveApples()
          When I ate 1 apple          # Little_Kid_Context::iAteApples()
          Then I should have 2 apples # Little_Kid_Context::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/big_brother.feature:6
          Given I have 15 apples      # Big_Brother_Context::iHaveApples()
          When I ate 10 apple         # Big_Brother_Context::iAteApples()
          Then I should have 5 apples # Big_Brother_Context::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Narrative-based suites
    When I run "behat --config=behat-narrative-filters.php"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/little_kid.feature:6
          Given I have 3 apples       # Little_Kid_Context::iHaveApples()
          When I ate 1 apple          # Little_Kid_Context::iAteApples()
          Then I should have 2 apples # Little_Kid_Context::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/big_brother.feature:6
          Given I have 15 apples      # Big_Brother_Context::iHaveApples()
          When I ate 10 apple         # Big_Brother_Context::iAteApples()
          Then I should have 5 apples # Big_Brother_Context::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Running single suite
    When I run "behat --config=behat-role-filters.php -s big_brother"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/big_brother.feature:6
          Given I have 15 apples      # Big_Brother_Context::iHaveApples()
          When I ate 10 apple         # Big_Brother_Context::iAteApples()
          Then I should have 5 apples # Big_Brother_Context::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Running suite with a hyphen in suite name
    When I run "behat --config=behat-hyphens.php --suite suite-with-hyphens"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/little_kid.feature:6
          Given I have 3 apples       # Little_Kid_Context::iHaveApples()
          When I ate 1 apple          # Little_Kid_Context::iAteApples()
          Then I should have 2 apples # Little_Kid_Context::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """
