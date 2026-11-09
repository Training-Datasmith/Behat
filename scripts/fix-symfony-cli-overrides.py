#!/usr/bin/env python3
"""Restore Symfony Console override signatures in Testwork Application/Command."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
APP = ROOT / "src/Behat/Testwork/Cli/Application.php"
if not APP.is_file():
    raise SystemExit("Application.php missing")

text = APP.read_text(encoding="utf-8")
replacements = [
    ("function get_default_input_definition", "function getDefaultInputDefinition"),
    ("function do_run", "function doRun"),
    ("parent::do_run", "parent::doRun"),
    ("function get_default_commands", "function getDefaultCommands"),
    ("parent::get_default_commands", "parent::getDefaultCommands"),
    ("parent::configure_io", "parent::configureIO"),
    ("has_parameter_option", "hasParameterOption"),
    ("get_parameter_option", "getParameterOption"),
    ("set_parameter(", "setParameter("),
    ("set_decorated(", "setDecorated("),
    ("set_persistent(", "setPersistent("),
    ("add_commands(", "addCommands("),
    ("return $this->get_name();", "return $this->getName();"),
    ("$this->get_name()", "$this->getName()"),
]
for old, new in replacements:
    text = text.replace(old, new)
APP.write_text(text, encoding="utf-8")

DEBUG = ROOT / "src/Behat/Testwork/Cli/Debug_Command.php"
if DEBUG.is_file():
    dbg = DEBUG.read_text(encoding="utf-8")
    dbg = dbg.replace("$this->application->get_name()", "$this->application->getName()")
    dbg = dbg.replace("$this->application->get_version()", "$this->application->getVersion()")
    DEBUG.write_text(dbg, encoding="utf-8")

LOADER = ROOT / "src/Behat/Testwork/Cli/Application.php"
if LOADER.is_file():
    loader_fix = LOADER.read_text(encoding="utf-8")
    loader_fix = loader_fix.replace(
        "setConfigurationFilePath(", "set_configuration_file_path(",
    )
    LOADER.write_text(loader_fix, encoding="utf-8")

DISP = ROOT / "src/Behat/Testwork/Event_Dispatcher/Testwork_Event_Dispatcher.php"
if DISP.is_file():
    d = DISP.read_text(encoding="utf-8")
    d = d.replace("function get_listeners", "function getListeners")
    d = d.replace("parent::get_listeners", "parent::getListeners")
    DISP.write_text(d, encoding="utf-8")

print("Symfony CLI overrides patched")
