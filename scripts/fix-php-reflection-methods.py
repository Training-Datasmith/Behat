#!/usr/bin/env python3
"""Restore upstream PHP Reflection API method names mistaken for snake_case."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SCAN = [ROOT / "src", ROOT / "tests", ROOT / "features"]

DEF_RE = re.compile(r"\bfunction\s+(?P<name>[a-z][a-z0-9_]*)\s*\(")
CALL_RE = re.compile(r"->(?P<call>[a-z][a-z0-9_]*)\s*\(")

METHOD_DEFS: set[str] = set()
for php in (ROOT / "src").rglob("*.php"):
    METHOD_DEFS.update(m.group("name") for m in DEF_RE.finditer(php.read_text(encoding="utf-8", errors="replace")))

# PHP Reflection* APIs (and close builtins) — not Behat repo methods.
PHP_REFLECTION_CALLS = {
    "implements_interface": "implementsInterface",
    "get_parent_class": "getParentClass",
    "get_declaring_class": "getDeclaringClass",
    "get_declaring_function": "getDeclaringFunction",
    "get_parameters": "getParameters",
    "get_prototype": "getPrototype",
    "get_doc_comment": "getDocComment",
    "get_return_type": "getReturnType",
    "get_types": "getTypes",
    "get_default_value": "getDefaultValue",
    "is_default_value_available": "isDefaultValueAvailable",
    "get_name": "getName",
    "get_class": "getClass",
    "get_constants": "getConstants",
    "get_method": "getMethod",
    "get_methods": "getMethods",
    "new_instance": "newInstance",
    "new_instance_args": "newInstanceArgs",
    "new_instance_without_constructor": "newInstanceWithoutConstructor",
    "get_constructor": "getConstructor",
    "is_instance_of": "isInstanceOf",
    "is_subclass_of": "isSubclassOf",
    "get_property": "getProperty",
    "get_properties": "getProperties",
    "get_interface_names": "getInterfaceNames",
    "get_trait_names": "getTraitNames",
    "get_type": "getType",
    "get_position": "getPosition",
    "is_static": "isStatic",
    "is_public": "isPublic",
    "is_private": "isPrivate",
    "is_protected": "isProtected",
    "is_abstract": "isAbstract",
    "is_final": "isFinal",
    "is_optional": "isOptional",
    "allows_null": "allowsNull",
    "has_type": "hasType",
    "is_builtin": "isBuiltin",
    "is_variadic": "isVariadic",
    "is_instance": "isInstance",
    "has_method": "hasMethod",
    "set_value": "setValue",
    "get_value": "getValue",
    "get_file_name": "getFileName",
    "get_start_line": "getStartLine",
    "get_end_line": "getEndLine",
    "invoke_args": "invokeArgs",
}


def snake_to_camel(name: str) -> str:
    parts = [p for p in name.split("_") if p]
    if not parts:
        return name
    return parts[0] + "".join(p[:1].upper() + p[1:] for p in parts[1:])


def process(text: str) -> str:
    defined = {m.group("name") for m in DEF_RE.finditer(text)}

    def repl(m: re.Match[str]) -> str:
        call = m.group("call")
        if call not in PHP_REFLECTION_CALLS or call in defined:
            return m.group(0)
        return f"->{PHP_REFLECTION_CALLS[call]}("

    return CALL_RE.sub(repl, text)


def main() -> None:
    changed = 0
    for base in SCAN:
        if not base.is_dir():
            continue
        for path in base.rglob("*.php"):
            text = path.read_text(encoding="utf-8")
            updated = process(text)
            if updated != text:
                path.write_text(updated, encoding="utf-8")
                changed += 1
    print(f"fix-php-reflection-methods: updated {changed} files")


if __name__ == "__main__":
    main()
