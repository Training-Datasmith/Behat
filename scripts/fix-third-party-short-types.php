<?php

declare(strict_types=1);

/**
 * Fix mistaken Studly_Snake short type names for third-party symbols in repo PHP trees.
 */
$replacements = [
    'Input_Interface' => 'InputInterface',
    'Output_Interface' => 'OutputInterface',
    'Container_Builder' => 'ContainerBuilder',
    'Symfony_Command' => 'SymfonyCommand',
    'Array_Input' => 'ArrayInput',
    'Input_Definition' => 'InputDefinition',
    'Input_Option' => 'InputOption',
    'Input_Argument' => 'InputArgument',
    'Array_Node_Definition' => 'ArrayNodeDefinition',
    'Node_Builder' => 'NodeBuilder',
    'Tree_Builder' => 'TreeBuilder',
    'Yaml_Reference_Dumper' => 'YamlReferenceDumper',
    'Node_Interface' => 'NodeInterface',
    'Event_Subscriber_Interface' => 'EventSubscriberInterface',
    'Compiler_Pass_Interface' => 'CompilerPassInterface',
    'Tagged_Container_Interface' => 'TaggedContainerInterface',
    'Parameter_Bag' => 'ParameterBag',
    'Event_Dispatcher' => 'EventDispatcher',
    'Event_Dispatcher_Interface' => 'EventDispatcherInterface',
    'Table_Node' => 'TableNode',
    'Py_String_Node' => 'PyStringNode',
    'Feature_Node' => 'FeatureNode',
    'Step_Node' => 'StepNode',
    'Outline_Node' => 'OutlineNode',
    'Example_Table_Node' => 'ExampleTableNode',
    'Example_Node' => 'ExampleNode',
    'Scenario_Node' => 'ScenarioNode',
    'Scenario_Like_Interface' => 'ScenarioLikeInterface',
    'Background_Node' => 'BackgroundNode',
    'Argument_Interface' => 'ArgumentInterface',
    'Step_Container_Interface' => 'StepContainerInterface',
    'Gherkin_Compatibility_Mode' => 'GherkinCompatibilityMode',
    'Node_Exception' => 'NodeException',
    'Installed_Versions' => 'InstalledVersions',
    'Xdebug_Handler' => 'XdebugHandler',
];

$symfonyMethodMap = [
    'do_run' => 'doRun',
    'get_command_name' => 'getCommandName',
    'configure_io' => 'configureIO',
];

$roots = ['src', 'tests', 'features', 'bin'];
$changed = 0;

foreach ($roots as $root) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../' . $root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }
        $original = $content;

        $namespaceSensitive = ['Event_Dispatcher' => true, 'Event_Dispatcher_Interface' => true];
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            $isDecl = preg_match('/^\s*(namespace|use)\s/', $line) === 1;
            foreach ($replacements as $from => $to) {
                if ($isDecl && isset($namespaceSensitive[$from])) {
                    continue;
                }
                $line = preg_replace('/\b' . preg_quote($from, '/') . '\b/', $to, $line) ?? $line;
            }
            $lines[$i] = $line;
        }
        $content = implode("\n", $lines);
        $content = preg_replace('/\bInstalledVersions::get_version\s*\(/', 'InstalledVersions::getVersion(', $content) ?? $content;

        if (str_contains($path, 'Testwork/Cli/Application.php') || str_contains($path, 'Testwork/Cli/Command.php')) {
            foreach ($symfonyMethodMap as $from => $to) {
                $content = preg_replace('/\bfunction\s+' . preg_quote($from, '/') . '\b/', 'function ' . $to, $content) ?? $content;
            }
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            ++$changed;
        }
    }
}

fwrite(STDOUT, "Updated {$changed} files\n");
