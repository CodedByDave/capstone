# Concurrent-user load test

This test sends read-only traffic to `/`, `/plans`, and `/login`. It ramps through
increasing virtual-user stages and stops when the error rate exceeds 5% or p95
latency exceeds five seconds.

Run the short local check:

```powershell
$env:LOAD_STAGES='10,50,100'
$env:LOAD_STAGE_SECONDS='15'
node tests/load/ccu-stress.mjs
```

Run the 10,000-CCU test against a production-like staging deployment:

```powershell
$env:LOAD_BASE_URL='https://staging.example.com'
$env:LOAD_STAGES='100,500,1000,2500,5000,10000'
$env:LOAD_STAGE_SECONDS='60'
$env:LOAD_TIMEOUT_MS='10000'
node tests/load/ccu-stress.mjs
```

Do not use the 10,000-user profile against production without an approved test
window. Run the load generator from another machine so application and generator
resources do not compete.
