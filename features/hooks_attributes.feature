Feature: Hooks Attributes
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks using attributes

  Background:
    Given I initialise the working directory from the "Hooks" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value                |
      | --no-colors |                      |
      | --config    | behat-attributes.php |

  Scenario: Basic hooks execution
    When I run behat with the following additional options:
      | option                | value |
      | features/test.feature |       |
    Then it should pass with:
      """
      ┌─ @Before_Feature # Feature_Context_Attributes::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:             # features/test.feature:2
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 50 # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                 # features/test.feature:4
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given I have entered 12 # Feature_Context_Attributes::iHaveEntered()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 12     # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @thirty
        Scenario:                # features/test.feature:9
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given I must have 30   # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          When I have entered 23 # Feature_Context_Attributes::iHaveEntered()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 23    # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @100 @thirty
        Scenario:               # features/test.feature:14
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given I must have 30  # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          When I have entered 1 # Feature_Context_Attributes::iHaveEntered()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 100  # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @filtered
        Scenario:                                                # features/test.feature:20
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have a scenario filter value of "filtered" # Feature_Context_Attributes::iMustHaveScenarioFilter()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @~filtered
        Scenario:                                                 # features/test.feature:24
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have a scenario filter value of "~filtered" # Feature_Context_Attributes::iMustHaveScenarioFilter()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @After_Feature # Feature_Context_Attributes::doSomethingAfterFeature()
      
      6 scenarios (6 passed)
      11 steps (11 passed)
      """

  Scenario: Hooks on tagged features
    When I run behat with the following additional options:
      | option                | value |
      | features/some.feature |       |
    Then it should pass with:
      """
      ┌─ @Before_Feature # Feature_Context_Attributes::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      ┌─ @Before_Feature @someFeature # Feature_Context_Attributes::doSomethingBeforeSomeFeature()
      │
      │  = do something before SOME feature
      │
      @someFeature
      Feature:
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:             # features/some.feature:3
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 50 # Feature_Context_Attributes::iMustHave()
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @After_Feature # Feature_Context_Attributes::doSomethingAfterFeature()
      
      │
      │  = do something after SOME feature
      │
      └─ @After_Feature @someFeature # Feature_Context_Attributes::doSomethingAfterSomeFeature()
      
      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Hooks output
    When I run behat with the following additional options:
      | option                        | value |
      | features/hooks-output.feature |       |
    Then it should fail with:
      """
      ┌─ @Before_Feature # Feature_Context_Attributes::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/hooks-output.feature:2
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given passing step # Feature_Context_Attributes::passingStep()
            │ Is passing
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/hooks-output.feature:5
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given failing step # Feature_Context_Attributes::failingStep()
            Failing (RuntimeException)
          │
          │  Is passing
          │
          └─ @After_Step # Feature_Context_Attributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                              # features/hooks-output.feature:8
          ┌─ @Before_Step # Feature_Context_Attributes::passingBeforeStep()
          │
          │  Is passing
          │
          ┌─ @Before_Step passing step with failing hook # Feature_Context_Attributes::failingBeforeStep()
          │
          ╳  Is failing (RuntimeException)
          │
          Given passing step with failing hook # Feature_Context_Attributes::passingStep()
        │
        │  Is passing
        │
        └─ @After_Scenario # Feature_Context_Attributes::passingAfterScenarioHook()
      
        ┌─ @Before_Scenario # Feature_Context_Attributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        ┌─ @Before_Scenario @failing-before-hook # Feature_Context_Attributes::failingBeforeScenarioHook()
        │
        ╳  Is failing (RuntimeException)
        │
        @failing-before-hook
        Scenario:            # features/hooks-output.feature:12
          Given passing step # Feature_Context_Attributes::passingStep()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @After_Feature # Feature_Context_Attributes::doSomethingAfterFeature()
      
      --- Failed hooks:
      
          Before_Step passing step with failing hook "features/hooks-output.feature:9" # Feature_Context_Attributes::failingBeforeStep()
          Before_Scenario @failing-before-hook "features/hooks-output.feature:12" # Feature_Context_Attributes::failingBeforeScenarioHook()
      
      --- Skipped scenarios:
      
          features/hooks-output.feature:12
      
      --- Failed scenarios:
      
          features/hooks-output.feature:5 (on line 6)
          features/hooks-output.feature:8
      
      4 scenarios (1 passed, 2 failed, 1 skipped)
      4 steps (1 passed, 1 failed, 2 skipped)
      """
