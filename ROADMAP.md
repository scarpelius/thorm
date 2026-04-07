# Roadmap

## Current Status

Thorm is in a public pre-1.0 stage.

Today the project already supports:

- CSR for browser-rendered applications
- SSR with hydration
- hybrid rendering with client islands inside server-rendered pages
- a PHP DSL for state, actions, effects, routing, components, and rendering

The main remaining work is not inventing new core concepts. It is hardening the release story around packaging, verification, documentation, and API clarity.

## Priorities

### 1. Release Readiness

The immediate goal is a coherent public package and install story.

This includes:

- consistent package metadata and branding
- clear installation and example workflows
- documented runtime asset handling
- clearer separation between public APIs and internal implementation details

### 2. Verification and Stability

The next step is moving from example-driven confidence to repeatable regression coverage.

This includes:

- broader automated coverage for the PHP DSL and IR generation
- regression coverage for runtime behavior
- stronger SSR and hydration verification
- baseline CI for release-facing paths

### 3. API Consolidation

Thorm needs a cleaner public surface before 1.0.

This includes:

- a more unified rendering API
- deprecation or removal of legacy entry points
- clearer public conventions for templates, output, and bootstrapping
- better definition of what is stable versus experimental

### 4. Documentation Quality

The documentation needs to work as a product guide, not only as internal notes.

This includes:

- a concise getting-started path
- clearer rendering-mode guidance
- more consistent API reference coverage
- better deployment-oriented documentation

### 5. Security and Trust

Thorm also needs clearer operational trust boundaries.

This includes:

- documenting safe and unsafe rendering behavior clearly
- improving IR and runtime input validation
- clarifying expectations around HTTP helpers and state mutation
- aligning release messaging with actual guarantees

## Planned Phases

### Public Alpha

Goal:

- publish Thorm as a serious framework for evaluation and experimentation

Focus:

- packaging consistency
- cleaned-up release documentation
- reproducible examples
- explicit experimental scope

### Public Beta

Goal:

- make Thorm credible for sustained internal adoption and serious pilot use

Focus:

- regression coverage for major primitives and runtime flows
- clearer supported public APIs
- stable packaging and upgrade expectations
- stronger end-to-end docs

### Version 1.0

Goal:

- establish Thorm as a stable framework release

Focus:

- compatibility policy
- semver discipline
- explicit browser and deployment support guidance
- supported SSR and hydration behavior

## Near-Term Direction

The next release cycle is focused on operational maturity, not broad feature expansion.

Highest-priority work:

- release packaging
- verification
- renderer and API consolidation
- release-quality documentation
- security posture clarity

Lower-priority work:

- advanced tooling
- plugin ambitions
- additional host-language targets
- performance positioning beyond measured evidence
