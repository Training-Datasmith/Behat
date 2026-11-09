Feature: Path filters
  In order to run only needed features
  As a Behat user
  I need Behat to support path(s) filtering

  Background:
    Given I initialise the working directory from the "PathFilters" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value   |
      | --no-colors |         |
      | --format    | pretty  |

  Scenario: First feature file only
    When I run "behat features/a/feature1.feature"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # Feature_Context::someSlowStepN()
          And Some normal step N13 # Feature_Context::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # Feature_Context::someFastStepN()

      2 scenarios (2 passed)
      3 steps (3 passed)
      """

  Scenario: Second feature file only
    When I run "behat features/a/feature2.feature"
    Then it should pass with:
      """
      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # Feature_Context::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # Feature_Context::someSlowStepN()
          And Some fast step N23   # Feature_Context::someFastStepN()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Both feature files
    When I run "behat features/a/feature1.feature features/a/feature2.feature"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # Feature_Context::someSlowStepN()
          And Some normal step N13 # Feature_Context::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # Feature_Context::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # Feature_Context::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # Feature_Context::someSlowStepN()
          And Some fast step N23   # Feature_Context::someFastStepN()

      3 scenarios (3 passed)
      6 steps (6 passed)
      """

    Scenario: Single nested directory
      When I run "behat features/a"
      Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # Feature_Context::someSlowStepN()
          And Some normal step N13 # Feature_Context::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # Feature_Context::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # Feature_Context::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # Feature_Context::someSlowStepN()
          And Some fast step N23   # Feature_Context::someFastStepN()

      3 scenarios (3 passed)
      6 steps (6 passed)
      """

  Scenario: Directory with nested directories
    When I run "behat features"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # Feature_Context::someSlowStepN()
          And Some normal step N13 # Feature_Context::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # Feature_Context::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # Feature_Context::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # Feature_Context::someSlowStepN()
          And Some fast step N23   # Feature_Context::someFastStepN()

      Feature: Third Feature

        Scenario: First Scenario   # features/b/feature1.feature:3
          Given Some fast step N14 # Feature_Context::someFastStepN()

        Scenario: Second Scenario  # features/b/feature1.feature:6
          Given Some fast step N14 # Feature_Context::someFastStepN()
          And Some fast step N14   # Feature_Context::someFastStepN()

      5 scenarios (5 passed)
      9 steps (9 passed)
      """

    Scenario: Single scenario from single feature file
      When I run "behat features/a/feature1.feature:7"
      Then it should pass with:
      """
      Feature: First Feature

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # Feature_Context::someFastStepN()

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Single scenario from each feature file
    When I run "behat features/a/feature1.feature:7 features/a/feature2.feature:6"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # Feature_Context::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # Feature_Context::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # Feature_Context::someSlowStepN()
          And Some fast step N23   # Feature_Context::someFastStepN()

      2 scenarios (2 passed)
      4 steps (4 passed)
      """
