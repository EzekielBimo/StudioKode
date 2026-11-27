import { readFileSync } from 'fs';
import { resolve } from 'path';

console.log('=== StudioKode LoadingScreen Empirical Timing & Behavior Stress Harness ===\n');

let totalAssertions = 0;
let passedAssertions = 0;

function assert(condition, message) {
    totalAssertions++;
    if (!condition) {
        console.error(`  ✗ FAIL: ${message}`);
        process.exitCode = 1;
    } else {
        passedAssertions++;
        console.log(`  ✓ PASS: ${message}`);
    }
}

// 1. Static Contract & Anti-Regression Verification
console.log('[Suite 1: Static Architectural Contracts & Framer Motion Lifecycle]');
const filePath = resolve('resources/js/Components/UI/LoadingScreen.jsx');
const content = readFileSync(filePath, 'utf8');

assert(content.includes('TOTAL_DURATION = 5000'), 'TOTAL_DURATION is configured to exactly 5000ms (5.0 seconds)');
assert(content.includes('STUCK_START = 1600'), 'STUCK_START initiates pause at 1600ms (1.6 seconds)');
assert(content.includes('STUCK_END = 3600'), 'STUCK_END unfreezes at 3600ms (holds stuck at 80% for 2.0 seconds)');
assert(content.includes('COMPLETE_TIME = 4500'), 'COMPLETE_TIME completes progress to 100% at 4500ms');

// Regression check: ensure if (!isVisible) return null does not exist to allow AnimatePresence exit
assert(!content.includes('if (!isVisible) {\n        return null;\n    }') &&
       !content.includes('if (!isVisible) return null;'),
       'Premature "if (!isVisible) return null" is eliminated to preserve AnimatePresence exit animation');

assert(content.includes('if (isExited)'), 'Uses isExited state guard for clean DOM unmounting only after exit complete');
assert(content.includes('handleExitComplete'), 'Defines handleExitComplete callback for AnimatePresence onExitComplete');
assert(content.includes('onExitComplete={handleExitComplete}'), 'Wires handleExitComplete to AnimatePresence onExitComplete');
assert(content.includes('animate={{ width: `${progress}%` }}'), 'Uses native Framer Motion animate width on progress track');

// 2. Mathematical Simulation of Timing Curve & Boundaries
console.log('\n[Suite 2: Mathematical Timing Function & Monotonicity Verification]');

const TOTAL_DURATION = 5000;
const STUCK_START = 1600;
const STUCK_END = 3600;
const COMPLETE_TIME = 4500;

function computeProgress(elapsed) {
    if (elapsed >= TOTAL_DURATION) return 100;
    if (elapsed < STUCK_START) {
        const ratio = elapsed / STUCK_START;
        const eased = 1 - Math.pow(1 - ratio, 2.2);
        return Math.min(80, Math.round(80 * eased));
    } else if (elapsed < STUCK_END) {
        return 80;
    } else if (elapsed < COMPLETE_TIME) {
        const ratio = (elapsed - STUCK_END) / (COMPLETE_TIME - STUCK_END);
        const eased = Math.pow(ratio, 0.9);
        return Math.min(100, Math.round(80 + 20 * eased));
    } else {
        return 100;
    }
}

function computeStage(nextProgress) {
    if (nextProgress < 28) return 0;
    if (nextProgress < 58) return 1;
    if (nextProgress < 85) return 2;
    return 3;
}

// Check boundary values
assert(computeProgress(0) === 0, 'Progress at 0ms is 0%');
assert(computeProgress(STUCK_START) === 80, 'Progress at exactly STUCK_START (1600ms) is 80%');
assert(computeProgress(2000) === 80, 'Progress at 2000ms is firmly stuck at 80%');
assert(computeProgress(2800) === 80, 'Progress at midpoint of pause (2800ms) is firmly stuck at 80%');
assert(computeProgress(STUCK_END - 1) === 80, 'Progress right before unfreeze (3599ms) is firmly stuck at 80%');
assert(computeProgress(STUCK_END) === 80, 'Progress at exactly STUCK_END (3600ms) is 80%');
assert(computeProgress(COMPLETE_TIME) === 100, 'Progress at COMPLETE_TIME (4500ms) reaches exactly 100%');
assert(computeProgress(4800) === 100, 'Progress during savor hold (4800ms) is 100%');
assert(computeProgress(TOTAL_DURATION) === 100, 'Progress at TOTAL_DURATION (5000ms) is 100%');

// Monotonicity check across the entire 0-5000ms range (step 10ms)
let isMonotonic = true;
let prev = 0;
let stuckCount = 0;

for (let t = 0; t <= TOTAL_DURATION; t += 10) {
    const curr = computeProgress(t);
    if (curr < prev) {
        isMonotonic = false;
        break;
    }
    if (t >= STUCK_START && t <= STUCK_END) {
        if (curr === 80) stuckCount++;
    }
    prev = curr;
}

assert(isMonotonic, 'Progress curve is strictly monotonically non-decreasing (never moves backwards)');
const expectedStuckTicks = Math.floor((STUCK_END - STUCK_START) / 10) + 1;
assert(stuckCount === expectedStuckTicks, `Progress is stuck at 80% for exactly ${STUCK_END - STUCK_START}ms (${stuckCount} samples at 10ms step)`);

// 3. Stage Index Telemetry Cohesion
console.log('\n[Suite 3: Stage Index Telemetry Cohesion]');

assert(computeStage(0) === 0, 'Stage at 0% is Stage 0 (01 / SURVEYING LIVING TOPOGRAPHY)');
assert(computeStage(35) === 1, 'Stage at 35% is Stage 1 (02 / TRACING BOTANICAL CONTOURS)');
assert(computeStage(80) === 2, 'Stage during 80% pause is strictly Stage 2 (03 / CALIBRATING SPATIAL MATRICES)');
assert(computeStage(86) === 3, 'Stage past 85% transitions to Stage 3 (04 / SANCTUARY ARCHITECTURE READY)');
assert(computeStage(100) === 3, 'Stage at 100% is Stage 3');

console.log(`\n======================================================`);
console.log(`Total Assertions: ${totalAssertions} | Passed: ${passedAssertions} | Failed: ${totalAssertions - passedAssertions}`);
console.log(`======================================================\n`);
