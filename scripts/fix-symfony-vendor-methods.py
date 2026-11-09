#!/usr/bin/env python3
"""Restore Symfony/vendor camelCase method names on chained calls in Symfony-aware files."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SCAN = [ROOT / "src", ROOT / "tests"]

CALL_RE = re.compile(r"->(?P<call>[a-z][a-z0-9_]*)\s*\(")
DEF_RE = re.compile(r"\bfunction\s+(?P<name>[a-z][a-z0-9_]*)\s*\(")

ROOT_SRC = ROOT / "src"
METHOD_DEFS: set[str] = set()
for php in ROOT_SRC.rglob("*.php"):
    if "vendor" in php.parts:
        continue
    text = php.read_text(encoding="utf-8", errors="replace")
    METHOD_DEFS.update(m.group("name") for m in DEF_RE.finditer(text))

BEHAT_KEEP = {
    "get_config_key",
    "get_extension_classes",
    "get_extensions",
    "get_extension",
    "activate_extension",
    "initialize_extensions",
    "find_and_sort_tagged_services",
}


def snake_to_camel(name: str) -> str:
    parts = [p for p in name.split("_") if p]
    if not parts:
        return name
    return parts[0] + "".join(p[:1].upper() + p[1:] for p in parts[1:])


def should_touch_file(text: str) -> bool:
    return bool(re.search(r"\buse\s+Symfony\\", text))


def process(text: str) -> str:
    defined = {m.group("name") for m in DEF_RE.finditer(text)}
    replacements: dict[str, str] = {}

    for match in CALL_RE.finditer(text):
        call = match.group("call")
        if "_" not in call or call in defined or call in BEHAT_KEEP or call in METHOD_DEFS:
            continue
        camel = snake_to_camel(call)
        if camel == call:
            continue
        replacements[call] = camel

    if not replacements:
        return text

    def repl(m: re.Match[str]) -> str:
        call = m.group("call")
        if call in replacements:
            return f"->{replacements[call]}("
        return m.group(0)

    return CALL_RE.sub(repl, text)


def main() -> None:
    changed = 0
    for base in SCAN:
        if not base.is_dir():
            continue
        for path in base.rglob("*.php"):
            if "vendor" in path.parts:
                continue
            text = path.read_text(encoding="utf-8")
            if not should_touch_file(text):
                continue
            updated = process(text)
            if updated != text:
                path.write_text(updated, encoding="utf-8")
                changed += 1
    print(f"fix-symfony-vendor-methods: updated {changed} files")


if __name__ == "__main__":
    main()
