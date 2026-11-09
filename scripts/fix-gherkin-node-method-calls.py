#!/usr/bin/env python3
"""Restore upstream Behat Gherkin / PHP Throwable method names on call sites."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SCAN = [ROOT / "src", ROOT / "tests", ROOT / "features"]

# Upstream Gherkin nodes & PHP exceptions (no repo-defined snake equivalents).
CALL_MAP = {
    "get_file": "getFile",
    "get_line": "getLine",
    "get_title": "getTitle",
    "get_scenarios": "getScenarios",
    "get_keyword": "getKeyword",
    "get_language": "getLanguage",
    "get_message": "getMessage",
    "get_code": "getCode",
    "get_trace": "getTrace",
    "has_background": "hasBackground",
    "get_background": "getBackground",
    "has_scenarios": "hasScenarios",
    "get_steps": "getSteps",
    "has_steps": "hasSteps",
    "get_examples": "getExamples",
    "get_tags": "getTags",
    "has_description": "hasDescription",
    "get_description": "getDescription",
    "get_node_type": "getNodeType",
    "get_strings": "getStrings",
    "get_raw": "getRaw",
    "get_tokens": "getTokens",
    "get_text": "getText",
    "get_argument_type": "getArgumentType",
    "get_table": "getTable",
    "get_pystring": "getPyString",
    "get_keyword_type": "getKeywordType",
    "has_arguments": "hasArguments",
    "get_full_text": "getFullText",
    "get_example_table": "getExampleTable",
    "get_example_tables": "getExampleTables",
    "get_row": "getRow",
    "get_column": "getColumn",
    "get_rows": "getRows",
    "get_table": "getTable",
    "get_columns_hash": "getColumnsHash",
    "get_rows_hash": "getRowsHash",
    "get_row_as_string": "getRowAsString",
    "get_row_as_string_with_wrapped_values": "getRowAsStringWithWrappedValues",
    "get_table_as_string": "getTableAsString",
    "get_row_line": "getRowLine",
}

GHERKIN_VAR = re.compile(
    r"\$(?:step|feature|outline|table|argument|node|background|scenario|example|keyword)[a-zA-Z0-9_]*"
    r"(?:->[a-zA-Z][a-zA-Z0-9_]*)*"
    r"->(?P<call>"
    + "|".join(re.escape(k) for k in CALL_MAP)
    + r")\s*\("
)


def process(text: str) -> str:
    def repl(m: re.Match[str]) -> str:
        return m.group(0).replace(f"->{m.group('call')}(", f"->{CALL_MAP[m.group('call')]}(")

    text = GHERKIN_VAR.sub(repl, text)
    # StepNode::getArguments and friends when receiver is a local named like $multi from step
    for snake, camel in CALL_MAP.items():
        text = re.sub(
            rf"\$step->({re.escape(snake)})\(",
            rf"$step->{camel}(",
            text,
        )

    return text


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
    print(f"fix-gherkin-node-method-calls: updated {changed} files")


if __name__ == "__main__":
    main()
