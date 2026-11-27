import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import InView from '../../Components/UI/InView';
import Button from '../../Components/UI/Button';
import { ArrowRight } from 'lucide-react';

export default function HomeIndex({
    featuredProjects = [],
    statistics = [],
    philosophyHighlights = [],
}) {
    return (
        <PublicLayout>
            <Head title="Architectural Sanctuaries & Botanical Landscape Design" />

            {/* Hero Section */}
            <section className="relative min-h-[85vh] flex items-center justify-center bg-jet bg-[#0B0E0C] text-ivory text-[#F4F2ED] px-6 overflow-hidden">
                <div className="absolute inset-0">
                    <img
                        src="/assets/backgrounds/hero-landscape.webp?v=sanctuary_v2"
                        alt="Sanctuary landscape"
                        className="w-full h-full object-cover object-center"
                        width="1920"
                        height="1080"
                        fetchpriority="high"
                        loading="eager"
                        decoding="async"
                    />
                    {/* Darkening layer for enhanced headline legibility */}
                    <div className="absolute inset-0 bg-black/40 pointer-events-none" aria-hidden="true" />
                    {/* Atmospheric gradient overlay calibrated for image vibrancy & high text legibility */}
                    <div className="absolute inset-0 bg-gradient-to-b from-black/65 via-[#224F10]/15 to-[#0D0D0D]/95 pointer-events-none" aria-hidden="true" />
                </div>
                <div className="relative max-w-4xl mx-auto text-center space-y-8 py-20">
                    <InView initial="visible">
                        <span className="inline-flex items-center gap-2 text-lime text-[#88B896] text-xs uppercase tracking-widest-plus font-mono font-medium drop-shadow-sm">
                            StudioKode Landscape Architecture
                        </span>
                    </InView>
                    <InView delay={0.1} initial="visible">
                        <h1 className="font-serif text-4xl sm:text-6xl md:text-7xl lg:text-8xl leading-[1.05] tracking-tight text-ivory text-[#F4F2ED] drop-shadow-md">
                            Mediating between architectural form and untamed nature.
                        </h1>
                    </InView>
                    <InView delay={0.2} initial="visible">
                        <p className="text-base sm:text-lg text-[#EAFFD0]/85 text-[#F4F2ED]/90 font-light max-w-2xl mx-auto leading-relaxed drop-shadow">
                            Crafting living topographies, dry-stone masonry, and biodiverse botanical tapestries for generational private estates.
                        </p>
                    </InView>
                    <InView delay={0.3} initial="visible">
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                            <Button href="/projects" variant="secondary" size="lg">
                                Explore Sanctuaries
                            </Button>
                            <Button href="/contact" variant="outline-light" size="lg">
                                Commission Project
                            </Button>
                        </div>
                    </InView>
                </div>
            </section>

            {/* Statistics Bar */}
            {statistics?.length > 0 && (
                <section className="border-y border-white/10 bg-jet/80 bg-[rgba(13,13,13,0.75)] backdrop-blur-md shadow-[inset_0_1px_0_rgba(255,255,255,0.06),inset_0_-1px_0_rgba(255,255,255,0.06)]">
                    <div className="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        {statistics.map((stat, i) => (
                            <InView key={i} delay={i * 0.08} className="space-y-1.5">
                                <div className="font-serif text-3xl md:text-5xl text-lime text-[#88B896] font-normal tracking-tight">
                                    {stat.value}
                                </div>
                                <div className="text-xs uppercase tracking-widest text-[#EAFFD0]/70 text-[#A8B0A6]/70 font-mono font-medium">
                                    {stat.label}
                                </div>
                            </InView>
                        ))}
                    </div>
                </section>
            )}

            {/* Featured Projects Showcase */}
            <section className="max-w-7xl mx-auto px-6 py-24 sm:py-32">
                <InView className="flex flex-col md:flex-row md:items-end justify-between mb-12">
                    <div>
                        <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                            Selected Works
                        </span>
                        <h2 className="font-serif text-3xl md:text-5xl tracking-tight text-ivory text-[#F4F2ED] mt-2">
                            Curated Sanctuaries
                        </h2>
                    </div>
                    <Link
                        href="/projects"
                        className="mt-4 md:mt-0 inline-flex items-center text-xs uppercase tracking-widest font-semibold text-[#EAFFD0]/75 text-[#C4C9C3] hover:text-lime transition-colors"
                    >
                        View Full Portfolio <ArrowRight className="w-4 h-4 ml-1.5 text-lime" />
                    </Link>
                </InView>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {(featuredProjects || []).map((project, i) => (
                        <ProjectCard
                            key={project.id || i}
                            project={project}
                            priority={i === 0}
                            delay={i * 0.1}
                        />
                    ))}
                </div>
            </section>

            {/* Philosophy Highlights */}
            {philosophyHighlights?.length > 0 && (
                <section className="bg-jet-surface/90 bg-[#0E1310]/85 backdrop-blur-md border-y border-forest/30 border-white/10 text-ivory text-[#F4F2ED] py-24 sm:py-32 shadow-[inset_0_1px_0_rgba(255,255,255,0.06),inset_0_-1px_0_rgba(255,255,255,0.06)]">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="text-center max-w-2xl mx-auto mb-16 space-y-3">
                            <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                                Our Approach
                            </span>
                            <h2 className="font-serif text-3xl md:text-4xl lg:text-5xl tracking-tight text-ivory text-[#F4F2ED]">
                                The Three Botanical Pillars
                            </h2>
                        </InView>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                            {philosophyHighlights.map((pillar, i) => (
                                <InView key={i} delay={i * 0.12} className="space-y-3 border-t border-forest/40 border-white/10 pt-6">
                                    <span className="font-serif text-2xl text-lime text-[#88B896] font-normal">
                                        {pillar.number}
                                    </span>
                                    <h3 className="font-serif text-2xl tracking-tight text-ivory text-[#F4F2ED]">
                                        {pillar.title}
                                    </h3>
                                    <p className="text-sm text-[#EAFFD0]/75 text-[#C4C9C3]/80 font-light leading-relaxed">
                                        {pillar.description}
                                    </p>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Inquiry CTA */}
            <section className="max-w-4xl mx-auto px-6 py-28 text-center space-y-6">
                <InView>
                    <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                        Begin A Conversation
                    </span>
                    <h2 className="font-serif text-3xl md:text-5xl lg:text-6xl tracking-tight text-ivory text-[#F4F2ED] mt-2">
                        Let us envision your living landscape.
                    </h2>
                    <p className="mt-4 text-base sm:text-lg text-[#EAFFD0]/80 text-[#C4C9C3]/80 font-light leading-relaxed max-w-xl mx-auto">
                        Whether an alpine estate, urban microclimate courtyard, or coastal woodland sanctuary, our partners personally guide each inquiry.
                    </p>
                    <div className="pt-8">
                        <Button href="/contact" variant="primary" size="lg">
                            Initiate Commission
                        </Button>
                    </div>
                </InView>
            </section>
        </PublicLayout>
    );
}
