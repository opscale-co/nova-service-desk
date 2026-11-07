# Changelog

All notable changes to opscale-co/nova-api will be documented in this file.

## 2.0.0 (2026-11-07)

* feat(laravel): require Laravel 13, drop Laravel 11/12 ([5234934](https://github.com/opscale-co/nova-service-desk/commit/5234934))
* chore(deps): bump nova-comments to ^1.1 ([ad2d317](https://github.com/opscale-co/nova-service-desk/commit/ad2d317))
* chore(deps): upgrade to PHP 8.3 toolchain and modernize codebase ([08791da](https://github.com/opscale-co/nova-service-desk/commit/08791da))


### BREAKING CHANGE

* requires Laravel 13 (illuminate/* ^13.0, laravel/nova
^5.10, orchestra/testbench-dusk ^11.0). Drops support for Laravel 11 and
12. Sibling packages bumped to published majors: actions ^5.0,
nova-comments ^2.0, nova-dynamic-resources ^2.0, nova-package-tools ^2.0,
nova-toolbar-actions ^2.0, validations ^2.0.

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>

## 1.2.0 (2026-06-14)

* ci(workflows): bump php to 8.3 ([e8ac1cb](https://github.com/opscale-co/nova-service-desk/commit/e8ac1cb))
* feat(request): add custom index fields for request resource ([0970f33](https://github.com/opscale-co/nova-service-desk/commit/0970f33))
* feat(task): enable comments on task resource ([a4aeb66](https://github.com/opscale-co/nova-service-desk/commit/a4aeb66))

## 1.1.0 (2026-04-13)

* Merge branch 'main' of https://github.com/opscale-co/nova-service-desk ([be7541b](https://github.com/opscale-co/nova-service-desk/commit/be7541b))
* feat(workflow): added workflows ([eba0536](https://github.com/opscale-co/nova-service-desk/commit/eba0536))


### BREAKING CHANGE

* Added new migrations and the ways how a task can be moved between stages

## <small>1.0.9 (2026-03-16)</small>

* Merge branch 'main' of https://github.com/opscale-co/nova-service-desk ([8569807](https://github.com/opscale-co/nova-service-desk/commit/8569807))
* fix(gettasksequence): fixed bug when generating unique sequence ([0955e60](https://github.com/opscale-co/nova-service-desk/commit/0955e60))
* fix(kanbancard): fixed bad root route ([a0db4f3](https://github.com/opscale-co/nova-service-desk/commit/a0db4f3))
* fix(kanbancard): fixed base route definition ([14dd904](https://github.com/opscale-co/nova-service-desk/commit/14dd904))

## <small>1.0.8 (2026-03-09)</small>

* fix(averagemetric): solved SQLite specific query in metric ([011dabb](https://github.com/opscale-co/nova-service-desk/commit/011dabb))
* fix(template): removed wrong validation ([eb75e9c](https://github.com/opscale-co/nova-service-desk/commit/eb75e9c))

## <small>1.0.7 (2026-02-19)</small>

* fix(dependencies): solved request wrong index presentation ([be853e8](https://github.com/opscale-co/nova-service-desk/commit/be853e8))

## <small>1.0.6 (2026-02-17)</small>

* Merge branch 'main' of https://github.com/opscale-co/nova-service-desk ([3c08017](https://github.com/opscale-co/nova-service-desk/commit/3c08017))
* perf(request): added tracking code ([94f009c](https://github.com/opscale-co/nova-service-desk/commit/94f009c))

## <small>1.0.5 (2026-02-17)</small>

* fix(request): this is just a bump ([ca39f78](https://github.com/opscale-co/nova-service-desk/commit/ca39f78))
* ci(composer.lock): removed from git ignore ([d7aa050](https://github.com/opscale-co/nova-service-desk/commit/d7aa050))

## <small>1.0.4 (2026-02-17)</small>

* fix(request): rendering template actions ([db15a99](https://github.com/opscale-co/nova-service-desk/commit/db15a99))
* Merge branch 'main' of https://github.com/opscale-co/nova-service-desk ([12dd752](https://github.com/opscale-co/nova-service-desk/commit/12dd752))
* style(request): hidden categorization fields ([07cf227](https://github.com/opscale-co/nova-service-desk/commit/07cf227))

## <small>1.0.3 (2026-02-17)</small>

* fix(auto-check): fixed wrong templating in auto-check workflow ([a9c967b](https://github.com/opscale-co/nova-service-desk/commit/a9c967b))
* fix(statusesresolver): rename class for cosistency ([6103e1c](https://github.com/opscale-co/nova-service-desk/commit/6103e1c))
* Merge branch 'main' of https://github.com/opscale-co/nova-service-desk ([bfe90df](https://github.com/opscale-co/nova-service-desk/commit/bfe90df))
* refactor(request): updated dependencies loading for requests ([abaf90e](https://github.com/opscale-co/nova-service-desk/commit/abaf90e))

## <small>1.0.2 (2026-02-07)</small>

* Merge branch 'main' of https://github.com/opscale-co/nova-service-desk ([ba41525](https://github.com/opscale-co/nova-service-desk/commit/ba41525))
* refactor(dependencies): removed nova-catalogs dependency ([ee7bb08](https://github.com/opscale-co/nova-service-desk/commit/ee7bb08))


### BREAKING CHANGE

* Migrations and model references changed

## <small>1.0.1 (2026-02-06)</small>

* Merge branch 'main' into update-dependency ([e1d4d62](https://github.com/opscale-co/nova-service-desk/commit/e1d4d62))
* Merge pull request #2 from kevin-Oz/update-dependency ([5724371](https://github.com/opscale-co/nova-service-desk/commit/5724371)), closes [#2](https://github.com/opscale-co/nova-service-desk/issues/2)
* fix(dependencies): update nova-dynamic-resources requirement to ^1.4 ([2706ddd](https://github.com/opscale-co/nova-service-desk/commit/2706ddd))

## 1.0.0 (2026-01-28)

* build(semantic release): fixed dependencies for semantic release ([8af9d04](https://github.com/opscale-co/nova-service-desk/commit/8af9d04))
* fix(translations): updated translations ([3db6fe4](https://github.com/opscale-co/nova-service-desk/commit/3db6fe4))
* chore(gitignore): updated ([74a4e6d](https://github.com/opscale-co/nova-service-desk/commit/74a4e6d))
* feat(project): first release ([1ee1573](https://github.com/opscale-co/nova-service-desk/commit/1ee1573))
* Initial commit ([a2f2985](https://github.com/opscale-co/nova-service-desk/commit/a2f2985))

# Changelog

All notable changes to `nova-service-desk` will be documented in this file

## 1.0.0 - 2024-XX-XX

- initial release
