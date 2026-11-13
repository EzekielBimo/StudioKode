import React, { useState, useEffect, useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import MobileMenu from './MobileMenu';

export default function Navbar() {
    const [mobileOpen, setMobileOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);
    const { url } = usePage();
    const headerRef = useRef(null);

    useEffect(() => {
        const handleScroll = () => {
            setScrolled(window.scrollY > 20);
        };
        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    // Close mobile menu on route change or Escape key
    useEffect(() => {
        setMobileOpen(false);
    }, [url]);

    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                setMobileOpen(false);
            }
        };
        if (mobileOpen) {
            window.addEventListener('keydown', handleKeyDown);
        }
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [mobileOpen]);

    // Close mobile menu on click/tap outside the navbar container
    useEffect(() => {
        if (!mobileOpen) return;
        const handlePointerDown = (e) => {
            if (headerRef.current && !headerRef.current.contains(e.target)) {
                setMobileOpen(false);
            }
        };
        document.addEventListener('pointerdown', handlePointerDown);
        return () => document.removeEventListener('pointerdown', handlePointerDown);
    }, [mobileOpen]);

    const navItems = [
        { name: 'Home', href: '/' },
        { name: 'Projects', href: '/projects' },
        { name: 'Studio', href: '/studio' },
        { name: 'Services', href: '/services' },
        { name: 'Contact', href: '/contact' },
    ];

    const isActive = (path) => {
        const pathname = (url || '').split('?')[0];
        if (path === '/') return pathname === '/';
        return pathname.startsWith(path);
    };

    return (
        <header
            ref={headerRef}
            className={`fixed top-0 inset-x-0 z-40 pointer-events-none flex flex-col items-center transition-all duration-300 ease-in-out ${
                scrolled
                    ? 'pt-2 sm:pt-3.5 px-3 sm:px-6'
                    : 'pt-0 px-0'
            }`}
        >
            {/* Docked top navbar when at top, morphs into floating glassmorphic pill when scrolled */}
            <nav
                aria-label="Main Navigation"
                className={`pointer-events-auto w-full transition-all duration-300 ease-in-out backdrop-blur-xl sm:backdrop-blur-2xl ${
                    scrolled
                        ? 'max-w-4xl h-10 sm:h-11 px-3 sm:px-5 rounded-full bg-[#0B0E0C]/85 shadow-[0_12px_36px_rgba(0,0,0,0.5),inset_0_1px_1px_rgba(255,255,255,0.3)] border border-white/20'
                        : 'max-w-full h-14 sm:h-16 px-4 sm:px-6 lg:px-8 rounded-none bg-[#0B0E0C]/80 shadow-[inset_0_-1px_0_rgba(255,255,255,0.06)] border-b border-white/10'
                }`}
            >
                <div className="w-full max-w-7xl mx-auto h-full flex items-center justify-between">
                    {/* Left Section: Brand Icon & Typography + Subtle Divider */}
                    <div className="flex items-center">
                        <Link
                            href="/"
                            className="group flex items-center gap-2 focus:outline-none"
                        >
                            {/* Hexagonal geometric brand mark matching reference */}
                            <svg
                                className="w-4 h-4 text-white group-hover:text-emerald-300 transition-colors flex-shrink-0"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path d="M8.5 3a2 2 0 0 0-1.7 1L3.3 11a2 2 0 0 0 0 2l3.5 7a2 2 0 0 0 1.7 1h7a2 2 0 0 0 1.7-1l3.5-7a2 2 0 0 0 0-2l-3.5-7A2 2 0 0 0 15.5 3h-7zm2.5 4h2l3 5-3 5h-2l-3-5 3-5z" />
                            </svg>
                            <span className="font-sans font-semibold text-sm sm:text-base tracking-tight text-[#F4F6F0] group-hover:text-emerald-300 transition-colors">
                                StudioKode
                            </span>
                        </Link>

                        {/* Subtle Vertical Divider */}
                        <div
                            className="hidden md:block h-3.5 w-px bg-white/20 ml-3.5 mr-2 lg:ml-4 lg:mr-3"
                            aria-hidden="true"
                        />
                    </div>

                    {/* Center Section: Navigation Links */}
                    <div className="hidden md:flex items-center space-x-4 lg:space-x-6 text-xs sm:text-[13px] font-medium">
                        {navItems.map((item) => {
                            const active = isActive(item.href);
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    className={`relative py-0.5 transition-colors duration-200 ${
                                        active
                                            ? 'text-white font-semibold'
                                            : 'text-[#C5C8C0] hover:text-white'
                                    }`}
                                >
                                    {item.name}
                                    {active && (
                                        <span className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-3.5 h-[2px] bg-emerald-400 rounded-full shadow-[0_0_8px_rgba(52,211,153,0.6)]" />
                                    )}
                                </Link>
                            );
                        })}
                    </div>

                    {/* Right Section: Solid Pill CTA Button & Mobile Trigger */}
                    <div className="flex items-center space-x-1.5 sm:space-x-2">
                        {/* Desktop Solid Pill CTA */}
                        <div className="hidden md:flex items-center">
                            <Link
                                href="/contact"
                                className="px-3.5 sm:px-4 py-1.5 rounded-full bg-[#1C3024] text-[#F4F6F0] text-xs font-semibold border border-emerald-500/30 hover:bg-[#F4F6F0] hover:text-neutral-900 hover:shadow-md transition-all duration-200 active:scale-[0.98] shadow-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.15)] backdrop-blur-xs"
                            >
                                Konsultasi
                            </Link>
                        </div>

                        {/* Mobile Menu Trigger */}
                        <button
                            type="button"
                            onClick={() => setMobileOpen(!mobileOpen)}
                            aria-expanded={mobileOpen}
                            aria-controls="mobile-navigation"
                            className="md:hidden p-1.5 text-[#F4F6F0] hover:text-emerald-400 hover:bg-white/10 rounded-full transition-colors focus:outline-none"
                            aria-label="Toggle navigation menu"
                        >
                            {mobileOpen ? <X className="w-4 h-4" /> : <Menu className="w-4 h-4" />}
                        </button>
                    </div>
                </div>
            </nav>

            {/* Mobile Menu Drawer */}
            <div className={`w-full flex justify-center ${scrolled ? '' : 'px-3 sm:px-6'}`}>
                <MobileMenu
                    isOpen={mobileOpen}
                    onClose={() => setMobileOpen(false)}
                    navItems={navItems}
                    currentUrl={url}
                />
            </div>
        </header>
    );
}
