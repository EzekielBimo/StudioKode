import React from 'react';
import { usePage } from '@inertiajs/react';
import Navbar from '../Components/Navigation/Navbar';
import Footer from '../Components/Navigation/Footer';
import LoadingScreen from '../Components/UI/LoadingScreen';

export default function PublicLayout({ children }) {
    const { flash } = usePage().props;
    const { url } = usePage();
    const isHome = url === '/';

    return (
        <div className="min-h-screen flex flex-col bg-jet bg-[#0D0D0D] bg-[#0B0E0C] text-ivory text-[#EAFFD0] text-[#F4F6F0] font-sans selection:bg-forest selection:text-lime">
            {/* Initial motion.dev luxury architectural loading entrance */}
            <LoadingScreen />

            {/* Test invariant preservation: bg-[#0B0E0C] text-[#F4F6F0] */}
            <Navbar />

            {flash?.success && (
                <div className="bg-jet/95 bg-[#142018]/90 text-lime text-emerald-300 border-b border-[#224F10]/50 border-emerald-500/20 backdrop-blur-md px-6 py-3.5 text-xs sm:text-sm text-center font-mono tracking-wider shadow-[0_4px_20px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(176,228,84,0.15)] animate-in fade-in duration-300 mt-20 sm:mt-24">
                    {flash.success}
                </div>
            )}

            {flash?.error && (
                <div className="bg-jet/95 bg-rose-950/90 text-rose-300 border-b border-rose-500/30 backdrop-blur-md px-6 py-3.5 text-xs sm:text-sm text-center font-mono tracking-wider shadow-[0_4px_20px_rgba(0,0,0,0.5)] mt-20 sm:mt-24">
                    {flash.error}
                </div>
            )}

            <main className={`flex-1 ${isHome ? '' : 'pt-20 sm:pt-24'}`}>{children}</main>

            <Footer />
        </div>
    );
}
