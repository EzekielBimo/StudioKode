// Contrast Audit Tool for StudioKode Smoked Obsidian / Dark Moss Glass Theme
// Uses standard WCAG 2.1 relative luminance and contrast ratio algorithms.

function parseHex(hex) {
    hex = hex.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }
    const num = parseInt(hex, 16);
    return [(num >> 16) & 255, (num >> 8) & 255, num & 255];
}

function alphaComposite(fgRgb, bgRgb, alpha) {
    return [
        Math.round(alpha * fgRgb[0] + (1 - alpha) * bgRgb[0]),
        Math.round(alpha * fgRgb[1] + (1 - alpha) * bgRgb[1]),
        Math.round(alpha * fgRgb[2] + (1 - alpha) * bgRgb[2]),
    ];
}

function srgbToLinear(c) {
    const val = c / 255;
    return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
}

function relativeLuminance(rgb) {
    const r = srgbToLinear(rgb[0]);
    const g = srgbToLinear(rgb[1]);
    const b = srgbToLinear(rgb[2]);
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function contrastRatio(rgb1, rgb2) {
    const l1 = relativeLuminance(rgb1);
    const l2 = relativeLuminance(rgb2);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
}

const obsidianBase = parseHex('#0B0E0C');
const obsidianSurface = parseHex('#0E1310');
const obsidianElevated = parseHex('#121814');
const obsidianFooter = parseHex('#080B09');
const mossGlassBg = alphaComposite([20, 32, 24], obsidianBase, 0.65);
const mossGlassButtonBg = alphaComposite([20, 32, 24], obsidianBase, 0.75);
const foliageButtonBg = parseHex('#1C2A21');
const foliageButtonHover = parseHex('#283C2F');
const foliageBadgeBg = parseHex('#152319');
const adminActiveNavBg = alphaComposite([16, 185, 129], obsidianSurface, 0.15);
const flashSuccessBg = parseHex('#142018');
const flashErrorBg = parseHex('#4C0519');

const pairings = [
    // Primary Typography
    { name: 'Primary Cream (#F4F6F0) on Obsidian Canvas (#0B0E0C)', fg: parseHex('#F4F6F0'), bg: obsidianBase, target: 4.5 },
    { name: 'Primary Cream (#F4F2ED) on Card Surface (#0E1310)', fg: parseHex('#F4F2ED'), bg: obsidianSurface, target: 4.5 },
    { name: 'Primary Cream (#F4F2ED) on Elevated Surface (#121814)', fg: parseHex('#F4F2ED'), bg: obsidianElevated, target: 4.5 },
    { name: 'Primary Cream (#F5F2EB) on Admin Canvas (#0B0E0C)', fg: parseHex('#F5F2EB'), bg: obsidianBase, target: 4.5 },
    { name: 'Primary Cream (#F4F6F0) on Footer Canvas (#080B09)', fg: parseHex('#F4F6F0'), bg: obsidianFooter, target: 4.5 },

    // Secondary & Sage Typography
    { name: 'Secondary Sage (#C5C8C0) on Obsidian Canvas (#0B0E0C)', fg: parseHex('#C5C8C0'), bg: obsidianBase, target: 4.5 },
    { name: 'Secondary Sage (#C5C8C0) on Card Surface (#0E1310)', fg: parseHex('#C5C8C0'), bg: obsidianSurface, target: 4.5 },
    { name: 'Secondary Sage (#C4C9C3) on Elevated Surface (#121814)', fg: parseHex('#C4C9C3'), bg: obsidianElevated, target: 4.5 },
    { name: 'Secondary Sage Stone (#A7ACA0) on Obsidian Canvas (#0B0E0C)', fg: parseHex('#A7ACA0'), bg: obsidianBase, target: 4.5 },
    { name: 'Secondary Sage Stone (#A7ACA0) on Card Surface (#0E1310)', fg: parseHex('#A7ACA0'), bg: obsidianSurface, target: 4.5 },
    { name: 'Secondary Sage Stone (#A7ACA0) on Footer Canvas (#080B09)', fg: parseHex('#A7ACA0'), bg: obsidianFooter, target: 4.5 },
    { name: 'Admin Sidebar Inactive Text (70% #E6E2DD) on #0E1310', fg: alphaComposite(parseHex('#E6E2DD'), obsidianSurface, 0.70), bg: obsidianSurface, target: 4.5 },

    // Muted & Tertiary
    { name: 'Muted Stone (#7C8375) on Obsidian Canvas (#0B0E0C)', fg: parseHex('#7C8375'), bg: obsidianBase, target: 3.0 },
    { name: 'Muted Stone (#7C8375) on Footer Canvas (#080B09)', fg: parseHex('#7C8375'), bg: obsidianFooter, target: 3.0 },
    { name: 'Input Label (#C4C9C3) on Elevated Surface (#121814)', fg: parseHex('#C4C9C3'), bg: obsidianElevated, target: 4.5 },
    { name: 'Input Placeholder (40% #A8B0A6) on Elevated Surface (#121814)', fg: alphaComposite(parseHex('#A8B0A6'), obsidianElevated, 0.40), bg: obsidianElevated, target: 1.5 },

    // Buttons
    { name: 'Button Primary Text (#F4F2ED) on Foliage (#1C2A21)', fg: parseHex('#F4F2ED'), bg: foliageButtonBg, target: 4.5 },
    { name: 'Button Primary Text (#F4F2ED) on Hover Foliage (#283C2F)', fg: parseHex('#F4F2ED'), bg: foliageButtonHover, target: 4.5 },
    { name: 'Button Secondary Text (#F4F2ED) on Moss Glass (0.75 rgba)', fg: parseHex('#F4F2ED'), bg: mossGlassButtonBg, target: 4.5 },
    { name: 'Button Outline Text (#88B896) on Obsidian Canvas (#0B0E0C)', fg: parseHex('#88B896'), bg: obsidianBase, target: 4.5 },
    { name: 'Button Outline-Light Text (#F4F2ED) on Canvas (#0B0E0C)', fg: parseHex('#F4F2ED'), bg: obsidianBase, target: 4.5 },
    { name: 'Button Ghost Text (#C4C9C3) on Canvas (#0B0E0C)', fg: parseHex('#C4C9C3'), bg: obsidianBase, target: 4.5 },

    // Badges
    { name: 'Badge Neutral Text (#C4C9C3) on Moss Glass (0.65 rgba)', fg: parseHex('#C4C9C3'), bg: mossGlassBg, target: 4.5 },
    { name: 'Badge Foliage Text (#88B896) on Dark Foliage (#152319)', fg: parseHex('#88B896'), bg: foliageBadgeBg, target: 4.5 },
    { name: 'Badge Outline Text (#C4C9C3) on Canvas (#0B0E0C)', fg: parseHex('#C4C9C3'), bg: obsidianBase, target: 4.5 },
    { name: 'Badge Outline-Light Text (#F4F2ED) on Canvas (#0B0E0C)', fg: parseHex('#F4F2ED'), bg: obsidianBase, target: 4.5 },

    // Navigation & Active Links
    { name: 'Desktop Navbar Active Link Emerald-400 (#34D399) on #0B0E0C', fg: parseHex('#34D399'), bg: obsidianBase, target: 4.5 },
    { name: 'Desktop Navbar Inactive Link (#C5C8C0) on #0B0E0C', fg: parseHex('#C5C8C0'), bg: obsidianBase, target: 4.5 },
    { name: 'Mobile Menu Active Link Emerald-400 (#34D399) on #0E1310', fg: parseHex('#34D399'), bg: obsidianSurface, target: 4.5 },
    { name: 'Mobile Menu Inactive Link (#C5C8C0) on #0E1310', fg: parseHex('#C5C8C0'), bg: obsidianSurface, target: 4.5 },
    { name: 'Admin Active Nav Emerald-300 (#6EE7B7) on Active Glass', fg: parseHex('#6EE7B7'), bg: adminActiveNavBg, target: 4.5 },

    // Selection & Alerts
    { name: 'Selection Text (#F4F6F0) on Dark Moss Selection (#1C3024)', fg: parseHex('#F4F6F0'), bg: parseHex('#1C3024'), target: 4.5 },
    { name: 'Admin Selection Text (#F9F8F6) on Selection (#1C2A21)', fg: parseHex('#F9F8F6'), bg: parseHex('#1C2A21'), target: 4.5 },
    { name: 'Flash Success Text (#A7F3D0) on #142018', fg: parseHex('#A7F3D0'), bg: flashSuccessBg, target: 4.5 },
    { name: 'Flash Error Text (#FECDD3) on Rose-950 (#4C0519)', fg: parseHex('#FECDD3'), bg: flashErrorBg, target: 4.5 },
    { name: 'Input Error Text Rose-400 (#FB7185) on Elevated (#121814)', fg: parseHex('#FB7185'), bg: obsidianElevated, target: 4.5 },
];

console.log('| Pairing | Ratio | Target | WCAG AA | WCAG AAA |');
console.log('|---|---|---|---|---|');

let failures = 0;
for (const p of pairings) {
    const ratio = contrastRatio(p.fg, p.bg);
    const aaPass = ratio >= p.target ? 'PASS' : 'FAIL';
    const aaaPass = ratio >= 7.0 ? 'PASS' : (ratio >= 4.5 ? 'AA-ONLY' : 'FAIL');
    if (aaPass === 'FAIL') failures++;
    console.log(`| ${p.name} | ${ratio.toFixed(2)}:1 | ${p.target}:1 | ${aaPass} | ${aaaPass} |`);
}

console.log(`\nTotal checked: ${pairings.length}, Failures: ${failures}`);
if (failures > 0) process.exit(1);
