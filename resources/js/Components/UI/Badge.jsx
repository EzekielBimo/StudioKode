import React from 'react';

export default function Badge({ children, variant = 'neutral', className = '' }) {
    const variants = {
        neutral: 'bg-[rgba(20,32,24,0.65)] text-[#C4C9C3] border border-white/10 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]',
        foliage: 'bg-[#152319] bg-[#224F10]/80 text-[#88B896] text-[#B0E454] border border-[#B0E454]/20 border-white/10 shadow-xs backdrop-blur-xs shadow-[inset_0_1px_0_rgba(176,228,84,0.1)]',
        outline: 'border border-white/15 text-[#C4C9C3] hover:text-[#B0E454] backdrop-blur-xs bg-white/[0.02]',
        'outline-light': 'border border-white/25 text-[#F4F2ED] text-[#EAFFD0] backdrop-blur-xs bg-white/[0.03] shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]',
        forest: 'bg-[#224F10] text-[#B0E454] border border-[#B0E454]/25 shadow-xs backdrop-blur-xs shadow-[inset_0_1px_0_rgba(176,228,84,0.12)]',
        lime: 'bg-[#B0E454]/15 text-[#B0E454] border border-[#B0E454]/30 backdrop-blur-xs shadow-[0_0_12px_rgba(176,228,84,0.15)]',
    };

    return (
        <span
            className={`inline-block px-2.5 py-1 text-[11px] uppercase tracking-widest font-medium ${
                variants[variant] || variants.neutral
            } ${className}`}
        >
            {children}
        </span>
    );
}
