# Graph Report - nova-service-desk  (2026-09-04)

## Corpus Check
- 135 files · ~81,531 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 890 nodes · 1585 edges · 67 communities (43 shown, 18 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 14 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `ad2d3174`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- ChangeStatus
- Illuminate\Support\Facades\Schema
- devDependencies
- ToolServiceProvider.php
- AssignTask.php
- WorkbenchServiceProvider.php
- Nova/Request.php
- SLAPriority
- Laravel\Nova\Http\Requests\NovaRequest
- Tool.vue
- TaskActivity
- scripts
- NovaServiceProvider
- Opscale\Actions\Action
- TaskStatus
- Subcategory
- Laravel\Nova\Fields\Repeater\Repeatable
- require-dev
- Insight
- Account
- Task
- composer.json
- scripts
- DuskTestCase
- rector.php
- require
- Workflow
- Select
- .ensureFixtures
- CategoryRepository
- TasksByStatus
- README.md
- psr-4
- keywords
- config
- extra
- app.php
- autoload
- console.php
- UserFactory
- webpack.mix.js
- commit-msg
- pre-commit
- prepare-commit-msg
- Illuminate\Database\Eloquent\Model
- Laravel\Nova\Resource
- Task
- TaskRepository
- User
- Tool.php
- Nova/User.php
- SLAPolicy
- StatusRule.php
- AverageTime
- CanTransition
- OpenRequests
- RequestActivity
- post-commit
- post-checkout
- CLAUDE.md

## God Nodes (most connected - your core abstractions)
1. `Task` - 45 edges
2. `TaskStatus` - 29 edges
3. `WorkflowStage` - 29 edges
4. `Category` - 24 edges
5. `ChangeStatus` - 23 edges
6. `Subcategory` - 22 edges
7. `Workflow` - 21 edges
8. `scripts` - 20 edges
9. `Account` - 20 edges
10. `SLAPriority` - 19 edges

## Surprising Connections (you probably didn't know these)
- `ServiceResolver` --implements--> `ProvidesService`  [EXTRACTED]
  workbench/app/Resolvers/ServiceResolver.php → src/Contracts/ProvidesService.php
- `ServiceResolver` --implements--> `RequiresService`  [EXTRACTED]
  workbench/app/Resolvers/ServiceResolver.php → src/Contracts/RequiresService.php
- `TechnicalSupportWorkflowResolver` --implements--> `WorkflowResolver`  [EXTRACTED]
  workbench/app/Resolvers/TechnicalSupportWorkflowResolver.php → src/Contracts/WorkflowResolver.php
- `TaskStatus` --implements--> `CanTransition`  [EXTRACTED]
  src/Models/Enums/TaskStatus.php → src/Contracts/CanTransition.php
- `Category` --mixes_in--> `CategoryRepository`  [EXTRACTED]
  src/Models/Category.php → src/Models/Repositories/CategoryRepository.php

## Import Cycles
- None detected.

## Communities (67 total, 18 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.10
Nodes (8): Illuminate\Database\Eloquent\Relations\BelongsTo, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Database\Eloquent\Relations\Pivot, Laravel\Nova\Actions\Actionable, Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate, CategoryTemplate, RequestRepository, Request

### Community 1 - "ChangeStatus"
Cohesion: 0.17
Nodes (4): Laravel\Nova\Fields\Select, ChangeStatus, NovaRequest, Task

### Community 2 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.06
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 3 - "devDependencies"
Cohesion: 0.04
Nodes (47): @commitlint/cli, @commitlint/config-conventional, @commitlint/cz-commitlint, @commitlint/prompt, cross-env, cz-conventional-changelog, eslint, eslint-config-prettier (+39 more)

### Community 4 - "ToolServiceProvider.php"
Cohesion: 0.07
Nodes (16): Illuminate\Http\JsonResponse, Illuminate\Http\Request, Illuminate\Routing\Controller, Illuminate\Support\Facades\Route, Laravel\Nova\Http\Middleware\Authenticate, Laravel\Nova\Nova, Opscale\NovaDynamicResources\Nova\Template, Opscale\NovaPackageTools\NovaPackage (+8 more)

### Community 5 - "AssignTask.php"
Cohesion: 0.11
Nodes (8): Illuminate\Support\Collection, Illuminate\Support\Facades\Auth, Illuminate\Validation\ValidationException, Laravel\Nova\Fields\ActionFields, static, WorkflowRepository, AssignTask, Throwable

### Community 6 - "WorkbenchServiceProvider.php"
Cohesion: 0.14
Nodes (8): Illuminate\Notifications\ChannelManager, Illuminate\Support\Facades\Notification, Illuminate\Support\ServiceProvider, ProvidesService, RequiresService, Workbench\App\Channels\StdoutChannel, WorkbenchServiceProvider, ServiceResolver

### Community 7 - "Nova/Request.php"
Cohesion: 0.14
Nodes (14): Laravel\Nova\Fields\Badge, Laravel\Nova\Fields\BelongsTo, Laravel\Nova\Fields\Boolean, Laravel\Nova\Fields\Date, Laravel\Nova\Fields\DateTime, Laravel\Nova\Fields\Email, Laravel\Nova\Fields\ID, Laravel\Nova\Fields\MultiSelect (+6 more)

### Community 8 - "SLAPriority"
Cohesion: 0.21
Nodes (3): SLAPriority, CalculatePriority, Task

### Community 9 - "Laravel\Nova\Http\Requests\NovaRequest"
Cohesion: 0.18
Nodes (6): Illuminate\Contracts\Database\Eloquent\Builder, Laravel\Nova\Auth\PasswordValidationRules, Laravel\Nova\Http\Requests\NovaRequest, Department, Resource, User

### Community 10 - "Tool.vue"
Cohesion: 0.08
Nodes (11): columns(), slugify(), STAGE_ICONS, taskBelongsToStage(), fetchTasks(), fetchWorkflows(), handleTransition(), mounted() (+3 more)

### Community 12 - "scripts"
Cohesion: 0.07
Nodes (27): path, config, commitizen, dependencies, vue-dndrop, private, scripts, analyse (+19 more)

### Community 13 - "NovaServiceProvider"
Cohesion: 0.12
Nodes (8): Illuminate\Support\Facades\Gate, Laravel\Fortify\Features, Laravel\Nova\Dashboard, Laravel\Nova\Dashboards\Main, Laravel\Nova\NovaApplicationServiceProvider, Laravel\Nova\Tool, Tool, NovaServiceProvider

### Community 14 - "Opscale\Actions\Action"
Cohesion: 0.05
Nodes (8): Carbon\Carbon, Illuminate\Support\Facades\Date, Opscale\Actions\Action, Subcategory, CalculateDueDate, GetCategorySequence, GetSubcategorySequence, GetTaskSequence

### Community 15 - "TaskStatus"
Cohesion: 0.11
Nodes (10): Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Database\Eloquent\Relations\MorphTo, Opscale\NovaDynamicResources\Models\Field, Opscale\NovaDynamicResources\Models\Template, Account, Category, ServiceChannel, SLAPolicyStatus (+2 more)

### Community 16 - "Subcategory"
Cohesion: 0.15
Nodes (5): Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Support\Facades\DB, PHPUnit\Framework\Attributes\Test, Subcategory, Workflow

### Community 17 - "Laravel\Nova\Fields\Repeater\Repeatable"
Cohesion: 0.32
Nodes (3): Laravel\Nova\Fields\Repeater\Repeatable, Contact, TimeSlot

### Community 18 - "require-dev"
Cohesion: 0.15
Nodes (13): require-dev, driftingly/rector-laravel, larastan/larastan, laravel/nova, laravel/nova-devtool, mockery/mockery, opscale-co/strict-rules, orchestra/testbench-dusk (+5 more)

### Community 19 - "Insight"
Cohesion: 0.15
Nodes (4): Laravel\Nova\Fields\File, Laravel\Nova\Fields\Trix, InsightScope, Insight

### Community 22 - "composer.json"
Cohesion: 0.20
Nodes (9): authors, description, homepage, license, minimum-stability, name, prefer-stable, repositories (+1 more)

### Community 23 - "scripts"
Cohesion: 0.20
Nodes (10): scripts, build, clear, post-autoload-dump, prepare, serve, @clear, Composer\\Config::disableProcessTimeout (+2 more)

### Community 24 - "DuskTestCase"
Cohesion: 0.09
Nodes (9): Laravel\Dusk\Browser, Laravel\Nova\NovaCoreServiceProvider, Laravel\Nova\NovaServiceProvider, Orchestra\Testbench\Concerns\WithWorkbench, Orchestra\Testbench\Dusk\TestCase, Orchestra\Testbench\TestCase, ConfigurationTest, DuskTestCase (+1 more)

### Community 25 - "rector.php"
Cohesion: 0.20
Nodes (10): Rector\Caching\ValueObject\Storage\FileCacheStorage, Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector, Rector\Config\RectorConfig, laravelSetup(), Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector, phpSetup(), Rector\PHPUnit\Set\PHPUnitSetList, Rector\Set\ValueObject\SetList (+2 more)

### Community 26 - "require"
Cohesion: 0.25
Nodes (8): require, opscale-co/actions, opscale-co/nova-comments, opscale-co/nova-dynamic-resources, opscale-co/nova-package-tools, opscale-co/nova-toolbar-actions, opscale-co/validations, php

### Community 30 - "CategoryRepository"
Cohesion: 0.38
Nodes (3): Illuminate\Support\Facades\Cache, CategoryRepository, static

### Community 31 - "TasksByStatus"
Cohesion: 0.32
Nodes (3): Laravel\Nova\Metrics\Partition, Laravel\Nova\Metrics\PartitionResult, TasksByStatus

### Community 32 - "README.md"
Cohesion: 0.05
Nodes (34): 1.0.0 - 2024-XX-XX, 1.0.0 (2026-01-28), 1.1.0 (2026-04-13), 1.2.0 (2026-06-14), BREAKING CHANGE, BREAKING CHANGE, Changelog, Changelog (+26 more)

### Community 33 - "psr-4"
Cohesion: 0.33
Nodes (6): autoload-dev, psr-4, Opscale\\NovaServiceDesk\\Tests\\, Workbench\\App\\, Workbench\\Database\\Factories\\, Workbench\\Database\\Seeders\\

### Community 34 - "keywords"
Cohesion: 0.33
Nodes (6): keywords, laravel, nova, opscale, package, tool

### Community 35 - "config"
Cohesion: 0.50
Nodes (4): pestphp/pest-plugin, config, allow-plugins, sort-packages

### Community 36 - "extra"
Cohesion: 0.50
Nodes (4): extra, laravel, providers, Opscale\\NovaServiceDesk\\ToolServiceProvider

### Community 37 - "app.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

### Community 38 - "autoload"
Cohesion: 0.67
Nodes (3): autoload, psr-4, Opscale\\NovaServiceDesk\\

### Community 40 - "UserFactory"
Cohesion: 0.19
Nodes (7): Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Workbench\App\Models\Product, ProductFactory, static, UserFactory

### Community 51 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.20
Nodes (10): Illuminate\Database\Eloquent\Concerns\HasUlids, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes, Opscale\Validations\Validatable, Insight, InsightRepository, ResolutionRepository, Resolution (+2 more)

### Community 52 - "Laravel\Nova\Resource"
Cohesion: 0.15
Nodes (9): Laravel\Nova\Fields\HasMany, Laravel\Nova\Fields\Hidden, Laravel\Nova\Fields\MorphTo, Laravel\Nova\Fields\Tag, Laravel\Nova\Resource, Laravel\Nova\Tabs\Tab, Opscale\NovaDynamicResources\Models\Enums\TemplateType, Opscale\NovaDynamicResources\Nova\Field (+1 more)

### Community 53 - "Task"
Cohesion: 0.23
Nodes (6): Opscale\NovaComments\Commentable, WorkflowResolver, Task, WorkflowStage, WorkflowStage, TechnicalSupportWorkflowResolver

### Community 55 - "User"
Cohesion: 0.23
Nodes (8): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Seeder, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, User, DatabaseSeeder, ServiceDeskSeeder

### Community 56 - "Tool.php"
Cohesion: 0.15
Nodes (4): Laravel\Nova\Menu\MenuGroup, Laravel\Nova\Menu\MenuItem, Laravel\Nova\Menu\MenuSection, Resolution

### Community 57 - "Nova/User.php"
Cohesion: 0.31
Nodes (9): Illuminate\Http\Resources\MergeValue, Laravel\Nova\Actions\Action, Laravel\Nova\Card, Laravel\Nova\Fields\Field, Laravel\Nova\Fields\Password, Laravel\Nova\Filters\Filter, Laravel\Nova\Lenses\Lens, Laravel\Nova\ResourceTool (+1 more)

### Community 59 - "StatusRule.php"
Cohesion: 0.36
Nodes (5): BackedEnum, Closure, Illuminate\Contracts\Validation\ValidationRule, InvalidArgumentException, StatusRule

### Community 64 - "post-commit"
Cohesion: 0.40
Nodes (4): post-commit script, GRAPHIFY_CHANGED, GRAPHIFY_REBUILD_LOG, PYTHONHASHSEED

### Community 65 - "post-checkout"
Cohesion: 0.50
Nodes (3): post-checkout script, GRAPHIFY_REBUILD_LOG, PYTHONHASHSEED

## Knowledge Gaps
- **126 isolated node(s):** `name`, `description`, `laravel`, `nova`, `package` (+121 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 373 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **18 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Request` connect `ToolServiceProvider.php` to `AssignTask.php`, `Nova/Request.php`, `TaskStatus`, `Laravel\Nova\Resource`, `Tool.php`, `CanTransition`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._
- **Why does `Task` connect `Task` to `Illuminate\Database\Eloquent\Relations\BelongsTo`, `ChangeStatus`, `ToolServiceProvider.php`, `AssignTask.php`, `SLAPriority`, `TaskActivity`, `Opscale\Actions\Action`, `TaskStatus`, `Illuminate\Database\Eloquent\Model`, `TaskRepository`, `AverageTime`, `.ensureFixtures`, `TasksByStatus`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Why does `Category` connect `TaskStatus` to `ToolServiceProvider.php`, `Nova/Request.php`, `SLAPriority`, `Opscale\Actions\Action`, `Subcategory`, `Illuminate\Database\Eloquent\Model`, `User`, `DuskTestCase`, `.ensureFixtures`, `CategoryRepository`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **What connects `name`, `description`, `laravel` to the rest of the system?**
  _126 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Relations\BelongsTo` be split into smaller, more focused modules?**
  _Cohesion score 0.0960591133004926 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Support\Facades\Schema` be split into smaller, more focused modules?**
  _Cohesion score 0.06274509803921569 - nodes in this community are weakly interconnected._
- **Should `devDependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.0425531914893617 - nodes in this community are weakly interconnected._