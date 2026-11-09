Feature: attributes
  In order to keep annotations shorter and faster to parse
  As a tester
  I need to be able to use PHP Attributes

  Background:
    Given I initialise the working directory from the "Attributes" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Step Attributes
    When I run behat with the following additional options:
      | option                | value           |
      | --profile             | step_attributes |
      | features/some.feature |                 |
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # Step_Attributes_Context::iHaveFruit()
          When I eat 1 apple                   # Step_Attributes_Context::iEatFruit()
          Then I should have 2 apples          # Step_Attributes_Context::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # Step_Attributes_Context::iHaveFruit()
          When I eat 1 banana                   # Step_Attributes_Context::iEatFruit()
          Then I should have 2 bananas          # Step_Attributes_Context::iShouldHaveFruit()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Hook Feature Hook Attributes
    When I run behat with the following additional options:
      | option                | value         |
      | --profile             | feature_hooks |
      | features/some.feature |               |
    Then it should pass with:
      """
      ┌─ @Before_Feature # Feature_Hook_Attributes_Context::beforeFeature()
      │
      │  = BEFORE FEATURE =
      │
      ┌─ @Before_Feature Fruit story # Feature_Hook_Attributes_Context::beforeFruitStory()
      │
      │  = BEFORE FRUIT STORY =
      │
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # Feature_Hook_Attributes_Context::iHaveFruit()
          When I eat 1 apple                   # Feature_Hook_Attributes_Context::iEatFruit()
          Then I should have 2 apples          # Feature_Hook_Attributes_Context::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # Feature_Hook_Attributes_Context::iHaveFruit()
          When I eat 1 banana                   # Feature_Hook_Attributes_Context::iEatFruit()
          Then I should have 2 bananas          # Feature_Hook_Attributes_Context::iShouldHaveFruit()

      │
      │  = AFTER FEATURE =
      │
      └─ @After_Feature # Feature_Hook_Attributes_Context::afterFeature()

      │
      │  = AFTER FRUIT STORY =
      │
      └─ @After_Feature Fruit story # Feature_Hook_Attributes_Context::afterFruitStory()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Hook Scenario Hook Attributes
    When I run behat with the following additional options:
      | option                          | value          |
      | --profile                       | scenario_hooks |
      | features/some-with-tags.feature |                |
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        ┌─ @Before_Scenario # Scenario_Hook_Attributes_Context::beforeScenario()
        │
        │  = BEFORE SCENARIO =
        │
        Scenario: I'm little hungry for apples # features/some-with-tags.feature:6
          Given I have 3 apples                # Scenario_Hook_Attributes_Context::iHaveFruit()
          When I eat 1 apple                   # Scenario_Hook_Attributes_Context::iEatFruit()
          Then I should have 2 apples          # Scenario_Hook_Attributes_Context::iShouldHaveFruit()
        │
        │  = AFTER SCENARIO =
        │
        └─ @After_Scenario # Scenario_Hook_Attributes_Context::afterScenario()

        ┌─ @Before_Scenario # Scenario_Hook_Attributes_Context::beforeScenario()
        │
        │  = BEFORE SCENARIO =
        │
        ┌─ @Before_Scenario @bananas # Scenario_Hook_Attributes_Context::beforeBananas()
        │
        │  = BEFORE BANANAS =
        │
        @bananas
        Scenario: I'm little hungry for bananas # features/some-with-tags.feature:12
          Given I have 3 bananas                # Scenario_Hook_Attributes_Context::iHaveFruit()
          When I eat 1 banana                   # Scenario_Hook_Attributes_Context::iEatFruit()
          Then I should have 2 bananas          # Scenario_Hook_Attributes_Context::iShouldHaveFruit()
        │
        │  = AFTER SCENARIO =
        │
        └─ @After_Scenario # Scenario_Hook_Attributes_Context::afterScenario()
        │
        │  = AFTER BANANAS =
        │
        └─ @After_Scenario @bananas # Scenario_Hook_Attributes_Context::afterBananas()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Hook Suite Hook Attributes
    When I run behat with the following additional options:
      | option                  | value       |
      | --profile               | suite_hooks |
      | features/apples.feature |             |
    Then it should pass with:
      """
      ┌─ @Before_Suite # Suite_Hook_Attributes_Context::beforeSuite()
      │
      │  = BEFORE SUITE =
      │

      ┌─ @Before_Suite apples # Suite_Hook_Attributes_Context::beforeSuiteApples()
      │
      │  = BEFORE APPLES =
      │

      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/apples.feature:6
          Given I have 3 apples                # Suite_Hook_Attributes_Context::iHaveFruit()
          When I eat 1 apple                   # Suite_Hook_Attributes_Context::iEatFruit()
          Then I should have 2 apples          # Suite_Hook_Attributes_Context::iShouldHaveFruit()

      │
      │  = AFTER SUITE =
      │
      └─ @After_Suite # Suite_Hook_Attributes_Context::afterSuite()

      │
      │  = AFTER APPLES =
      │
      └─ @After_Suite apples # Suite_Hook_Attributes_Context::afterSuiteApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Hook Step Hook Attributes
    When I run behat with the following additional options:
      | option                | value      |
      | --profile             | step_hooks |
      | features/some.feature |            |
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          ┌─ @Before_Step # Step_Hook_Attributes_Context::beforeStep()
          │
          │  = BEFORE STEP =
          │
          ┌─ @Before_Step I have 3 apples # Step_Hook_Attributes_Context::beforeApples()
          │
          │  = BEFORE APPLES =
          │
          Given I have 3 apples                # Step_Hook_Attributes_Context::iHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @After_Step # Step_Hook_Attributes_Context::afterStep()
          │
          │  = AFTER APPLES =
          │
          └─ @After_Step I have 3 apples # Step_Hook_Attributes_Context::afterApples()
          ┌─ @Before_Step # Step_Hook_Attributes_Context::beforeStep()
          │
          │  = BEFORE STEP =
          │
          When I eat 1 apple                   # Step_Hook_Attributes_Context::iEatFruit()
          │
          │  = AFTER STEP =
          │
          └─ @After_Step # Step_Hook_Attributes_Context::afterStep()
          ┌─ @Before_Step # Step_Hook_Attributes_Context::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Then I should have 2 apples          # Step_Hook_Attributes_Context::iShouldHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @After_Step # Step_Hook_Attributes_Context::afterStep()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          ┌─ @Before_Step # Step_Hook_Attributes_Context::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Given I have 3 bananas                # Step_Hook_Attributes_Context::iHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @After_Step # Step_Hook_Attributes_Context::afterStep()
          ┌─ @Before_Step # Step_Hook_Attributes_Context::beforeStep()
          │
          │  = BEFORE STEP =
          │
          When I eat 1 banana                   # Step_Hook_Attributes_Context::iEatFruit()
          │
          │  = AFTER STEP =
          │
          └─ @After_Step # Step_Hook_Attributes_Context::afterStep()
          ┌─ @Before_Step # Step_Hook_Attributes_Context::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Then I should have 2 bananas          # Step_Hook_Attributes_Context::iShouldHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @After_Step # Step_Hook_Attributes_Context::afterStep()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """
