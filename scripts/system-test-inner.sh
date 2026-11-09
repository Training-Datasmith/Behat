#!/usr/bin/env bash
set -euo pipefail

export COMPOSER_ALLOW_SUPERUSER=1
export LANG=C

git config --global --add safe.directory /app 2>/dev/null || true

fail_deprecation_output() {
    local label="$1"
    local file="$2"
    local stripped
    stripped="$(mktemp)"
    sed -E 's/\x1b\[[0-9;]*[[:alpha:]]//g' "${file}" >"${stripped}"
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

php scripts/check-cluster-pins.php

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
rm -f "${cs_out}"

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
if grep -E '^[0-9]+ scenarios? \([^)]*failed' "${behat_out}" >/dev/null; then
    echo 'behat: found failed scenarios in summary' >&2
    exit 1
fi
rm -f "${behat_out}"

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
if ! grep -E '^OK \([0-9]+ tests?, [0-9]+ assertions\)$' "${phpunit_out}" >/dev/null; then
    echo 'phpunit: missing strict OK success line' >&2
    exit 1
fi
rm -f "${phpunit_out}"

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
if ! grep -F '[OK] No errors' "${phpstan_out}" >/dev/null; then
    echo 'phpstan: missing [OK] No errors' >&2
    exit 1
fi
rm -f "${phpstan_out}"

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
if ! grep -Fq ' [OK] Rector is done!' "${rector_out}" && ! grep -Fq '[OK] Rector is done!' "${rector_out}"; then
  echo 'rector: missing Rector success marker' >&2
  exit 1
fi
rm -f "${rector_out}"

echo 'system-test: all steps passed.'
