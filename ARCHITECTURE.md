# Behat Architecture

## Purpose

A PHP BDD (Behaviour-Driven Development) testing framework that links Gherkin
`.feature` files to PHP step definitions, enabling executable specifications.

## Directory Structure

```
src/Behat/
  Behat.php                   — application entry point / kernel factory
  ApplicationFactory.php      — creates the Symfony Console Application
  Config/                     — YAML/PHP configuration loading and processing
  Context/                    — step definition contexts and their loaders
    Context.php               — marker interface
    SnippetAcceptingContext.php — context that can accept generated snippets
    Annotation/               — annotation-based step definition reader
    Attribute/                — PHP 8 attribute-based step definition reader
    Initializer/              — context argument injection (Closure, DI, etc.)
  Definition/                 — step definition registry and matching
    Pattern/                  — pattern matching strategies (Regex, Turnip)
  EventDispatcher/            — event classes and dispatcher wiring
  Extension/                  — Behat extension system
    Extension.php             — extension interface
  Formatter/                  — output formatters (pretty, progress, JUnit, JSON)
  Gherkin/                    — Gherkin parser integration
  Hook/                       — before/after scenario/step/suite hooks
  Keyword/                    — locale-specific Gherkin keywords (i18n)
  Output/                     — output formatters and printers
  Snippet/                    — step snippet generation (PHPUnit, Turnip)
  Suite/                      — test suite configuration
  Testwork/                   — reusable testing framework layer
    Argument/                 — argument transformation system
    Cache/                    — file-based suite cache
    Call/                     — step call + result objects
    Cli/                      — CLI controllers
    Environment/              — test environment management
    EventDispatcher/          — Symfony EventDispatcher integration
    Exception/                — exception + error formatting
    Filesystem/               — file utilities
    Hook/                     — hook call dispatching
    Output/                   — output manager abstraction
    Specification/             — test case specifications (Feature files)
    Suite/                    — test suite wiring
    Tester/                   — step / scenario / feature / suite testers
bin/
  behat                       — CLI entry point
features/                     — Behat's own integration tests (.feature files)
```

## Key Design Decisions

- **Extension system**: almost all functionality is provided via extensions
  loaded in `behat.yml`; the core framework is intentionally minimal.
  Extensions hook into the DI container and event system.
- **Dual step-definition styles**: steps can be defined using PHP 8 attributes
  (`#[Given]`, `#[When]`, `#[Then]`) or Doctrine Annotations; both are read by
  separate drivers.
- **Event-driven execution**: the test runner emits events at each lifecycle
  point (suite start, feature start, scenario start, step call, etc.) enabling
  formatters and hooks to observe execution non-invasively.
- **Pattern matching strategies**: step patterns can be plain Turnip-style tokens
  (`I have :n items`) or full regular expressions, selected per-suite.
- **Testwork layer**: the reusable `Testwork` sub-namespace provides a
  framework-agnostic testing harness on which Behat's Gherkin-specific layer sits.

## Extension Points

- Implement `ExtensionInterface` to add new CLI commands, formatters, or
  context initializers.
- Implement `ArgumentTransformation` to convert step arguments to domain objects.
- Implement a custom `PatternTransformer` for bespoke step matching syntax.
- Add custom hooks via `#[BeforeScenario]` / `#[AfterScenario]` attributes.

## Dependency Flow

```
bin/behat
  └── ApplicationFactory → Symfony Console Application
        └── ServiceContainer (DI) ← Extensions
              ├── Gherkin parser → FeatureNode / ScenarioNode
              ├── ContextFactory + step Definition registry
              ├── Tester chain  (Suite → Feature → Scenario → Step)
              └── Formatters (Pretty, JUnit, JSON, Progress, …)
```
