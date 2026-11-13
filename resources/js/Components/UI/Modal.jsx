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
                    className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[#0B0E0C]/90 backdrop-blur-md"
                    onClick={onClose}
                >
                    <motion.div
                        initial={{ opacity: 0, scale: 0.96 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.96 }}
                        transition={{ duration: 0.25 }}
                        className={`relative w-full ${maxWidth} bg-[#0E1310]/95 backdrop-blur-xl text-[#F4F2ED] overflow-hidden shadow-2xl shadow-black/80 shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] border border-white/15`}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header bar */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-white/10">
                            {title ? (
                                <h3 className="font-serif text-xl tracking-tight text-[#F4F2ED] truncate pr-4">
                                    {title}
                                </h3>
                            ) : (
                                <div />
                            )}
                            <button
                                onClick={onClose}
                                className="p-1.5 text-[#A8B0A6]/70 hover:text-[#F4F2ED] hover:bg-white/10 rounded-full transition-colors"
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
