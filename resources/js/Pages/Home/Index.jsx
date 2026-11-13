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
            <section className="relative min-h-[85vh] flex items-center justify-center bg-gradient-to-b from-[#0B0E0C] via-[#0E1310] to-[#0B0E0C] text-[#F4F2ED] px-6 overflow-hidden">
                <div className="absolute inset-0 opacity-40 mix-blend-overlay">
                    <img
                        src="https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1200&q=70"
                        alt="Sanctuary landscape"
                        className="w-full h-full object-cover"
                        width="1200"
                        height="800"
                        fetchpriority="high"
                    />
                </div>
                <div className="relative max-w-4xl mx-auto text-center space-y-8 py-20">
                    <InView initial="visible">
                        <span className="text-xs uppercase tracking-[0.25em] text-[#88B896] font-medium">
                            StudioKode Landscape Architecture
                        </span>
                    </InView>
                    <InView delay={0.1} initial="visible">
                        <h1 className="font-serif text-4xl sm:text-6xl md:text-7xl leading-[1.08] tracking-tight">
                            Mediating between architectural form and untamed nature.
                        </h1>
                    </InView>
                    <InView delay={0.2} initial="visible">
                        <p className="text-base sm:text-lg text-[#C4C9C3]/90 font-light max-w-2xl mx-auto leading-relaxed">
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
            {statistics.length > 0 && (
                <section className="border-b border-white/10 bg-[rgba(20,32,24,0.45)] backdrop-blur-md shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                    <div className="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        {statistics.map((stat, i) => (
                            <InView key={i} delay={i * 0.08} className="space-y-1">
                                <div className="font-serif text-3xl md:text-4xl text-[#88B896] font-normal">
                                    {stat.value}
                                </div>
                                <div className="text-xs uppercase tracking-widest text-[#A8B0A6]/70 font-medium">
                                    {stat.label}
                                </div>
                            </InView>
                        ))}
                    </div>
                </section>
            )}

            {/* Featured Projects Showcase */}
            <section className="max-w-7xl mx-auto px-6 py-24">
                <InView className="flex flex-col md:flex-row md:items-end justify-between mb-12">
                    <div>
                        <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                            Selected Works
                        </span>
                        <h2 className="font-serif text-3xl md:text-5xl tracking-tight text-[#F4F2ED] mt-2">
                            Curated Sanctuaries
                        </h2>
                    </div>
                    <Link
                        href="/projects"
                        className="mt-4 md:mt-0 inline-flex items-center text-xs uppercase tracking-widest font-semibold text-[#C4C9C3] hover:text-[#88B896] transition-colors"
                    >
                        View Full Portfolio <ArrowRight className="w-4 h-4 ml-1.5" />
                    </Link>
                </InView>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {featuredProjects.map((project, i) => (
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
            {philosophyHighlights.length > 0 && (
                <section className="bg-[#0E1310]/85 backdrop-blur-md border-y border-white/10 text-[#F4F2ED] py-24 shadow-[inset_0_1px_0_rgba(255,255,255,0.06),inset_0_-1px_0_rgba(255,255,255,0.06)]">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="text-center max-w-2xl mx-auto mb-16 space-y-3">
                            <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                                Our Approach
                            </span>
                            <h2 className="font-serif text-3xl md:text-4xl tracking-tight text-[#F4F2ED]">
                                The Three Botanical Pillars
                            </h2>
                        </InView>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                            {philosophyHighlights.map((pillar, i) => (
                                <InView key={i} delay={i * 0.12} className="space-y-3 border-t border-white/10 pt-6">
                                    <span className="font-serif text-2xl text-[#88B896]/70">
                                        {pillar.number}
                                    </span>
                                    <h3 className="font-serif text-xl tracking-tight text-[#F4F2ED]">
                                        {pillar.title}
                                    </h3>
                                    <p className="text-sm text-[#C4C9C3]/80 font-light leading-relaxed">
                                        {pillar.description}
                                    </p>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Inquiry CTA */}
            <section className="max-w-4xl mx-auto px-6 py-24 text-center space-y-6">
                <InView>
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        Begin A Conversation
                    </span>
                    <h2 className="font-serif text-3xl md:text-5xl tracking-tight text-[#F4F2ED] mt-2">
                        Let us envision your living landscape.
                    </h2>
                    <p className="mt-4 text-base text-[#C4C9C3]/80 font-light leading-relaxed max-w-xl mx-auto">
                        Whether an alpine estate, urban microclimate courtyard, or coastal woodland sanctuary, our partners personally guide each inquiry.
                    </p>
                    <div className="pt-6">
                        <Button href="/contact" size="lg">
                            Initiate Commission
                        </Button>
                    </div>
                </InView>
            </section>
        </PublicLayout>
    );
}
