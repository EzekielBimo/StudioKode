import React, { useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import {
    LayoutDashboard,
    Layers,
    Mail,
    ExternalLink,
    LogOut,
    Menu,
    X,
    CheckCircle2,
    AlertCircle,
    User,
} from 'lucide-react';
import { id } from '../lib/id';

export default function AdminLayout({ title, children }) {
    const { auth, flash, url } = usePage().props;
    const [mobileOpen, setMobileOpen] = useState(false);
    const [dismissFlash, setDismissFlash] = useState(false);

    const currentUrl = url || (typeof window !== 'undefined' ? window.location.pathname : '');

    const navItems = [
        {
            name: id.nav.dashboard,
            href: '/admin',
            icon: LayoutDashboard,
            active: currentUrl === '/admin' || currentUrl === '/admin/dashboard',
        },
        {
            name: id.nav.projects,
            href: '/admin/projects',
            icon: Layers,
            active: currentUrl.startsWith('/admin/projects'),
        },
        {
            name: id.nav.inquiries,
            href: '/admin/inquiries',
            icon: Mail,
            active: currentUrl.startsWith('/admin/inquiries'),
        },
        {
            name: id.nav.profile,
            href: '/admin/profile',
            icon: User,
            active: currentUrl.startsWith('/admin/profile'),
        },
    ];

    const handleLogout = (e) => {
        e.preventDefault();
        if (window.confirm(id.auth.logout_confirm || 'Apakah Anda yakin ingin keluar?')) {
            router.post('/admin/logout');
        }
    };

    return (
        <div className="min-h-screen bg-[#0B0E0C] text-[#F5F2EB] font-sans flex flex-col md:flex-row selection:bg-[#1C2A21] selection:text-[#F9F8F6]">
            <Head title={title ? `${title} — ${id.brand.admin_panel}` : id.brand.admin_panel} />

            {/* Mobile Top Bar */}
            <div className="md:hidden bg-[#0E1310]/95 backdrop-blur-md text-[#F5F2EB] border-b border-white/10 shadow-[inset_0_-1px_0_rgba(255,255,255,0.05)] px-4 py-3 flex items-center justify-between sticky top-0 z-40">
                <Link href="/admin" className="flex items-center space-x-2">
                    <span className="font-serif text-xl tracking-tight text-[#F5F2EB]">{id.brand.name}</span>
                    <span className="text-[9px] uppercase tracking-widest text-[#E6E2DD]/70 bg-white/10 px-1.5 py-0.5 rounded border border-white/10">
                        Admin
                    </span>
                </Link>
                <button
                    type="button"
                    onClick={() => setMobileOpen(!mobileOpen)}
                    className="p-1.5 rounded text-[#E6E2DD] hover:text-white hover:bg-white/10 focus:outline-none"
                    aria-label="Toggle Navigation"
                >
                    {mobileOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
                </button>
            </div>

            {/* Mobile Slide-Over Drawer */}
            {mobileOpen && (
                <div className="md:hidden fixed inset-0 z-50 flex">
                    <div
                        className="fixed inset-0 bg-black/60 backdrop-blur-sm"
                        onClick={() => setMobileOpen(false)}
                    />
                    <div className="relative flex-1 flex flex-col max-w-xs w-full bg-[#0E1310] backdrop-blur-xl text-[#F5F2EB] p-6 justify-between border-r border-white/10 shadow-[inset_-1px_0_0_rgba(255,255,255,0.05),0_0_30px_rgba(0,0,0,0.5)]">
                        <div>
                            <div className="flex items-center justify-between mb-8 pb-4 border-b border-white/10">
                                <div>
                                    <span className="font-serif text-2xl tracking-tight text-[#F5F2EB]">
                                        {id.brand.name}
                                    </span>
                                    <p className="text-[9px] uppercase tracking-[0.2em] text-[#E6E2DD]/60">
                                        {id.brand.admin_panel}
                                    </p>
                                </div>
                                <button
                                    onClick={() => setMobileOpen(false)}
                                    className="p-1 text-[#E6E2DD]/70 hover:text-white"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>

                            <nav className="space-y-1.5">
                                {navItems.map((item) => {
                                    const Icon = item.icon;
                                    return (
                                        <Link
                                            key={item.href}
                                            href={item.href}
                                            onClick={() => setMobileOpen(false)}
                                            className={`flex items-center px-3.5 py-2.5 rounded text-sm font-medium transition-colors ${
                                                item.active
                                                    ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-sm'
                                                    : 'text-[#E6E2DD]/80 hover:bg-white/5 hover:text-white'
                                            }`}
                                        >
                                            <Icon className="w-4 h-4 mr-3 opacity-80" />
                                            {item.name}
                                        </Link>
                                    );
                                })}

                                <a
                                    href="/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center px-3.5 py-2.5 rounded text-sm font-medium text-[#E6E2DD]/80 hover:bg-white/5 hover:text-white transition-colors"
                                >
                                    <ExternalLink className="w-4 h-4 mr-3 opacity-80" />
                                    {id.nav.view_site}
                                </a>
                            </nav>
                        </div>

                        {/* Mobile User Profile & Logout */}
                        <div className="pt-6 border-t border-white/10">
                            <Link
                                href="/admin/profile"
                                onClick={() => setMobileOpen(false)}
                                className="flex items-center mb-4 p-1.5 -mx-1.5 rounded-lg hover:bg-white/5 transition-colors group"
                            >
                                <div className="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center justify-center mr-3 font-medium text-xs group-hover:border-emerald-400">
                                    {auth?.user?.name ? auth.user.name.charAt(0).toUpperCase() : <User className="w-4 h-4" />}
                                </div>
                                <div className="overflow-hidden">
                                    <p className="text-sm font-medium text-[#F5F2EB] truncate group-hover:text-emerald-300 transition-colors">
                                        {auth?.user?.name || 'Administrator'}
                                    </p>
                                    <p className="text-xs text-[#E6E2DD]/60 truncate">
                                        {auth?.user?.email || 'admin@studiokode.com'}
                                    </p>
                                </div>
                            </Link>
                            <button
                                type="button"
                                onClick={handleLogout}
                                className="w-full flex items-center justify-center px-3 py-2 rounded border border-white/15 text-xs uppercase tracking-wider font-medium text-[#F5F2EB] hover:bg-rose-950/40 hover:border-rose-500/40 hover:text-rose-200 transition-colors"
                            >
                                <LogOut className="w-3.5 h-3.5 mr-2" />
                                {id.auth.logout_button}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Desktop Sidebar */}
            <aside className="hidden md:flex flex-col w-64 bg-[#0E1310]/95 backdrop-blur-md text-[#F5F2EB] border-r border-white/10 shrink-0 sticky top-0 h-screen justify-between p-6 z-30 shadow-[inset_-1px_0_0_rgba(255,255,255,0.05)]">
                <div>
                    {/* Brand header */}
                    <div className="mb-8 pb-5 border-b border-white/10">
                        <Link href="/admin" className="block group">
                            <span className="font-serif text-2xl tracking-tight text-[#F5F2EB] group-hover:text-emerald-300 transition-colors block">
                                {id.brand.name}
                            </span>
                            <span className="text-[9px] uppercase tracking-[0.25em] text-[#E6E2DD]/60 font-medium block mt-1">
                                {id.brand.admin_panel}
                            </span>
                        </Link>
                    </div>

                    {/* Navigation Links */}
                    <nav className="space-y-1.5">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`flex items-center px-3.5 py-2.5 rounded text-xs uppercase tracking-wider font-medium transition-colors ${
                                        item.active
                                            ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.15)]'
                                            : 'text-[#E6E2DD]/70 hover:bg-white/5 hover:text-white'
                                    }`}
                                >
                                    <Icon className="w-4 h-4 mr-3 opacity-80" />
                                    {item.name}
                                </Link>
                            );
                        })}

                        <div className="pt-4 mt-4 border-t border-white/10">
                            <a
                                href="/"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center px-3.5 py-2.5 rounded text-xs uppercase tracking-wider font-medium text-[#E6E2DD]/60 hover:bg-white/5 hover:text-white transition-colors"
                            >
                                <ExternalLink className="w-4 h-4 mr-3 opacity-80" />
                                {id.nav.view_site}
                            </a>
                        </div>
                    </nav>
                </div>

                {/* User Profile & Logout Box */}
                <div className="pt-6 border-t border-white/10">
                    <Link
                        href="/admin/profile"
                        className="flex items-center mb-4 p-1.5 -mx-1.5 rounded-lg hover:bg-white/5 transition-colors group"
                        title={id.nav.profile || 'Profil Admin'}
                    >
                        <div className="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center justify-center mr-3 font-medium text-xs shrink-0 group-hover:border-emerald-400">
                            {auth?.user?.name ? auth.user.name.charAt(0).toUpperCase() : <User className="w-4 h-4" />}
                        </div>
                        <div className="overflow-hidden">
                            <p className="text-xs font-semibold text-[#F5F2EB] truncate group-hover:text-emerald-300 transition-colors">
                                {auth?.user?.name || 'Administrator'}
                            </p>
                            <p className="text-[11px] text-[#E6E2DD]/60 truncate">
                                {auth?.user?.email || 'admin@studiokode.com'}
                            </p>
                        </div>
                    </Link>
                    <button
                        type="button"
                        onClick={handleLogout}
                        className="w-full flex items-center justify-center px-3 py-2 rounded border border-white/15 text-xs uppercase tracking-wider font-medium text-[#F5F2EB] hover:bg-rose-950/40 hover:border-rose-500/40 hover:text-rose-200 transition-colors"
                    >
                        <LogOut className="w-3.5 h-3.5 mr-2" />
                        {id.auth.logout_button}
                    </button>
                </div>
            </aside>

            {/* Main Content Area */}
            <div className="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[#0B0E0C]">
                {/* Global Flash Alerts */}
                {!dismissFlash && flash?.success && (
                    <div className="bg-[#142018]/90 border-b border-emerald-500/30 text-emerald-200 px-6 py-3.5 flex items-center justify-between backdrop-blur-md shadow-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                        <div className="flex items-center space-x-2.5">
                            <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                            <span className="text-xs sm:text-sm font-medium">{flash.success}</span>
                        </div>
                        <button
                            type="button"
                            onClick={() => setDismissFlash(true)}
                            className="text-[#E6E2DD]/70 hover:text-white p-1"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                )}

                {!dismissFlash && flash?.error && (
                    <div className="bg-rose-950/80 border-b border-rose-500/30 text-rose-200 px-6 py-3.5 flex items-center justify-between backdrop-blur-md shadow-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                        <div className="flex items-center space-x-2.5">
                            <AlertCircle className="w-4 h-4 text-rose-300 shrink-0" />
                            <span className="text-xs sm:text-sm font-medium">{flash.error}</span>
                        </div>
                        <button
                            type="button"
                            onClick={() => setDismissFlash(true)}
                            className="text-rose-200/80 hover:text-white p-1"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                )}

                {/* Page Content */}
                <main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                    {children}
                </main>
            </div>
        </div>
    );
}
