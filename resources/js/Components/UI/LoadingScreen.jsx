import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

// In-memory runtime session flag: ensures loading animation plays on fresh site landing / reload,
// but prevents repetitive blocking on client-side Inertia SPA link navigation.
let hasLoadedInSession = false;

export default function LoadingScreen({ onComplete }) {
    // Determine visibility synchronously on initial render pass to prevent 1-frame page flash during hydration.
    const [isVisible, setIsVisible] = useState(() => {
        if (typeof window === 'undefined') return false;
        const isPreview = window.location.search.includes('loader');
        if (hasLoadedInSession && !isPreview) return false;
        return true;
    });

    const [progress, setProgress] = useState(0);
    const [stageIndex, setStageIndex] = useState(0);

    const stages = [
        { label: '01 / SURVEYING LIVING TOPOGRAPHY', telemetry: 'LAT -08°31\'S • ELEV +48.2M' },
        { label: '02 / TRACING BOTANICAL CONTOURS', telemetry: 'ISOBARS CALIBRATED • 10M RES' },
        { label: '03 / CALIBRATING SPATIAL MATRICES', telemetry: 'MICROCLIMATE MATRIX ALIGNED' },
        { label: '04 / SANCTUARY ARCHITECTURE READY', telemetry: 'STUDIOKODE MASTERPLAN ENGAGED' },
    ];

    useEffect(() => {
        if (!isVisible) {
            return;
        }

        const prefersReduced = typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReduced) {
            hasLoadedInSession = true;
            setIsVisible(false);
            return;
        }

        const interval = setInterval(() => {
            setProgress((prev) => {
                if (prev >= 100) {
                    clearInterval(interval);
                    return 100;
                }
                // Non-linear organic progress curve: swift initial reveal, stately completion
                const remaining = 100 - prev;
                const increment = Math.max(1, Math.min(4, Math.ceil(remaining * 0.11)));
                const next = Math.min(100, prev + increment);

                if (next < 28) {
                    setStageIndex(0);
                } else if (next < 62) {
                    setStageIndex(1);
                } else if (next < 88) {
                    setStageIndex(2);
                } else {
                    setStageIndex(3);
                }

                return next;
            });
        }, 30);

        return () => clearInterval(interval);
    }, [isVisible]);

    useEffect(() => {
        if (progress === 100 && isVisible) {
            const timer = setTimeout(() => {
                hasLoadedInSession = true;
                setIsVisible(false);
            }, 320);
            return () => clearTimeout(timer);
        }
    }, [progress, isVisible]);

    const handleExitComplete = () => {
        hasLoadedInSession = true;
        if (onComplete) {
            onComplete();
        }
    };

    if (!isVisible) {
        return null;
    }

    const currentStage = stages[stageIndex];

    return (
        <AnimatePresence onExitComplete={handleExitComplete}>
            {isVisible && (
                <motion.div
                    key="studiokode-loading-sanctuary"
                    role="status"
                    aria-label="StudioKode Initializing Sanctuary"
                    aria-live="polite"
                    initial={{ opacity: 1 }}
                    exit={{
                        opacity: 0,
                        scale: 1.05,
                        filter: 'blur(14px)',
                        transition: { duration: 0.75, ease: [0.22, 1, 0.36, 1] },
                    }}
                    style={{ willChange: 'opacity, transform, filter' }}
                    className="fixed inset-0 z-50 pointer-events-auto exit:pointer-events-none flex flex-col items-center justify-center bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] select-none overflow-hidden"
                >
                    {/* Living Topographic Contour Layer (motion.dev SVG Path Animations) */}
                    <div
                        className="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden"
                        aria-hidden="true"
                    >
                        <motion.svg
                            initial={{ opacity: 0, scale: 0.92 }}
                            animate={{ opacity: 0.22, scale: 1, rotate: 360 }}
                            transition={{
                                opacity: { duration: 1.4, ease: 'easeOut' },
                                scale: { duration: 1.8, ease: 'easeOut' },
                                rotate: { duration: 240, repeat: Infinity, ease: 'linear' },
                            }}
                            className="w-[300px] h-[300px] sm:w-[740px] sm:h-[740px] md:w-[1020px] md:h-[1020px] text-[#88B896]"
                            viewBox="0 0 800 800"
                            fill="none"
                            stroke="currentColor"
                        >
                            {/* Inner Topographic Elevation Contour Rings mirroring the 'O' contour motif */}
                            <motion.circle
                                cx="400"
                                cy="400"
                                r="85"
                                strokeWidth="1.2"
                                strokeDasharray="3 5"
                                initial={{ pathLength: 0, opacity: 0 }}
                                animate={{ pathLength: 1, opacity: 0.4 }}
                                transition={{ duration: 1.2, delay: 0.1, ease: [0.16, 1, 0.3, 1] }}
                            />
                            <motion.ellipse
                                cx="400"
                                cy="400"
                                rx="150"
                                ry="130"
                                strokeWidth="1.2"
                                initial={{ pathLength: 0, opacity: 0 }}
                                animate={{ pathLength: 1, opacity: 0.5 }}
                                transition={{ duration: 1.4, delay: 0.2, ease: [0.16, 1, 0.3, 1] }}
                            />
                            <motion.path
                                d="M 210 400 C 260 290, 350 270, 400 270 C 460 270, 540 300, 590 400 C 550 510, 450 530, 400 530 C 340 530, 250 500, 210 400 Z"
                                strokeWidth="1.4"
                                initial={{ pathLength: 0, opacity: 0 }}
                                animate={{ pathLength: 1, opacity: 0.6 }}
                                transition={{ duration: 1.6, delay: 0.3, ease: [0.16, 1, 0.3, 1] }}
                            />
                            <motion.path
                                d="M 140 400 C 210 230, 330 210, 400 210 C 490 210, 590 250, 660 400 C 600 570, 480 590, 400 590 C 310 590, 190 550, 140 400 Z"
                                strokeWidth="1.2"
                                strokeDasharray="5 7"
                                initial={{ pathLength: 0, opacity: 0 }}
                                animate={{ pathLength: 1, opacity: 0.45 }}
                                transition={{ duration: 1.8, delay: 0.4, ease: [0.16, 1, 0.3, 1] }}
                            />
                            <motion.path
                                d="M 70 400 C 150 160, 310 140, 400 140 C 530 140, 650 180, 730 400 C 670 640, 510 660, 400 660 C 270 660, 130 620, 70 400 Z"
                                strokeWidth="1.0"
                                initial={{ pathLength: 0, opacity: 0 }}
                                animate={{ pathLength: 1, opacity: 0.3 }}
                                transition={{ duration: 2.0, delay: 0.5, ease: [0.16, 1, 0.3, 1] }}
                            />
                        </motion.svg>
                    </div>

                    {/* Ambient Forest & Lime Green Radial Aura */}
                    <motion.div
                        initial={{ opacity: 0, scale: 0.85 }}
                        animate={{
                            opacity: [0.35, 0.65, 0.35],
                            scale: [0.95, 1.08, 0.95],
                        }}
                        transition={{
                            duration: 4.5,
                            repeat: Infinity,
                            ease: 'easeInOut',
                        }}
                        className="absolute w-[300px] h-[300px] sm:w-[520px] sm:h-[520px] rounded-full bg-radial from-[#224F10]/50 via-[#142018]/35 to-transparent blur-3xl pointer-events-none"
                        aria-hidden="true"
                    />

                    {/* Architectural Hero Container */}
                    <div className="relative z-10 flex flex-col items-center text-center px-6 max-w-md w-full">
                        {/* Emblem Plaque with Contour Scan Shimmer & Precision Brackets */}
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9, y: 18 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            transition={{ duration: 0.95, ease: [0.16, 1, 0.3, 1] }}
                            className="relative mb-7"
                        >
                            {/* Radial Lime Halo */}
                            <motion.div
                                animate={{
                                    opacity: [0.25, 0.55, 0.25],
                                    scale: [0.96, 1.04, 0.96],
                                }}
                                transition={{
                                    duration: 3.2,
                                    repeat: Infinity,
                                    ease: 'easeInOut',
                                }}
                                className="absolute inset-0 -m-4 bg-gradient-to-r from-[#224F10]/60 via-[#B0E454]/25 to-[#224F10]/60 rounded-3xl blur-2xl"
                                aria-hidden="true"
                            />

                            {/* Frosted Glass Emblem Plaque with Architectural Coordinates */}
                            <div className="relative p-5 sm:p-6 rounded-3xl bg-[#142018]/80 border border-[#23382B]/90 shadow-[0_24px_60px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.18)] backdrop-blur-2xl overflow-hidden">
                                {/* Precision Architectural Corner Crosshairs */}
                                <div className="absolute top-2 left-2 text-[9px] font-mono text-[#88B896]/40 select-none">+</div>
                                <div className="absolute top-2 right-2 text-[9px] font-mono text-[#88B896]/40 select-none">+</div>
                                <div className="absolute bottom-2 left-2 text-[9px] font-mono text-[#88B896]/40 select-none">+</div>
                                <div className="absolute bottom-2 right-2 text-[9px] font-mono text-[#88B896]/40 select-none">+</div>

                                {/* Official StudioKode Brand Emblem */}
                                <picture className="relative z-10 block">
                                    <source srcSet="/assets/logo/kode-logo.webp" type="image/webp" />
                                    <img
                                        src="/assets/logo/kode-logo.png"
                                        alt="StudioKode Official Emblem"
                                        className="h-12 sm:h-16 w-auto object-contain filter drop-shadow-[0_0_18px_rgba(176,228,84,0.38)]"
                                        width="1024"
                                        height="409"
                                        loading="eager"
                                    />
                                </picture>

                                {/* Dynamic Contour Scan Shimmer Beam across Logo Surface */}
                                <motion.div
                                    initial={{ x: '-160%', opacity: 0 }}
                                    animate={{ x: '160%', opacity: [0, 0.85, 0] }}
                                    transition={{
                                        duration: 1.8,
                                        delay: 0.45,
                                        ease: [0.16, 1, 0.3, 1],
                                        repeat: Infinity,
                                        repeatDelay: 3.5,
                                    }}
                                    className="absolute inset-0 z-20 w-1/2 h-full bg-gradient-to-r from-transparent via-[#EAFFD0]/20 to-transparent skew-x-[-25deg] pointer-events-none"
                                    aria-hidden="true"
                                />
                            </div>
                        </motion.div>

                        {/* Editorial Typography & Topographic Coordinates */}
                        <motion.div
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.85, delay: 0.18, ease: [0.16, 1, 0.3, 1] }}
                            className="space-y-1.5 mb-7"
                        >
                            <h2 className="text-xs sm:text-sm uppercase tracking-[0.36em] text-[#EAFFD0] font-sans font-medium">
                                StudioKode
                            </h2>
                            <p className="text-[10px] tracking-[0.24em] text-[#88B896] uppercase font-mono font-light">
                                Landscape Architecture &bull; Living Topography
                            </p>
                        </motion.div>

                        {/* Precision Architectural Progress Meter */}
                        <motion.div
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.85, delay: 0.3, ease: [0.16, 1, 0.3, 1] }}
                            className="w-full max-w-xs space-y-3"
                        >
                            {/* Hairline Progress Track */}
                            <div className="relative h-[2.5px] w-full bg-[#16221A] rounded-full overflow-hidden border border-white/5">
                                <motion.div
                                    className="h-full bg-gradient-to-r from-[#224F10] via-[#88B896] to-[#B0E454] rounded-full shadow-[0_0_12px_rgba(176,228,84,0.75)]"
                                    style={{ width: `${progress}%` }}
                                    transition={{ ease: 'easeOut', duration: 0.08 }}
                                />
                            </div>

                            {/* Phase Telemetry and Percentage Readout */}
                            <div className="flex items-center justify-between text-[10px] font-mono tracking-widest text-[#A8B0A6]">
                                <span className="text-[#88B896]/95 transition-all duration-300">
                                    {currentStage.label}
                                </span>
                                <span className="text-[#EAFFD0] tabular-nums font-semibold ml-2">
                                    {String(progress).padStart(2, '0')}%
                                </span>
                            </div>

                            {/* Subtle Architectural Survey Telemetry Coordinate */}
                            <div className="text-[9px] font-mono tracking-[0.22em] text-[#88B896]/50 uppercase text-center pt-1">
                                {currentStage.telemetry}
                            </div>
                        </motion.div>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
