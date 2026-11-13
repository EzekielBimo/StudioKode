import React from 'react';

export default function Input({
    label,
    error,
    id,
    name,
    type = 'text',
    multiline = false,
    rows = 4,
    required = false,
    className = '',
    ...props
}) {
    const inputId = id || name;
    const baseClasses = `w-full px-4 py-2.5 bg-[#121814]/90 backdrop-blur-sm shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] border text-[#F4F2ED] text-sm focus:outline-none transition-colors duration-200 placeholder:text-[#A8B0A6]/40 ${
        error
            ? 'border-rose-500/80 focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20'
            : 'border-white/15 focus:border-[#88B896] focus:ring-1 focus:ring-[#88B896]/30'
    } ${className}`;

    return (
        <div className="w-full">
            {label && (
                <label
                    htmlFor={inputId}
                    className="block text-xs uppercase tracking-wider font-medium text-[#C4C9C3] mb-1.5"
                >
                    {label}
                    {required && <span className="text-rose-400 ml-1">*</span>}
                </label>
            )}

            {multiline || type === 'textarea' ? (
                <textarea
                    id={inputId}
                    name={name}
                    rows={rows}
                    required={required}
                    className={baseClasses}
                    {...props}
                />
            ) : (
                <input
                    id={inputId}
                    name={name}
                    type={type}
                    required={required}
                    className={baseClasses}
                    {...props}
                />
            )}

            {error && <p className="mt-1.5 text-xs text-rose-400 font-medium">{error}</p>}
        </div>
    );
}
