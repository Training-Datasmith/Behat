#!/usr/bin/env bash
set -euo pipefail

export COMPOSER_ALLOW_SUPERUSER=1
export LANG=C

git config --global --add safe.directory /app 2>/dev/null || true

php scripts/check-legacy-repo-names.php

strip_ansi() {
    local infile="$1"
    local outfile="$2"
    sed -E 's/\x1b\[[0-9;]*[[:alpha:]]//g' "${infile}" >"${outfile}"
}

fail_deprecation_output() {
    local label="$1"
    local file="$2"
    local stripped
    stripped="$(mktemp)"
    strip_ansi "${file}" "${stripped}"
    if grep -E '(^|[[:space:]])(PHP[[:space:]]+)?(Warning|Deprecated|Notice):' "${stripped}" >/dev/null; then
        echo "${label}: output contains Warning, Deprecated, or Notice lines" >&2
        grep -E '(^|[[:space:]])(PHP[[:space:]]+)?(Warning|Deprecated|Notice):' "${stripped}" >&2 || true
        rm -f "${stripped}"
        exit 1
    fi
    rm -f "${stripped}"
}

php -v | tee /tmp/php-version.txt
php -r '
$v = PHP_VERSION;
if (version_compare($v, "8.2.0", "<") || version_compare($v, "8.3.0", ">=")) {
    fwrite(STDERR, "PHP {$v} is outside required >=8.2 <8.3 (digest-pinned 8.2 floor)\n");
    exit(1);
}
'

for ext in ctype dom filter iconv json mbstring phar tokenizer xml xmlwriter; do
    if ! php -m | grep -qi "^${ext}$"; then
        echo "Missing required PHP extension: ${ext}" >&2
        exit 1
    fi
done

COMPOSER_URL="https://getcomposer.org/download/2.8.10/composer.phar"
COMPOSER_SHA256="28dbb6bd8bef31479c7985b774c130a8bda37dbe63c35b56f6cb6bc377427573"
curl -fsSL -o /tmp/composer.phar "${COMPOSER_URL}"
echo "${COMPOSER_SHA256}  /tmp/composer.phar" | sha256sum -c -

composer_out="$(mktemp)"
set +e
php /tmp/composer.phar update --no-interaction --prefer-dist --no-progress >"${composer_out}" 2>&1
composer_status=$?
set -e
cat "${composer_out}"
fail_deprecation_output "composer update" "${composer_out}"
if [[ ${composer_status} -ne 0 ]]; then
    rm -f "${composer_out}"
    exit "${composer_status}"
fi
rm -f "${composer_out}"

pins_out="$(mktemp)"
set +e
php scripts/check-cluster-pins.php >"${pins_out}" 2>&1
pins_status=$?
set -e
cat "${pins_out}"
fail_deprecation_output "check-cluster-pins" "${pins_out}"
if [[ ${pins_status} -ne 0 ]]; then
    rm -f "${pins_out}"
    exit "${pins_status}"
fi
rm -f "${pins_out}"

audit_out="$(mktemp)"
set +e
php /tmp/composer.phar audit >"${audit_out}" 2>&1
audit_status=$?
set -e
cat "${audit_out}"
fail_deprecation_output "composer audit" "${audit_out}"
if [[ ${audit_status} -ne 0 ]]; then
    exit "${audit_status}"
fi
if ! grep -Fxq 'No security vulnerability advisories found.' "${audit_out}"; then
    echo 'composer audit: missing exact success line' >&2
    exit 1
fi
rm -f "${audit_out}"

cs_out="$(mktemp)"
set +e
php /tmp/composer.phar cs >"${cs_out}" 2>&1
cs_status=$?
set -e
cat "${cs_out}"
fail_deprecation_output "composer cs" "${cs_out}"
if [[ ${cs_status} -ne 0 ]]; then
    exit "${cs_status}"
fi
cs_stripped="$(mktemp)"
strip_ansi "${cs_out}" "${cs_stripped}"
if ! grep -E '^Found 0 of [0-9]+ files that can be fixed' "${cs_stripped}" >/dev/null; then
    echo 'php-cs-fixer: missing Found 0 of N files that can be fixed success line' >&2
    rm -f "${cs_stripped}"
    exit 1
fi
rm -f "${cs_stripped}" "${cs_out}"

behat_out="$(mktemp)"
set +e
./bin/behat --format=progress --strict >"${behat_out}" 2>&1
behat_status=$?
set -e
cat "${behat_out}"
fail_deprecation_output "behat" "${behat_out}"
if [[ ${behat_status} -ne 0 ]]; then
    exit "${behat_status}"
fi
behat_stripped="$(mktemp)"
strip_ansi "${behat_out}" "${behat_stripped}"
BASELINE_BEHAT_SCENARIOS="${BASELINE_BEHAT_SCENARIOS:-379}"
BASELINE_BEHAT_STEPS="${BASELINE_BEHAT_STEPS:-1770}"
if ! grep -E "^${BASELINE_BEHAT_SCENARIOS} scenarios? \\([0-9]+ passed\\)\$" "${behat_stripped}" >/dev/null; then
    echo 'behat: missing strict scenarios passed summary line' >&2
    rm -f "${behat_stripped}"
    exit 1
fi
if ! grep -E "^${BASELINE_BEHAT_STEPS} steps? \\([0-9]+ passed\\)\$" "${behat_stripped}" >/dev/null; then
    echo 'behat: missing strict steps passed summary line' >&2
    rm -f "${behat_stripped}"
    exit 1
fi
if grep -E '^[0-9]+ scenarios? \(' "${behat_stripped}" | grep -Ev '^[0-9]+ scenarios? \([0-9]+ passed\)$' >/dev/null; then
    echo 'behat: scenario summary line is not exactly N passed' >&2
    grep -E '^[0-9]+ scenarios? \(' "${behat_stripped}" | grep -Ev '^[0-9]+ scenarios? \([0-9]+ passed\)$' >&2 || true
    rm -f "${behat_stripped}"
    exit 1
fi
if grep -E '^[0-9]+ steps? \(' "${behat_stripped}" | grep -Ev '^[0-9]+ steps? \([0-9]+ passed\)$' >/dev/null; then
    echo 'behat: step summary line is not exactly N passed' >&2
    grep -E '^[0-9]+ steps? \(' "${behat_stripped}" | grep -Ev '^[0-9]+ steps? \([0-9]+ passed\)$' >&2 || true
    rm -f "${behat_stripped}"
    exit 1
fi
rm -f "${behat_stripped}" "${behat_out}"

phpunit_out="$(mktemp)"
set +e
./vendor/bin/phpunit --colors=never --no-coverage >"${phpunit_out}" 2>&1
phpunit_status=$?
set -e
cat "${phpunit_out}"
fail_deprecation_output "phpunit" "${phpunit_out}"
if [[ ${phpunit_status} -ne 0 ]]; then
    exit "${phpunit_status}"
fi
phpunit_stripped="$(mktemp)"
strip_ansi "${phpunit_out}" "${phpunit_stripped}"
BASELINE_PHPUNIT_TESTS="${BASELINE_PHPUNIT_TESTS:-101}"
BASELINE_PHPUNIT_ASSERTIONS="${BASELINE_PHPUNIT_ASSERTIONS:-124}"
if ! grep -E "^OK \\(${BASELINE_PHPUNIT_TESTS} tests?, ${BASELINE_PHPUNIT_ASSERTIONS} assertions\\)\$" "${phpunit_stripped}" >/dev/null; then
    echo 'phpunit: missing strict OK success line' >&2
    rm -f "${phpunit_stripped}"
    exit 1
fi
rm -f "${phpunit_stripped}" "${phpunit_out}"

phpstan_out="$(mktemp)"
set +e
./vendor/bin/phpstan analyse --configuration=phpstan.dist.neon --memory-limit=-1 --no-progress --no-ansi >"${phpstan_out}" 2>&1
phpstan_status=$?
set -e
cat "${phpstan_out}"
fail_deprecation_output "phpstan" "${phpstan_out}"
if [[ ${phpstan_status} -ne 0 ]]; then
    exit "${phpstan_status}"
fi
phpstan_stripped="$(mktemp)"
strip_ansi "${phpstan_out}" "${phpstan_stripped}"
if ! grep -E '^[[:space:]]*\[OK\] No errors[[:space:]]*$' "${phpstan_stripped}" >/dev/null; then
    echo 'phpstan: missing full-line [OK] No errors success marker' >&2
    rm -f "${phpstan_stripped}"
    exit 1
fi
rm -f "${phpstan_stripped}" "${phpstan_out}"

rector_out="$(mktemp)"
set +e
./vendor/bin/rector process --dry-run --no-progress-bar >"${rector_out}" 2>&1
rector_status=$?
set -e
cat "${rector_out}"
fail_deprecation_output "rector" "${rector_out}"
if [[ ${rector_status} -ne 0 ]]; then
    exit "${rector_status}"
fi
rector_stripped="$(mktemp)"
strip_ansi "${rector_out}" "${rector_stripped}"
if ! grep -E '^[[:space:]]*\[OK\] Rector is done![[:space:]]*$' "${rector_stripped}" >/dev/null; then
    echo 'rector: missing full-line [OK] Rector is done! success marker' >&2
    rm -f "${rector_stripped}"
    exit 1
fi
rm -f "${rector_stripped}" "${rector_out}"

echo 'system-test: all steps passed.'
