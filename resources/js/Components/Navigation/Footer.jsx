import React from 'react';
import { Link } from '@inertiajs/react';

export default function Footer() {
    return (
        <footer className="mt-auto bg-[#080B09] text-[#F4F6F0] py-16 border-t border-white/10">
            <div className="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
                {/* Brand Manifesto */}
                <div className="space-y-4">
                    <span className="font-serif text-2xl tracking-tight block">StudioKode</span>
                    <p className="text-sm text-[#A7ACA0] leading-relaxed font-light">
                        Landscape architecture and botanical sanctuaries balancing living topography, tactile materiality, and ecological permanence.
                    </p>
                </div>

                {/* Exploration */}
                <div>
                    <h4 className="text-xs uppercase tracking-widest text-emerald-400/90 font-semibold mb-4">
                        Exploration
                    </h4>
                    <ul className="space-y-2.5 text-sm text-[#C5C8C0] font-light">
                        <li>
                            <Link href="/" className="hover:text-emerald-300 transition-colors">Home</Link>
                        </li>
                        <li>
                            <Link href="/projects" className="hover:text-emerald-300 transition-colors">Portfolio</Link>
                        </li>
                        <li>
                            <Link href="/studio" className="hover:text-emerald-300 transition-colors">Studio Philosophy</Link>
                        </li>
                        <li>
                            <Link href="/services" className="hover:text-emerald-300 transition-colors">Services &amp; Scope</Link>
                        </li>
                    </ul>
                </div>

                {/* Ateliers */}
                <div>
                    <h4 className="text-xs uppercase tracking-widest text-emerald-400/90 font-semibold mb-4">
                        Ateliers
                    </h4>
                    <div className="text-sm text-[#C5C8C0] font-light leading-relaxed space-y-1">
                        <span className="block">Kyoto: Kamigamo Sanctuary</span>
                        <span className="block">Z&uuml;rich: Seestrasse 142</span>
                        <span className="block">Melbourne: Flinders Lane</span>
                    </div>
                </div>

                {/* Consultations */}
                <div>
                    <h4 className="text-xs uppercase tracking-widest text-emerald-400/90 font-semibold mb-4">
                        Inquiries
                    </h4>
                    <p className="text-sm text-[#A7ACA0] font-light leading-relaxed mb-4">
                        Consultations by private appointment for architectural estates and residences.
                    </p>
                    <Link
                        href="/contact"
                        className="inline-block px-4 py-2 border border-emerald-500/30 text-xs uppercase tracking-wider text-emerald-300 hover:bg-emerald-500/15 hover:border-emerald-400 hover:text-white transition-colors"
                    >
                        Initiate Consultation
                    </Link>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-6 mt-16 pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between text-xs text-[#7C8375] font-light">
                <p>&copy; {new Date().getFullYear()} StudioKode Landscape Architecture. All rights reserved.</p>
                <p className="mt-2 md:mt-0">Botanical Architecture &amp; Spatial Ecology</p>
            </div>
        </footer>
    );
}
