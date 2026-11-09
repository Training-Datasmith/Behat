#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

UPSTREAM_TREE="fe6960db"
WIRING_COMMIT="65cf4821"

echo "== restore ai-training tree (clean) =="
git rm -rf src features tests bin examples 2>/dev/null || rm -rf src features tests bin examples
git checkout "${UPSTREAM_TREE}" -- src features tests bin examples ARCHITECTURE.md i18n.php
git checkout "${WIRING_COMMIT}" -- composer.json scripts docker/system-test/Dockerfile rector.php phpstan.dist.neon

echo "== PSR-4 directory renames =="
python3 - <<'PY'
from pathlib import Path
import subprocess
renames = {
    'ServiceContainer': 'Service_Container',
    'EventDispatcher': 'Event_Dispatcher',
    'PathOptions': 'Path_Options',
    'HelperContainer': 'Helper_Container',
    'EventListener': 'Event_Listener',
}
paths=[]
for old,new in renames.items():
    for d in Path('src').rglob(old):
        if d.is_dir() and d.name==old:
            target=d.with_name(new)
            if not target.exists():
                paths.append((d,target))
paths.sort(key=lambda t: len(str(t[0])), reverse=True)
for d,target in paths:
    subprocess.check_call(['git','mv',str(d),str(target)])
PY

echo "== fix namespaces to match directories =="
find src -name '*.php' -print0 | xargs -0 perl -pi -e '
  s/namespace Behat\\Testwork\\EventDispatcher/namespace Behat\\Testwork\\Event_Dispatcher/g;
  s/namespace Behat\\Behat\\EventDispatcher/namespace Behat\\Behat\\Event_Dispatcher/g;
  s/namespace Behat\\Testwork\\PathOptions/namespace Behat\\Testwork\\Path_Options/g;
  s/namespace Behat\\Behat\\HelperContainer/namespace Behat\\Behat\\Helper_Container/g;
  s/namespace Behat\\Behat\\Gherkin\\ServiceContainer/namespace Behat\\Behat\\Gherkin\\Service_Container/g;
  s/\\EventDispatcher\\/\\Event_Dispatcher\\/g;
  s/\\PathOptions\\/\\Path_Options\\/g;
  s/\\HelperContainer\\/\\Helper_Container\\/g;
  s/\\ServiceContainer\\/\\Service_Container\\/g;
'

perl -pi -e "s/'\\\\ServiceContainer\\\\\\\\'/\\'\\\\\\\\Service_Container\\\\\\\\\\'/g" src/Behat/Testwork/Service_Container/Extension_Manager.php

echo "== third-party FQCN + short types =="
python3 scripts/fix-third-party-fqcn.py
docker run --rm -v "$ROOT:/app" -w /app behat-system-test:remote-test-2026-11-09 php scripts/fix-third-party-short-types.php
find src tests features bin -name '*.php' -print0 | xargs -0 perl -pi -e '
  s/Behat\\(Testwork|Behat|Tests)\\EventDispatcher/Behat\\$1\\Event_Dispatcher/g;
  s/namespace Behat\\(Testwork|Behat)\\EventDispatcher/namespace Behat\\$1\\Event_Dispatcher/g;
  s/namespace Behat\\Tests\\Testwork\\EventDispatcher/namespace Behat\\Tests\\Testwork\\Event_Dispatcher/g;
  s/Behat\\Behat\\Gherkin\\ServiceContainer;/Behat\\Behat\\Gherkin\\Service_Container;/g;
'
python3 scripts/fix-behat-method-calls.py
docker run --rm -v "$ROOT:/app" -w /app behat-system-test:remote-test-2026-11-09 php scripts/fix-symfony-di-calls.php
python3 scripts/fix-symfony-vendor-methods.py
python3 scripts/fix-di-method-call-names.py

echo "== Symfony console parent APIs =="
python3 scripts/fix-symfony-cli-overrides.py
python3 scripts/fix-php-reflection-methods.py
python3 scripts/fix-gherkin-node-method-calls.py

echo "== bin/behat + tooling paths =="
perl -pi -e "s/Behat\\\\Behat\\\\ApplicationFactory/Behat\\\\Behat\\\\Application_Factory/g; s/createApplication/create_application/g" bin/behat
perl -pi -e 's|DeprecationExtension\.php|Deprecation_Extension.php|' rector.php
perl -pi -e '
  s/PHPUnitExceptionStringer\.php/PHP_Unit_Exception_Stringer.php/;
  s/ConsoleDefinitionPrinter\.php/Console_Definition_Printer.php/;
  s/StopOnFailureController\.php/Stop_On_Failure_Controller.php/;
  s/JUnitScenarioPrinter\.php/J_Unit_Scenario_Printer.php/;
  s/ListPrinter\.php/List_Printer.php/;
  s/RuntimeFeatureTester\.php/Runtime_Feature_Tester.php/;
  s/ReturnTypeTransformation\.php/Return_Type_Transformation.php/;
  s/OrderController\.php/Order_Controller.php/;
' phpstan.dist.neon

echo "== JSON/PHP acronym class/file alignment =="
python3 - <<'PY'
import re
from pathlib import Path
pairs=[]
for p in Path('src').rglob('*.php'):
    t=p.read_text(encoding='utf-8',errors='replace')
    exp=p.stem
    mc=re.search(r'^((?:final\s+|abstract\s+)?class\s+)([A-Za-z0-9_]+)', t, re.M)
    if mc and mc.group(2)!=exp:
        old,new=mc.group(2),exp
        pairs.append((old,new))
        t2=re.sub(r'^((?:final\s+|abstract\s+)?class\s+)'+re.escape(old)+r'\b', r'\1'+new, t, count=1, flags=re.M)
        p.write_text(t2, encoding='utf-8')
for old,new in pairs:
    for p in Path('.').rglob('*.php'):
        if 'vendor' in p.parts: continue
        t=p.read_text(encoding='utf-8',errors='replace')
        t2=re.sub(r'\b'+re.escape(old)+r'\b', new, t)
        if t2!=t: p.write_text(t2, encoding='utf-8')
print('acronym pairs', pairs)
PY

docker run --rm -v "$ROOT:/app" -w /app behat-system-test:remote-test-2026-11-09 php scripts/apply-legacy-rename-map.php

if [ -d src/Behat/Behat/Output/Node/Event_Listener/JUnit ]; then
  git mv src/Behat/Behat/Output/Node/Event_Listener/JUnit src/Behat/Behat/Output/Node/Event_Listener/J_Unit
fi
if [ -d src/Behat/Behat/Output/Node/Printer/JUnit ]; then
  git mv src/Behat/Behat/Output/Node/Printer/JUnit src/Behat/Behat/Output/Node/Printer/J_Unit
fi

echo "pipeline complete"
