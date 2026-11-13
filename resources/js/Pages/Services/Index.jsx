import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';
import Button from '../../Components/UI/Button';
import { Check } from 'lucide-react';

export default function ServicesIndex({ services = [] }) {
    return (
        <PublicLayout>
            <Head title="Architectural Landscape & Botanical Services" />

            {/* Header */}
            <div className="max-w-7xl mx-auto px-6 py-20">
                <InView className="max-w-3xl">
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        Our Scope
                    </span>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] mt-2 tracking-tight">
                        Disciplinary Rigor from Masterplan to Stewardship
                    </h1>
                    <p className="mt-6 text-lg text-[#C4C9C3]/90 font-light leading-relaxed">
                        We provide complete landscape architectural direction for visionary landowners, architects, and institutions seeking profound ecological and tactile permanence.
                    </p>
                </InView>
            </div>

            {/* Services Detailed List */}
            <section className="max-w-7xl mx-auto px-6 pb-24 space-y-16">
                {services.map((service, index) => (
                    <InView key={service.id || index} delay={index * 0.08} className="border-t border-white/10 pt-12">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <div className="lg:col-span-1 font-serif text-3xl text-[#88B896]/60">
                                {service.number}
                            </div>
                            <div className="lg:col-span-5 space-y-4">
                                <h2 className="font-serif text-2xl sm:text-3xl text-[#F4F2ED] tracking-tight">{service.title}</h2>
                                <p className="text-xs uppercase tracking-wider text-[#88B896] font-medium">{service.tagline}</p>
                                <p className="text-sm text-[#C4C9C3]/80 font-light leading-relaxed">{service.description}</p>
                            </div>
                            <div className="lg:col-span-6 bg-[rgba(20,32,24,0.65)] backdrop-blur-md border border-white/10 p-6 sm:p-8 space-y-4 shadow-xl shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                <h3 className="text-xs uppercase tracking-widest text-[#F4F2ED] font-semibold">Key Deliverables</h3>
                                <ul className="space-y-3">
                                    {service.deliverables?.map((item, dIdx) => (
                                        <li key={dIdx} className="flex items-start text-xs sm:text-sm text-[#C4C9C3]/90 font-light">
                                            <Check className="w-4 h-4 text-[#88B896] mr-3 shrink-0 mt-0.5" />
                                            <span>{item}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </InView>
                ))}
            </section>

            {/* Inquiries CTA */}
            <section className="bg-[#0E1310]/85 backdrop-blur-md border-t border-white/10 text-[#F4F2ED] py-20 text-center shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                <InView className="max-w-xl mx-auto px-6 space-y-6">
                    <h2 className="font-serif text-3xl tracking-tight text-[#F4F2ED]">Commission an Architectural Study</h2>
                    <p className="text-sm text-[#C4C9C3]/80 font-light leading-relaxed">
                        We welcome conversations at early territorial acquisition and conceptual architectural stages.
                    </p>
                    <div>
                        <Button href="/contact" variant="secondary" size="lg">
                            Contact Principals
                        </Button>
                    </div>
                </InView>
            </section>
        </PublicLayout>
    );
}
