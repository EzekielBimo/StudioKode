import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';

export default function StudioIndex({
    studio = {},
    philosophy = [],
    team = [],
    process = [],
    accolades = [],
}) {
    return (
        <PublicLayout>
            <Head title="Studio Philosophy, Methodology & Leadership" />

            {/* Header */}
            <div className="max-w-7xl mx-auto px-6 py-20">
                <InView className="max-w-3xl">
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        The Practice
                    </span>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] mt-2 tracking-tight">
                        Architecture Born From Ground &amp; Topography
                    </h1>
                    <p className="mt-6 text-lg text-[#C4C9C3]/90 font-light leading-relaxed">
                        {studio.manifesto}
                    </p>
                </InView>
            </div>

            {/* Philosophy Pillars */}
            <section className="bg-[#0E1310]/85 backdrop-blur-md border-y border-white/10 text-[#F4F2ED] py-24 shadow-[inset_0_1px_0_rgba(255,255,255,0.06),inset_0_-1px_0_rgba(255,255,255,0.06)]">
                <div className="max-w-7xl mx-auto px-6">
                    <InView className="max-w-2xl mb-16">
                        <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                            Core Tenets
                        </span>
                        <h2 className="font-serif text-3xl sm:text-4xl tracking-tight text-[#F4F2ED] mt-1">
                            Foundational Principles
                        </h2>
                    </InView>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
                        {philosophy.map((item, i) => (
                            <InView key={i} delay={i * 0.1} className="border-t border-white/10 pt-6 space-y-3">
                                <span className="font-serif text-2xl text-[#88B896]/70">{item.number}</span>
                                <h3 className="font-serif text-2xl tracking-tight text-[#F4F2ED]">{item.title}</h3>
                                <p className="text-sm text-[#C4C9C3]/80 font-light leading-relaxed">{item.description}</p>
                            </InView>
                        ))}
                    </div>
                </div>
            </section>

            {/* Multi-Stage Methodology */}
            <section className="max-w-7xl mx-auto px-6 py-24">
                <InView className="max-w-2xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        Design Evolution
                    </span>
                    <h2 className="font-serif text-3xl sm:text-4xl tracking-tight text-[#F4F2ED] mt-1">
                        Our Four-Stage Methodology
                    </h2>
                </InView>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                    {process.map((step, i) => (
                        <InView key={i} delay={i * 0.08} className="border border-white/10 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-6 space-y-4 shadow-lg shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                            <span className="font-serif text-3xl text-[#88B896]">{step.stage}</span>
                            <h3 className="font-serif text-xl text-[#F4F2ED]">{step.title}</h3>
                            <p className="text-xs text-[#C4C9C3]/80 font-light leading-relaxed">{step.description}</p>
                        </InView>
                    ))}
                </div>
            </section>

            {/* Leadership Team */}
            <section className="max-w-7xl mx-auto px-6 py-24 border-t border-white/10">
                <InView className="max-w-2xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        Studio Leadership
                    </span>
                    <h2 className="font-serif text-3xl sm:text-4xl tracking-tight text-[#F4F2ED] mt-1">
                        Principal Practitioners
                    </h2>
                </InView>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    {team.map((member, i) => (
                        <InView key={i} delay={i * 0.08} className="space-y-4">
                            <div className="aspect-[4/5] overflow-hidden bg-[#121814] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                <img
                                    src={member.image}
                                    alt={member.name}
                                    className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
                                />
                            </div>
                            <div>
                                <h3 className="font-serif text-lg text-[#F4F2ED]">{member.name}</h3>
                                <p className="text-xs uppercase tracking-wider text-[#88B896] font-medium mt-0.5">{member.role}</p>
                                <p className="text-xs text-[#A8B0A6]/80 font-light mt-2 leading-relaxed">{member.bio}</p>
                            </div>
                        </InView>
                    ))}
                </div>
            </section>

            {/* Accolades */}
            {accolades.length > 0 && (
                <section className="bg-[rgba(20,32,24,0.35)] backdrop-blur-sm border-t border-white/10 py-20 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="max-w-xl mb-12">
                            <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                                Recognition
                            </span>
                            <h2 className="font-serif text-2xl text-[#F4F2ED] mt-1">
                                Peer &amp; Industry Accolades
                            </h2>
                        </InView>

                        <div className="space-y-4">
                            {accolades.map((acc, i) => (
                                <InView key={i} className="flex items-center justify-between border-b border-white/10 pb-4 text-sm">
                                    <span className="font-serif text-lg text-[#F4F2ED]">{acc.award}</span>
                                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">{acc.year}</span>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
