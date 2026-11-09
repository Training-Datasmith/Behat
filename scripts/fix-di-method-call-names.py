#!/usr/bin/env python3
"""Use Behat snake_case method names in Symfony addMethodCall() configs."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DEF_RE = re.compile(r"\bfunction\s+(?P<name>[a-z][a-z0-9_]*)\s*\(")
METHOD_DEFS: set[str] = set()
for php in (ROOT / "src").rglob("*.php"):
    METHOD_DEFS.update(m.group("name") for m in DEF_RE.finditer(php.read_text(encoding="utf-8", errors="replace")))

ADD_CALL_RE = re.compile(r"->addMethodCall\(\s*'([^']+)'")


def camel_to_snake(name: str) -> str:
    s1 = re.sub(r"(.)([A-Z][a-z]+)", r"\1_\2", name)
    return re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", s1).lower()


def process(text: str) -> str:
    def repl(m: re.Match[str]) -> str:
        method = m.group(1)
        if "_" in method:
            return m.group(0)
        snake = camel_to_snake(method)
        if snake not in METHOD_DEFS:
            return m.group(0)
        return f"->addMethodCall('{snake}'"

    return ADD_CALL_RE.sub(repl, text)


def main() -> None:
    changed = 0
    for path in (ROOT / "src").rglob("*.php"):
        text = path.read_text(encoding="utf-8")
        updated = process(text)
        if updated != text:
            path.write_text(updated, encoding="utf-8")
            changed += 1
    print(f"fix-di-method-call-names: updated {changed} files")


if __name__ == "__main__":
    main()
