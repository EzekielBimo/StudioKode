import React, { useState, useEffect, useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu, X, ChevronDown } from 'lucide-react';
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
        { name: 'Projects', href: '/projects' },
        { name: 'Studio', href: '/studio' },
        { name: 'Contact', href: '/contact' },
    ];

    const isActive = (path) => {
        const pathname = (url || '').split('?')[0];
        if (path === '/') return pathname === '/';
        return pathname.startsWith(path);
    };

    const activeCategory = (() => {
        if (!url) return null;
        const [pathname, search] = url.split('?');
        if (pathname !== '/projects') return null;
        if (!search) return 'all';
        const params = new URLSearchParams(search);
        const cat = (params.get('category') || '').toLowerCase().trim();
        if (cat === 'design' || cat === 'project design' || cat === 'project-design') return 'design';
        if (cat === 'construction' || cat === 'project construction' || cat === 'project-construction') return 'construction';
        return 'all';
    })();

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
                        ? 'max-w-4xl h-10 sm:h-11 px-3 sm:px-5 rounded-full bg-jet/75 bg-[#0D0D0D]/75 bg-[#0B0E0C]/75 shadow-[0_16px_36px_rgba(0,0,0,0.6),inset_0_1px_1px_rgba(255,255,255,0.15)] border border-white/10 border-forest/40'
                        : 'max-w-full h-14 sm:h-16 px-4 sm:px-6 lg:px-8 rounded-none bg-jet/75 bg-[#0D0D0D]/75 bg-[#0B0E0C]/75 shadow-[inset_0_-1px_0_rgba(255,255,255,0.06)] border-b border-white/10'
                }`}
            >
                <div className="w-full max-w-7xl mx-auto h-full flex items-center justify-between">
                    {/* Left Section: Brand Logo (Standalone Emblem) + Subtle Divider */}
                    <div className="flex items-center">
                        <Link
                            href="/"
                            className="group flex items-center focus:outline-none"
                            aria-label="StudioKode Home"
                        >
                            {/* Official StudioKode brand emblem with organic topographic contours */}
                            <picture className="flex items-center">
                                <source srcSet="/assets/logo/kode-logo.webp" type="image/webp" />
                                <img
                                    src="/assets/logo/kode-logo.png"
                                    alt="StudioKode Logo"
                                    className="h-6 sm:h-7 w-auto object-contain transition-all duration-300 group-hover:scale-105 group-hover:drop-shadow-[0_0_12px_rgba(176,228,84,0.45)] drop-shadow-[0_0_8px_rgba(176,228,84,0.2)] flex-shrink-0"
                                    width="1024"
                                    height="409"
                                    loading="eager"
                                />
                            </picture>
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
                            if (item.name === 'Projects') {
                                return (
                                    <div key={item.name} className="relative group">
                                        <Link
                                            href={item.href}
                                            aria-haspopup="true"
                                            className={`relative py-1 transition-colors duration-200 inline-flex items-center gap-1 ${
                                                active
                                                    ? 'text-white font-semibold'
                                                    : 'text-[#C5C8C0] hover:text-white'
                                            }`}
                                        >
                                            <span>{item.name}</span>
                                            <ChevronDown className="w-3 h-3 text-[#A8B0A6]/60 group-hover:text-lime group-focus-within:text-lime group-hover:text-emerald-400 group-focus-within:text-emerald-400 transition-transform duration-200 group-hover:rotate-180 group-focus-within:rotate-180" />
                                            {active && (
                                                <span className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-[2px] bg-lime bg-emerald-400 rounded-full shadow-[0_0_10px_rgba(176,228,84,0.65)] shadow-[0_0_8px_rgba(52,211,153,0.6)]" />
                                            )}
                                        </Link>

                                        {/* Dropdown Flyout Card */}
                                        <div className="absolute top-full left-1/2 -translate-x-1/2 pt-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto group-focus-within:opacity-100 group-focus-within:pointer-events-auto focus-within:opacity-100 focus-within:pointer-events-auto transition-all duration-200 z-50">
                                            <div className="w-64 p-2 rounded-2xl bg-jet-surface/95 bg-[#0D0D0D]/95 bg-[#0E1310]/95 backdrop-blur-xl border border-white/10 shadow-[0_16px_40px_rgba(0,0,0,0.8),0_0_0_1px_rgba(34,79,16,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] space-y-1">
                                                <Link
                                                    href="/projects"
                                                    className={`flex flex-col p-2.5 rounded-xl transition-colors group/item ${
                                                        activeCategory === 'all'
                                                            ? 'bg-forest/30 bg-white/10 text-lime text-emerald-300'
                                                            : 'hover:bg-white/5'
                                                    }`}
                                                >
                                                    <span className={`text-xs font-semibold ${
                                                        activeCategory === 'all' ? 'text-lime text-emerald-300' : 'text-ivory text-[#F4F6F0]'
                                                    } group-hover/item:text-lime group-hover/item:text-emerald-300`}>
                                                        All Works
                                                    </span>
                                                    <span className="text-[11px] text-[#A8B0A6]/70 mt-0.5 font-light">
                                                        Complete landscape portfolio
                                                    </span>
                                                </Link>
                                                <Link
                                                    href="/projects?category=design"
                                                    className={`flex flex-col p-2.5 rounded-xl transition-colors group/item ${
                                                        activeCategory === 'design'
                                                            ? 'bg-forest/30 bg-white/10 text-lime text-emerald-300'
                                                            : 'hover:bg-white/5'
                                                    }`}
                                                >
                                                    <span className={`text-xs font-semibold ${
                                                        activeCategory === 'design' ? 'text-lime text-emerald-300' : 'text-ivory text-[#F4F6F0]'
                                                    } group-hover/item:text-lime group-hover/item:text-emerald-300`}>
                                                        Project Design
                                                    </span>
                                                    <span className="text-[11px] text-[#A8B0A6]/70 mt-0.5 font-light">
                                                        Garden concept &amp; spatial masterplans
                                                    </span>
                                                </Link>
                                                <Link
                                                    href="/projects?category=construction"
                                                    className={`flex flex-col p-2.5 rounded-xl transition-colors group/item ${
                                                        activeCategory === 'construction'
                                                            ? 'bg-forest/30 bg-white/10 text-lime text-emerald-300'
                                                            : 'hover:bg-white/5'
                                                    }`}
                                                >
                                                    <span className={`text-xs font-semibold ${
                                                        activeCategory === 'construction' ? 'text-lime text-emerald-300' : 'text-ivory text-[#F4F6F0]'
                                                    } group-hover/item:text-lime group-hover/item:text-emerald-300`}>
                                                        Project Construction
                                                    </span>
                                                    <span className="text-[11px] text-[#A8B0A6]/70 mt-0.5 font-light">
                                                        Living landscape build &amp; stone craft
                                                    </span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                );
                            }
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
                                        <span className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-[2px] bg-lime bg-emerald-400 rounded-full shadow-[0_0_10px_rgba(176,228,84,0.65)] shadow-[0_0_8px_rgba(52,211,153,0.6)]" />
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
                                className="px-3.5 sm:px-4 py-1.5 rounded-full bg-forest bg-[#1C3024] text-ivory text-[#F4F6F0] text-xs font-semibold border border-forest-light/60 border-emerald-500/30 hover:bg-lime hover:text-jet hover:border-lime hover:shadow-[0_0_15px_rgba(176,228,84,0.4)] transition-all duration-200 active:scale-[0.98] shadow-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.15)] backdrop-blur-xs"
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
                            className="md:hidden p-1.5 text-ivory text-[#F4F6F0] hover:text-lime hover:text-emerald-400 hover:bg-white/10 rounded-full transition-colors focus:outline-none"
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
