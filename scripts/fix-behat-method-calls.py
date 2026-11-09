#!/usr/bin/env python3
"""Restore Behat snake_case method calls mistakenly camelCased."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DEF_RE = re.compile(r"\bfunction\s+(?P<name>[a-z][a-z0-9_]*)\s*\(")

METHOD_DEFS: set[str] = set()
for php in (ROOT / "src").rglob("*.php"):
    text = php.read_text(encoding="utf-8", errors="replace")
    METHOD_DEFS.update(m.group("name") for m in DEF_RE.finditer(text))

SYMFONY_VARS = frozenset(
    {
        "container",
        "command",
        "input",
        "output",
        "definition",
        "tree_builder",
        "builder",
        "children_builder",
        "root_node",
        "node",
        "arrayNode",
        "xml",
        "dom",
    }
)

CALL_RE = re.compile(
    r"\$(?P<var>this|[a-zA-Z_][a-zA-Z0-9_]*)"
    r"(?P<chain>(?:->[a-z][a-z0-9_]*)*)"
    r"->(?P<call>[a-z][a-zA-Z0-9]*)\("
)


def camel_to_snake(name: str) -> str:
    s1 = re.sub(r"(.)([A-Z][a-z]+)", r"\1_\2", name)
    return re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", s1).lower()


def process(text: str) -> str:
    def repl(m: re.Match[str]) -> str:
        var = m.group("var")
        chain = m.group("chain")
        call = m.group("call")
        if "_" in call or var in SYMFONY_VARS or var == "this":
            return m.group(0)
        if not any(c.isupper() for c in call[1:]):
            return m.group(0)
        snake = camel_to_snake(call)
        if snake not in METHOD_DEFS:
            return m.group(0)
        return f"${var}{chain}->{snake}("

    return CALL_RE.sub(repl, text)


def main() -> None:
    changed = 0
    for path in list((ROOT / "src").rglob("*.php")) + list((ROOT / "tests").rglob("*.php")):
        if "vendor" in path.parts:
            continue
        text = path.read_text(encoding="utf-8")
        updated = process(text)
        if updated != text:
            path.write_text(updated, encoding="utf-8")
            changed += 1
    print(f"fix-behat-method-calls: updated {changed} files")


if __name__ == "__main__":
    main()
