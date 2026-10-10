# Tests

Unit tests run with plain PHPUnit and need no database or OpenDXP kernel:

```bash
composer install
vendor/bin/phpunit
```

- `tests/Unit/ObjectCreationRules/` — fork feature «Object Creation Rules» (`RuleMatcher`, `CreationGuardSubscriber`).
- `tests/Unit/*AccessorTest.php` — tree store → configuration conversion of perspectives and custom views.

Writing configuration (`writeConfiguration()`) needs a booted OpenDXP with a database and is not covered here.
