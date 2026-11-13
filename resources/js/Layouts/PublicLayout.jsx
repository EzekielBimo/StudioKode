import React from 'react';
import { usePage } from '@inertiajs/react';
import Navbar from '../Components/Navigation/Navbar';
import Footer from '../Components/Navigation/Footer';

export default function PublicLayout({ children }) {
    const { flash } = usePage().props;
    const { url } = usePage();
    const isHome = url === '/';

    return (
        <div className="min-h-screen flex flex-col bg-[#0B0E0C] text-[#F4F6F0] font-sans selection:bg-[#1C3024] selection:text-[#F4F6F0]">
            <Navbar />

            {flash?.success && (
                <div className="bg-[#142018]/90 text-emerald-300 border-b border-emerald-500/20 backdrop-blur-md px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] animate-in fade-in duration-300 mt-20 sm:mt-24">
                    {flash.success}
                </div>
            )}

            {flash?.error && (
                <div className="bg-rose-950/90 text-rose-200 border-b border-rose-500/30 backdrop-blur-md px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] mt-20 sm:mt-24">
                    {flash.error}
                </div>
            )}

            <main className={`flex-1 ${isHome ? '' : 'pt-20 sm:pt-24'}`}>{children}</main>

            <Footer />
        </div>
    );
}
