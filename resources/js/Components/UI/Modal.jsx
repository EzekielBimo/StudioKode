import React, { useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';

export default function Modal({
    show,
    isOpen,
    onClose,
    children,
    title,
    maxWidth = 'max-w-5xl',
}) {
    const isModalOpen = show ?? isOpen;

    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };

        if (isModalOpen) {
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', handleKeyDown);
        }

        return () => {
            document.body.style.overflow = 'unset';
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [isModalOpen, onClose]);

    return (
        <AnimatePresence>
            {isModalOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#0B0E0C]/90 bg-[#0D0D0D]/90 backdrop-blur-md"
                    onClick={onClose}
                >
                    <motion.div
                        initial={{ opacity: 0, scale: 0.96 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.96 }}
                        transition={{ duration: 0.25 }}
                        className={`relative w-full ${maxWidth} bg-[#0E1310]/95 bg-[#0D0D0D]/95 backdrop-blur-xl text-[#F4F2ED] text-[#EAFFD0] overflow-hidden shadow-2xl shadow-black/80 shadow-[inset_0_1px_0_rgba(176,228,84,0.1)] border border-white/15`}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header bar */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-white/10">
                            {title ? (
                                <h3 className="font-serif text-xl tracking-tight text-[#F4F2ED] text-[#EAFFD0] truncate pr-4">
                                    {title}
                                </h3>
                            ) : (
                                <div />
                            )}
                            <button
                                onClick={onClose}
                                className="p-1.5 text-[#A8B0A6]/70 hover:text-[#B0E454] hover:text-[#F4F2ED] hover:bg-[#224F10]/40 rounded-full transition-colors focus:outline-none focus:ring-1 focus:ring-[#B0E454]"
                                aria-label="Close modal"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {/* Content */}
                        <div className="p-6">{children}</div>
                    </motion.div>
                </div>
            )}
        </AnimatePresence>
    );
}
