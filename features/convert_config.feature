Feature: Convert config
  In order to migrate the existing yaml configuration
  As a developer
  I need to be able to convert this configuration to the new PHP format

  Background:
    Given I initialise the working directory from the "ConvertConfig" fixtures folder
    And I clear the default behat options
    And I provide the following options for all behat invocations:
      | option           | value |
      | --no-colors      |       |
      | --convert-config |       |

  Scenario: Convert empty file
    When I run behat with the following additional options:
      | option   | value      |
      | --config | empty.yaml |
    Then it should pass
    And "empty.php" file should contain:
      """
      <?php

      use Behat\Config\Config;

      return new Config();
      """
    And the "empty.yaml" file should have been removed from the working directory

  Scenario: Convert profiles
    When I run behat with the following additional options:
      | option   | value         |
      | --config | profiles.yaml |
    Then it should pass
    And "profiles.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile(new Profile('default'))
          ->with_profile(new Profile('another'));
      """
    And the "profiles.yaml" file should have been removed from the working directory

  Scenario: Preferred profile
    When I run behat with the following additional options:
      | option   | value                  |
      | --config | preferred_profile.yaml |
    Then it should pass
    And "preferred_profile.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile(new Profile('default'))
          ->with_profile((new Profile('another'))
              ->disable_formatter('pretty'))
          ->with_preferred_profile('another');
      """
    And the "preferred_profile.yaml" file should have been removed from the working directory

  Scenario: Imports
    When I run behat with the following additional options:
      | option   | value        |
      | --config | imports.yaml |
    Then it should pass
    And "imports.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->import('imported.php')
          ->with_profile(new Profile('default'));
    """
    And "imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile(new Profile('another'));
      """
    And the "imports.yaml" file should have been removed from the working directory
    And the "imported.yaml" file should have been removed from the working directory

  Scenario: Multiple Imports
    When I run behat with the following additional options:
      | option   | value                 |
      | --config | multiple_imports.yaml |
    Then it should pass with:
      """
      Starting conversion
      Converting configuration file: multiple_imports.yaml
      Converting configuration file: .%%DS%%imported.yaml
      Converting configuration file: .%%DS%%other_imported.yaml
      Conversion finished
      """
    And "multiple_imports.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->import([
              'imported.php',
              'other_imported.php',
          ])
          ->with_profile(new Profile('default'));
      """
    And "imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile(new Profile('another'));
      """
    And "other_imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile(new Profile('other'));
      """
    And the "multiple_imports.yaml" file should have been removed from the working directory
    And the "imported.yaml" file should have been removed from the working directory
    And the "other_imported.yaml" file should have been removed from the working directory

  Scenario: Suites
    When I run behat with the following additional options:
      | option   | value       |
      | --config | suites.yaml |
    Then it should pass
    And "suites.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_suite(new Suite('one_suite'))
              ->with_suite(new Suite('another_suite')));
      """
    And the "suites.yaml" file should have been removed from the working directory

  Scenario: Suite contexts
    When I run behat with the following additional options:
      | option   | value               |
      | --config | suite_contexts.yaml |
    Then it should pass
    And "suite_contexts.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_suite((new Suite('my_suite'))
                  ->with_contexts(
                      'MyContext',
                      'AnotherContext'
                  )));
      """
    And the "suite_contexts.yaml" file should have been removed from the working directory

  Scenario: Suite contexts with arguments
    When I run behat with the following additional options:
      | option   | value                         |
      | --config | suite_contexts_with_args.yaml |
    Then it should pass
    And "suite_contexts_with_args.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_suite((new Suite('my_suite'))
                  ->add_context('MyContext')
                  ->add_context(
                      'AContextWithPositionalArgs',
                      [
                          'First Arg',
                          'Second Arg',
                      ]
                  )
                  ->add_context(
                      'AContextWithNamedArgs',
                      [
                          'param1' => 'Something',
                          'param2' => 'Else',
                      ]
                  )
                  ->add_context('AnotherContext')));
      """
    And the "suite_contexts_with_args.yaml" file should have been removed from the working directory

  Scenario: Suite paths
    When I run behat with the following additional options:
      | option   | value            |
      | --config | suite_paths.yaml |
    Then it should pass
    And "suite_paths.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_suite((new Suite('my_suite'))
                  ->with_paths(
                      'one.feature',
                      'other.feature'
                  )));
      """
    And the "suite_paths.yaml" file should have been removed from the working directory

  Scenario: Suite filters
    When I run behat with the following additional options:
      | option   | value              |
      | --config | suite_filters.yaml |
    Then it should pass
    And "suite_filters.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Filter\Tag_Filter;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_suite((new Suite('my_suite'))
                  ->with_filter(new Tag_Filter('@run'))));
      """
    And the "suite_filters.yaml" file should have been removed from the working directory

  Scenario: Extensions
    When I run behat with the following additional options:
      | option   | value           |
      | --config | extensions.yaml |
    Then it should pass
    And "extensions.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_extension(new Extension('custom_extension.php', [
                  'property' => 'value',
                  'other_properties' => [
                      'my_tags' => [
                          'one_tag',
                          'another_tag',
                      ],
                  ],
              ])));
      """
    And the "extensions.yaml" file should have been removed from the working directory

  Scenario: Class references for known extensions and contexts
    When  I run behat with the following additional options:
      | option   | value                 |
      | --config | class_references.yaml |
    Then it should pass
    And "class_references.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Profile;
      use Behat\Config\Suite;
      use MyContext;
      use Some\Behat\Extension\ExplicitlyReferencedExtension;
      use Some\ShorthandExtension\Service_Container\ShorthandExtension;
      use test\MyApp\Contexts\MyFirstContext;
      use test\MyApp\Contexts\MySecondContext;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_extension(new Extension('class_references_loader.php'))
              ->with_extension(new Extension(ExplicitlyReferencedExtension::class))
              ->with_extension(new Extension(ShorthandExtension::class))
              ->with_suite((new Suite('named_contexts'))
                  ->with_contexts(
                      'UnknownContext',
                      MyContext::class,
                      MyFirstContext::class,
                      MySecondContext::class
                  ))
              ->with_suite((new Suite('contexts_with_args'))
                  ->add_context('UnknownContext')
                  ->add_context(
                      MyFirstContext::class,
                      [
                          'param1',
                      ]
                  )
                  ->add_context(MySecondContext::class)));
      """
    And the "class_references.yaml" file should have been removed from the working directory

  Scenario: Gherkin filters
    When I run behat with the following additional options:
      | option   | value                |
      | --config | profile_filters.yaml |
    Then it should pass
    And "profile_filters.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Filter\Name_Filter;
      use Behat\Config\Filter\Role_Filter;
      use Behat\Config\Gherkin_Options;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_gherkin_options((new Gherkin_Options())
                  ->with_filter(new Name_Filter('john'))
                  ->with_filter(new Role_Filter('admin'))));
      """
    And the "profile_filters.yaml" file should have been removed from the working directory

  Scenario: Unused definitions
    When I run behat with the following additional options:
      | option   | value                   |
      | --config | unused_definitions.yaml |
    Then it should pass
    And "unused_definitions.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_print_unused_definitions());
      """
    And the "unused_definitions.yaml" file should have been removed from the working directory

  Scenario: Formatters
    When I run behat with the following additional options:
      | option   | value           |
      | --config | formatters.yaml |
    Then it should pass
    And "formatters.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Formatter\Formatter;
      use Behat\Config\Formatter\JSON_Formatter;
      use Behat\Config\Formatter\J_Unit_Formatter;
      use Behat\Config\Formatter\Pretty_Formatter;
      use Behat\Config\Formatter\Progress_Formatter;
      use Behat\Config\Formatter\Show_Output_Option;
      use Behat\Config\Profile;
      use Behat\Testwork\Output\Printer\Factory\Output_Factory;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_formatter((new Pretty_Formatter(paths: false, shortSummary: false))
                  ->with_output_decorated(false))
              ->with_formatter(new Progress_Formatter())
              ->disable_formatter('junit')
              ->with_formatter((new Formatter('custom_formatter', [
                  'other_property' => 'value',
              ]))
                  ->with_output_verbosity(Output_Factory::VERBOSITY_VERBOSE))
              ->with_extension(new Extension('custom_extension.php')))
          ->with_profile((new Profile('with_options'))
              ->with_formatter((new J_Unit_Formatter())
                  ->with_output_path('build/logs/junit'))
              ->with_formatter((new JSON_Formatter())
                  ->with_output_path('build/logs/result.json'))
              ->with_formatter((new Progress_Formatter(showOutput: Show_Output_Option::OnFail))
                  ->with_output_verbosity(Output_Factory::VERBOSITY_VERY_VERBOSE))
              ->with_formatter((new Pretty_Formatter(expand: true, showOutput: Show_Output_Option::No))
                  ->with_output_styles([
                      'failed' => [
                          'white',
                          'red',
                          'blink',
                      ],
                  ])));
      """
    And the "formatters.yaml" file should have been removed from the working directory

  Scenario: path options
    When I run behat with the following additional options:
      | option   | value             |
      | --config | path_options.yaml |
    Then it should pass
    And "path_options.php" file should contain exactly:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile((new Profile('default'))
              ->with_path_options(print_absolute_paths: true))
          ->with_profile((new Profile('with_editor_url'))
              ->with_path_options(editor_url: 'phpstorm://open?file={relPath}&line={line}'))
          ->with_profile((new Profile('with_remove_prefix'))
              ->with_path_options(remove_prefix: [
                  'features/bootstrap/',
                  'features/',
              ]));
      """
    And the "path_options.yaml" file should have been removed from the working directory

  Scenario: Tester options
    When I run behat with the following additional options:
      | option   | value               |
      | --config | tester_options.yaml |
    Then it should pass
    And "tester_options.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Tester_Options;

      return (new Config())
          ->with_profile(new Profile('default'))
          ->with_profile((new Profile('ignore-errors'))
              ->with_tester_options((new Tester_Options())
                  ->with_error_reporting(E_ALL & ~E_DEPRECATED)))
          ->with_profile((new Profile('not-strict'))
              ->with_tester_options((new Tester_Options())
                  ->with_strict_result_interpretation(false)))
          ->with_profile((new Profile('print-deprecations'))
              ->with_tester_options((new Tester_Options())
                  ->with_print_behat_deprecations()))
          ->with_profile((new Profile('fail-on-deprecations'))
              ->with_tester_options((new Tester_Options())
                  ->with_fail_on_behat_deprecations()))
          ->with_profile((new Profile('complete'))
              ->with_tester_options((new Tester_Options())
                  ->with_strict_result_interpretation()
                  ->with_stop_on_failure(false)
                  ->with_skip_all_tests()
                  ->with_error_reporting(E_ALL & ~(E_WARNING | E_NOTICE | E_DEPRECATED))
                  ->with_print_behat_deprecations()
                  ->with_fail_on_behat_deprecations()));
      """
    And the "tester_options.yaml" file should have been removed from the working directory

  Scenario: Full configuration
    And the "MY_SECRET_PASSWORD" environment variable is set to "sesame"
    When I run behat with the following additional options:
      | option   | value                   |
      | --config | full_configuration.yaml |
    Then it should pass
    And "full_configuration.php" file should contain exactly:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Filter\Name_Filter;
      use Behat\Config\Filter\Role_Filter;
      use Behat\Config\Filter\Tag_Filter;
      use Behat\Config\Formatter\Formatter;
      use Behat\Config\Formatter\Pretty_Formatter;
      use Behat\Config\Formatter\Progress_Formatter;
      use Behat\Config\Gherkin_Options;
      use Behat\Config\Profile;
      use Behat\Config\Suite;
      use Behat\Config\Tester_Options;
      use Behat\Gherkin\GherkinCompatibilityMode;
      use Behat\Testwork\Output\Printer\Factory\Output_Factory;

      return (new Config())
          ->import('imported.php')
          ->with_profile((new Profile('default'))
              ->with_formatter((new Pretty_Formatter(paths: false, shortSummary: false))
                  ->with_output_decorated(false))
              ->with_formatter(new Progress_Formatter())
              ->disable_formatter('junit')
              ->with_formatter((new Formatter('custom_formatter', [
                  'other_property' => 'value',
              ]))
                  ->with_output_verbosity(Output_Factory::VERBOSITY_VERBOSE))
              ->with_gherkin_options((new Gherkin_Options())
                  ->with_cache_dir('/tmp/gherkin-cache')
                  ->with_compatibility_mode(GherkinCompatibilityMode::GHERKIN_32)
                  ->with_filter(new Name_Filter('john'))
                  ->with_filter(new Role_Filter('admin')))
              ->with_print_unused_definitions()
              ->with_path_options(
                  print_absolute_paths: true,
                  editor_url: 'phpstorm://open?file={relPath}&line={line}',
                  remove_prefix: [
                      'features/bootstrap/',
                      'features/',
                  ]
              )
              ->with_tester_options((new Tester_Options())
                  ->with_strict_result_interpretation()
                  ->with_print_behat_deprecations()
                  ->with_fail_on_behat_deprecations())
              ->with_extension(new Extension('custom_extension.php'))
              ->with_suite((new Suite('my_suite'))
                  ->add_context(
                      'MyContext',
                      [
                          'password' => '%env(MY_SECRET_PASSWORD)%',
                      ]
                  )
                  ->with_paths('one.feature')
                  ->with_filter(new Tag_Filter('@run'))))
          ->with_profile((new Profile('other'))
              ->disable_formatter('pretty')
              ->with_tester_options((new Tester_Options())
                  ->with_error_reporting(E_ERROR)))
          ->with_preferred_profile('other');
      """
    And "imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->with_profile(new Profile('another'));
      """
    And the "full_configuration.yaml" file should have been removed from the working directory
    And the "imported.yaml" file should have been removed from the working directory
