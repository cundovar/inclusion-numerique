---
status: done
---

# Instruction: Symfony remote runner

## Architecture projection

> Tree of the final files. ✅ create · ✏️ modify · ❌ delete

```txt
.
├── ✅ src/Service/Capture/RemoteCaptureRunner.php
├── ✅ src/Service/Capture/ConfiguredCaptureRunner.php
├── ✅ tests/Service/RemoteCaptureRunnerTest.php
├── ✏️ config/services.yaml
├── ✏️ docs/env.example
└── ✏️ docs/CONNECTEUR.md
```

## User Journey

```mermaid
flowchart TD
  A[Connector calls capturer_page] --> B[Symfony validates request]
  B --> C[Remote runner calls Netcup service]
  C --> D[Symfony stores JPEG in its own uploads]
  C --> E[Service unavailable]
  E --> F[Connector receives CAPTURE_INDISPONIBLE]
```

## Test Scope

```mermaid
---
title: Test scope
---
journey
  section Setup
    configured remote endpoint and mock response => remote runner is ready: 5: system
  section Happy path
    valid capture response => JPEG is written into Symfony temporary directory: 5: system
  section Edge case - unavailable service
    connection failure => connector-safe CAPTURE_INDISPONIBLE is returned: 1: system
  section Teardown
    temporary capture file is removed => test filesystem is clean: 5: system
```

## Tasks to do

### `1)` Add the Netcup HTTP client

> Allow Symfony to select the remote runner from environment configuration and preserve its existing local fallback for development.

1. Implement the authenticated client with response and image validation.
2. Add an environment-selected runner without exposing the bearer token in logs or responses.
3. Test valid, malformed and unavailable remote responses.

## Test acceptance criteria

| Task | Acceptance criteria |
| ---- | -------------------------------- |
| 1 | Configuring the remote URL sends the request to Netcup and stores only its returned JPEG locally. |
| 2 | Network, authentication and malformed-response failures produce `CAPTURE_INDISPONIBLE`. |
