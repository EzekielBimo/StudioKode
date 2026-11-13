import React from 'react';
import { Link } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';

export default function MobileMenu({ isOpen, onClose, navItems = [], currentUrl = '' }) {
    const pathname = (currentUrl || '').split('?')[0];

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    id="mobile-navigation"
                    aria-label="Mobile Navigation"
                    initial={{ opacity: 0, y: -10, scale: 0.98 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, y: -10, scale: 0.98 }}
                    transition={{ duration: 0.25, ease: [0.21, 0.47, 0.32, 0.98] }}
                    className="pointer-events-auto md:hidden w-full max-w-lg mt-2 mx-auto rounded-3xl border border-white/10 bg-[#0E1310]/95 backdrop-blur-xl px-6 py-6 shadow-2xl shadow-[inset_0_1px_0_rgba(255,255,255,0.15)] overflow-hidden"
                >
                    <nav className="flex flex-col space-y-2 text-sm font-medium">
                        {navItems.map((item) => {
                            const active = item.href === '/' ? pathname === '/' : pathname.startsWith(item.href);
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    onClick={onClose}
                                    className={`py-2 px-3 rounded-xl transition-all ${
                                        active
                                            ? 'text-emerald-400 font-semibold bg-white/5 border-l-2 border-emerald-400'
                                            : 'text-[#C5C8C0] hover:text-white hover:bg-white/5'
                                    }`}
                                >
                                    {item.name}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="mt-6 pt-5 border-t border-white/10 flex flex-col space-y-3">
                        <span className="text-xs text-[#A7ACA0]/70 font-light text-center">
                            Kyoto &middot; Z&uuml;rich &middot; Melbourne
                        </span>
                        <Link
                            href="/contact"
                            onClick={onClose}
                            className="w-full text-center py-3 rounded-full bg-[#1C3024] text-[#F4F6F0] border border-emerald-500/30 font-semibold text-xs sm:text-sm hover:bg-[#F4F6F0] hover:text-neutral-900 transition-colors shadow-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] backdrop-blur-xs"
                        >
                            Konsultasi
                        </Link>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
