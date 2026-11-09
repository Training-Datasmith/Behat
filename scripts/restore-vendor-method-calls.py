#!/usr/bin/env python3
"""Build snake->camel call map from upstream vs renamed file pairs; apply when third-party hints present."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
UPSTREAM = "5b36302c"

rename_out = subprocess.check_output(
    ["git", "-C", str(ROOT), "show", "--name-status", "--format=", "b5a9dc6b"],
    text=True,
)
path_map: dict[str, str] = {}
for line in rename_out.splitlines():
    m = re.match(r"^R\d*\t(.+)\t(.+)$", line)
    if m:
        path_map[m.group(2)] = m.group(1)

CALL_RE = re.compile(r"->(?P<call>[a-zA-Z][a-zA-Z0-9_]*)\s*\(")


def camel_to_snake(name: str) -> str:
    s1 = re.sub(r"(.)([A-Z][a-z]+)", r"\1_\2", name)
    return re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", s1).lower()


mapping: dict[str, str] = {}
for new_path, old_path in path_map.items():
    if not new_path.endswith(".php"):
        continue
    try:
        old_text = subprocess.check_output(
            ["git", "-C", str(ROOT), "show", f"{UPSTREAM}:{old_path}"],
            text=True,
        )
    except subprocess.CalledProcessError:
        continue
    for call in CALL_RE.findall(old_text):
        if "_" in call or call[0].isupper():
            continue
        snake = camel_to_snake(call)
        if snake != call and "_" in snake:
            prev = mapping.get(snake)
            if prev is not None and prev != call:
                continue
            mapping[snake] = call

VENDOR_HINT = re.compile(
    r"\b(InputInterface|OutputInterface|ContainerBuilder|ArrayInput|InputDefinition|"
    r"InputOption|InputArgument|SymfonyCommand|Application\b|XdebugHandler|EventDispatcher|"
    r"InstalledVersions|Command\b|ParameterBag)\b|Symfony\\|Composer\\|Psr\\|Behat\\Gherkin\\"
)

changed = 0
for path in ROOT.rglob("*.php"):
    if "vendor" in path.parts or path.parts[-1].startswith("restore-"):
        continue
    text = path.read_text(encoding="utf-8")
    if not VENDOR_HINT.search(text):
        continue
    original = text

    def repl(m: re.Match[str]) -> str:
        call = m.group(1)
        if call in mapping:
            return f".{mapping[call]}("
        return m.group(0)

    text = CALL_RE.sub(repl, text)
    if text != original:
        path.write_text(text, encoding="utf-8")
        changed += 1

print(f"Built {len(mapping)} mappings; updated {changed} files")
