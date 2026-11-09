#!/usr/bin/env python3
"""Restore third-party FQCN segments mistakenly Studly_Snake'd during rename."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

THIRD_PARTY_ROOTS = frozenset(
    {
        "Symfony",
        "Composer",
        "PHPUnit",
        "Psr",
        "Doctrine",
        "PhpParser",
        "Php_Parser",
        "Sebastian",
        "Opis",
        "Prophecy",
        "PharIo",
        "MyCLabs",
        "TheSeer",
    }
)


def studly_snake_to_pascal(segment: str) -> str:
    if "_" not in segment:
        return segment
    return "".join(part[:1].upper() + part[1:] for part in segment.split("_") if part)


def fix_fqcn(fqcn: str) -> str:
    parts = fqcn.split("\\")
    if not parts:
        return fqcn

    if parts[0] in THIRD_PARTY_ROOTS:
        return "\\".join(studly_snake_to_pascal(p) for p in parts)

    if len(parts) >= 2 and parts[0] == "Behat" and parts[1] == "Gherkin":
        return "\\".join(parts[:2] + [studly_snake_to_pascal(p) for p in parts[2:]])

    return fqcn


FQCN_PATTERN = re.compile(
    r"\b("
    r"Symfony\\[A-Za-z0-9_\\]+"
    r"|Composer\\[A-Za-z0-9_\\]+"
    r"|PHPUnit\\[A-Za-z0-9_\\]+"
    r"|Psr\\[A-Za-z0-9_\\]+"
    r"|Doctrine\\[A-Za-z0-9_\\]+"
    r"|PhpParser\\[A-Za-z0-9_\\]+"
    r"|Sebastian\\[A-Za-z0-9_\\]+"
    r"|Opis\\[A-Za-z0-9_\\]+"
    r"|Behat\\Gherkin\\[A-Za-z0-9_\\]+"
    r")\b"
)


def fix_base_extends(content: str) -> str:
    aliases: dict[str, str] = {}
    for match in re.finditer(r"^use\s+[^;]+\s+as\s+(\w+)\s*;", content, re.M):
        aliases[match.group(1)] = match.group(1)

    for alias in aliases:
        snake = re.sub(r"([a-z])([A-Z])", r"\1_\2", alias)
        if snake != alias:
            content = re.sub(rf"\bextends\s+{re.escape(snake)}\b", f"extends {alias}", content)
            content = re.sub(rf"\bimplements\s+{re.escape(snake)}\b", f"implements {alias}", content)

    # Known mistakes from partial rename tooling
    known = {
        "Base_Factory": "BaseFactory",
        "Base_Application": "BaseApplication",
        "Base_Command": "BaseCommand",
        "Base_Extension": "BaseExtension",
        "Base_Output_Formatter": "BaseOutputFormatter",
    }
    for wrong, right in known.items():
        content = re.sub(rf"\bextends\s+{wrong}\b", f"extends {right}", content)
        content = re.sub(rf",\s*{wrong}\b", f", {right}", content)

    return content


def process_file(path: Path) -> bool:
    original = path.read_text(encoding="utf-8")
    content = FQCN_PATTERN.sub(lambda m: fix_fqcn(m.group(1)), original)
    content = fix_base_extends(content)
    if content != original:
        path.write_text(content, encoding="utf-8")
        return True
    return False


def main() -> None:
    changed = 0
    for path in ROOT.rglob("*.php"):
        if "vendor" in path.parts:
            continue
        if process_file(path):
            changed += 1
    print(f"Updated {changed} files")


if __name__ == "__main__":
    main()
