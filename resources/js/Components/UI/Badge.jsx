import React from 'react';

export default function Badge({ children, variant = 'neutral', className = '' }) {
    const variants = {
        neutral: 'bg-[rgba(20,32,24,0.65)] text-[#C4C9C3] border border-white/10 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]',
        foliage: 'bg-[#152319] text-[#88B896] border border-emerald-500/30 shadow-xs backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]',
        outline: 'border border-white/15 text-[#C4C9C3] backdrop-blur-xs bg-white/[0.02]',
        'outline-light': 'border border-white/25 text-[#F4F2ED] backdrop-blur-xs bg-white/[0.03] shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]',
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
