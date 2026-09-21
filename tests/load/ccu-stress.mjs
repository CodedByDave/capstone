import http from 'node:http';
import https from 'node:https';
import { performance } from 'node:perf_hooks';

const baseUrl = new URL(process.env.LOAD_BASE_URL ?? 'http://127.0.0.1:8000');
const stages = (process.env.LOAD_STAGES ?? '100,500,1000,2500,5000,10000')
    .split(',')
    .map(Number)
    .filter((value) => Number.isInteger(value) && value > 0);
const durationSeconds = Number(process.env.LOAD_STAGE_SECONDS ?? 15);
const requestTimeoutMs = Number(process.env.LOAD_TIMEOUT_MS ?? 10000);
const maxErrorRate = Number(process.env.LOAD_MAX_ERROR_RATE ?? 0.05);
const maxP95Ms = Number(process.env.LOAD_MAX_P95_MS ?? 5000);

const transport = baseUrl.protocol === 'https:' ? https : http;
const agent = new transport.Agent({
    keepAlive: true,
    keepAliveMsecs: 1000,
    maxSockets: 12000,
    maxFreeSockets: 2000,
    timeout: requestTimeoutMs,
});

const journeys = [
    { path: '/', weight: 70 },
    { path: '/plans', weight: 20 },
    { path: '/login', weight: 10 },
];

function pickJourney() {
    const draw = Math.random() * 100;
    let cursor = 0;
    for (const journey of journeys) {
        cursor += journey.weight;
        if (draw < cursor) return journey.path;
    }
    return journeys[0].path;
}

function percentile(values, ratio) {
    if (!values.length) return 0;
    const sorted = [...values].sort((a, b) => a - b);
    return sorted[Math.min(sorted.length - 1, Math.ceil(sorted.length * ratio) - 1)];
}

function request(path) {
    return new Promise((resolve) => {
        const started = performance.now();
        const request = transport.request(
            new URL(path, baseUrl),
            {
                method: 'GET',
                agent,
                headers: {
                    Accept: 'text/html,application/xhtml+xml',
                    'User-Agent': 'LaundryHub-CCU-Stress-Test/1.0',
                    Connection: 'keep-alive',
                },
                timeout: requestTimeoutMs,
            },
            (response) => {
                response.resume();
                response.once('end', () => {
                    resolve({
                        ok: response.statusCode >= 200 && response.statusCode < 400,
                        status: response.statusCode,
                        latency: performance.now() - started,
                    });
                });
            },
        );

        request.once('timeout', () => request.destroy(new Error('request_timeout')));
        request.once('error', (error) => {
            resolve({
                ok: false,
                error: error.code ?? error.message,
                latency: performance.now() - started,
            });
        });
        request.end();
    });
}

const sleep = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));

async function runStage(users) {
    const deadline = performance.now() + durationSeconds * 1000;
    const results = [];

    async function virtualUser() {
        await sleep(Math.random() * 1000);
        while (performance.now() < deadline) {
            results.push(await request(pickJourney()));
            await sleep(250 + Math.random() * 750);
        }
    }

    const started = performance.now();
    await Promise.all(Array.from({ length: users }, () => virtualUser()));
    const elapsedSeconds = (performance.now() - started) / 1000;
    const failures = results.filter((result) => !result.ok);
    const latencies = results.map((result) => result.latency);
    const errors = Object.groupBy(
        failures,
        (result) => String(result.status ?? result.error ?? 'unknown'),
    );

    return {
        users,
        duration_seconds: Number(elapsedSeconds.toFixed(2)),
        requests: results.length,
        throughput_rps: Number((results.length / elapsedSeconds).toFixed(2)),
        successful: results.length - failures.length,
        failed: failures.length,
        error_rate: Number((failures.length / Math.max(results.length, 1)).toFixed(4)),
        latency_ms: {
            average: Number((latencies.reduce((sum, value) => sum + value, 0) / Math.max(latencies.length, 1)).toFixed(2)),
            p50: Number(percentile(latencies, 0.5).toFixed(2)),
            p95: Number(percentile(latencies, 0.95).toFixed(2)),
            p99: Number(percentile(latencies, 0.99).toFixed(2)),
            maximum: Number(Math.max(0, ...latencies).toFixed(2)),
        },
        errors: Object.fromEntries(
            Object.entries(errors).map(([key, values]) => [key, values.length]),
        ),
    };
}

console.log(JSON.stringify({
    target: baseUrl.origin,
    stages,
    stage_seconds: durationSeconds,
    thresholds: { max_error_rate: maxErrorRate, max_p95_ms: maxP95Ms },
}));

let failed = false;
for (const users of stages) {
    const result = await runStage(users);
    console.log(JSON.stringify(result));

    if (result.error_rate > maxErrorRate || result.latency_ms.p95 > maxP95Ms) {
        console.error(JSON.stringify({
            stopped_at_users: users,
            reason: result.error_rate > maxErrorRate ? 'error_rate_threshold' : 'latency_threshold',
        }));
        failed = true;
        break;
    }

    await sleep(3000);
}

agent.destroy();
process.exitCode = failed ? 1 : 0;
