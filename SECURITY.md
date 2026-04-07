# Security Policy

## Supported Versions

Thorm is pre-1.0 and under active development.

- `main` is the primary supported branch for security fixes.
- Older snapshots, forks, and unpublished local copies are not supported.
- Once tagged releases exist, this file will be updated with a version support table.

## Reporting a Vulnerability

Please report suspected vulnerabilities privately.

- Do not open a public GitHub issue for security problems.
- Include a clear description, reproduction steps, impact, and affected area if possible.
- If a proof of concept is needed, keep it minimal and safe.

## Contact

Security contact channel: `contact@thorm.dev`

## Response Process

- We will try to acknowledge valid reports within 7 days.
- We will assess severity, confirm impact, and work on a fix.
- Please allow reasonable time for a patch before public disclosure.

## Scope

Relevant issues include, for example:

- XSS or unsafe HTML/attribute handling
- unsafe URL handling
- prototype pollution or unsafe object ingestion
- auth, CSRF, or request-handling flaws in official helpers
- runtime sandbox or capability escapes
- supply-chain issues in project-controlled dependencies
