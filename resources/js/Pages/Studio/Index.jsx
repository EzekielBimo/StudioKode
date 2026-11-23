import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';
import Button from '../../Components/UI/Button';
import { MapPin } from 'lucide-react';

const defaultPhilosophy = [
    {
        number: '01',
        title: 'Unified Design & Build',
        description: 'We believe in design and build as a unified process, creating landscapes that grow naturally from their environment through integrated execution, sustainable practices, and locally sourced materials.',
    },
    {
        number: '02',
        title: 'Land-First Sensitivity',
        description: 'Beginning each project with a deep understanding of the land; its form, climate, and natural rhythms. We do not impose design; we allow the landscape to guide the process.',
    },
    {
        number: '03',
        title: 'Rooted in Culture & Materiality',
        description: 'Rooted in the essence of place, local culture, and the people who inhabit it, using natural and locally sourced materials to create functional outdoor spaces that feel alive, meaningful, and timeless.',
    },
    {
        number: '04',
        title: 'Enduring Ecological Harmony',
        description: 'Every detail is crafted with care, ensuring beauty and function evolve together over time. Our landscapes are made to belong, to endure, and to grow alongside the lives within them.',
    },
];

const defaultConceptSteps = [
    {
        number: '01',
        title: 'Contact Us',
        description: 'Clients can reach us via WhatsApp, email, or Instagram. We listen to your needs and gather initial information about the project.',
    },
    {
        number: '02',
        title: 'Project Meeting & Site Assessment',
        description: 'We visit the project site to evaluate existing conditions, take precise measurements, and discuss the client’s vision, preferences, and functional requirements for the landscape design.',
    },
    {
        number: '03',
        title: 'Moodboard & Landscape Concept',
        description: 'A moodboard is developed to capture the style, materials, planting approach, and overall design direction of the landscape project in line with the client’s vision.',
    },
    {
        number: '04',
        title: 'Project Collaboration Agreement',
        description: 'Once the concept is approved, we finalize a design agreement covering the scope, schedule, design fee, and terms, ensuring clear understanding between our team and the client before design and execution begin.',
    },
    {
        number: '05',
        title: 'Design & Build',
        description: 'Our team carries out the design and build process, including 3D renderings, technical plans, planting schemes, and site execution, bringing the concept to life.',
    },
];

export default function StudioIndex({
    studio = {},
    philosophy = [],
    team = [],
    process = [],
    conceptSteps = [],
    accolades = [],
}) {
    const activePhilosophy = (Array.isArray(philosophy) && philosophy.length > 0) ? philosophy : defaultPhilosophy;
    const activeConceptSteps = (Array.isArray(conceptSteps) && conceptSteps.length > 0) ? conceptSteps : defaultConceptSteps;

    return (
        <PublicLayout>
            <Head title="Studio Profile — Kode Landscape Studio | Tropical Design & Build" />

            {/* Header / Hero */}
            <div className="max-w-7xl mx-auto px-6 pt-20 pb-16 sm:pt-28 sm:pb-24">
                <InView className="max-w-4xl space-y-8">
                    <div className="inline-flex items-center gap-2 px-3.5 py-1.5 bg-[#152319] bg-forest/40 border border-[#B0E454]/30 border-emerald-500/20 text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-medium shadow-sm shadow-[inset_0_1px_0_rgba(176,228,84,0.15)]">
                        <MapPin className="w-3.5 h-3.5 text-lime text-[#88B896]" />
                        <span>Kode Landscape Studio · Bali, Indonesia</span>
                    </div>
                    <h1 className="font-serif text-4xl sm:text-6xl md:text-7xl lg:text-8xl text-ivory text-[#EAFFD0] text-[#F4F2ED] tracking-tight leading-[1.05]">
                        Architecture Born From Ground, Climate &amp; Tropical Topography
                    </h1>
                    <p className="text-base sm:text-xl text-[#C4C9C3]/90 font-light leading-relaxed max-w-3xl">
                        {studio?.manifesto || 'We believe in design and build as a unified process, creating landscapes that grow naturally from their environment. Rooted in the essence of place, local culture, and the people who inhabit it, our landscapes are made to belong, to endure, and to grow alongside the lives within them.'}
                    </p>
                    <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
                        <Button href="/contact" variant="primary" size="lg">
                            Initiate Commission
                        </Button>
                        <Button href="/projects" variant="secondary" size="lg">
                            Explore Works
                        </Button>
                    </div>
                </InView>
            </div>

            {/* About Us Section */}
            <section className="bg-forest/15 bg-[#0E1310]/85 backdrop-blur-md border-y border-forest/30 border-white/10 text-ivory text-[#F4F2ED] py-24 sm:py-32 shadow-[inset_0_1px_0_rgba(255,255,255,0.06),inset_0_-1px_0_rgba(255,255,255,0.06)]">
                <div className="max-w-7xl mx-auto px-6">
                    <InView className="max-w-2xl mb-16">
                        <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                            About Us
                        </span>
                        <h2 className="font-serif text-3xl sm:text-5xl md:text-6xl tracking-tight text-ivory text-[#F4F2ED] mt-2">
                            Kode Landscape Studio
                        </h2>
                    </InView>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16 items-start">
                        {/* Narrative Column */}
                        <InView delay={0.1} className="space-y-6 text-[#C4C9C3]/90 font-light leading-relaxed text-base sm:text-lg">
                            <p>
                                Kode Landscape Studio is a small, growing landscape design and build studio based in Bali, founded in 2025. We specialize in crafting warm tropical outdoor environments across coastal, hillside, and diverse natural settings for resorts, hotels, villas, commercial spaces, private residences, and landscape renovation projects.
                            </p>
                            <p>
                                Our work blends creative vision with hands-on execution, allowing us to deliver landscapes that feel natural, well-curated, and thoughtfully designed to realize each client’s dream; rooted in the richness of nature and cultural heritage.
                            </p>
                            <p>
                                We believe that every outdoor space should tell a story, reflecting both the spirit of its surroundings and the lifestyle of those who inhabit it. By integrating sustainable practices and locally inspired design elements, we create landscapes that are not only beautiful but also enduring and meaningful.
                            </p>
                        </InView>

                        {/* Dossier Card Column */}
                        <InView delay={0.2} className="border border-forest/40 border-white/10 bg-[#121212]/90 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-8 sm:p-10 space-y-6 shadow-2xl shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                            <div className="border-b border-forest/30 border-white/10 pb-6">
                                <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-medium">Studio Dossier</span>
                                <h3 className="font-serif text-2xl text-ivory text-[#F4F2ED] mt-1">Tropical Practice &amp; Build</h3>
                            </div>

                            <dl className="space-y-4 text-sm">
                                <div className="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-white/5 gap-1">
                                    <dt className="text-xs uppercase tracking-wider text-lime/80 text-[#88B896] font-mono font-medium">Headquarters</dt>
                                    <dd className="text-ivory text-[#F4F2ED] font-light">Bali, Indonesia</dd>
                                </div>
                                <div className="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-white/5 gap-1">
                                    <dt className="text-xs uppercase tracking-wider text-lime/80 text-[#88B896] font-mono font-medium">Founded</dt>
                                    <dd className="text-ivory text-[#F4F2ED] font-light">2025</dd>
                                </div>
                                <div className="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-white/5 gap-1">
                                    <dt className="text-xs uppercase tracking-wider text-lime/80 text-[#88B896] font-mono font-medium">Specialization</dt>
                                    <dd className="text-ivory text-[#F4F2ED] font-light">Warm Tropical Outdoor Environments</dd>
                                </div>
                                <div className="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-white/5 gap-1">
                                    <dt className="text-xs uppercase tracking-wider text-lime/80 text-[#88B896] font-mono font-medium">Settings</dt>
                                    <dd className="text-ivory text-[#F4F2ED] font-light text-right">Coastal, Hillside &amp; Natural Contexts</dd>
                                </div>
                                <div className="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-white/5 gap-1">
                                    <dt className="text-xs uppercase tracking-wider text-lime/80 text-[#88B896] font-mono font-medium">Typologies</dt>
                                    <dd className="text-ivory text-[#F4F2ED] font-light text-right">Resorts, Hotels, Villas, Commercial, Private Residences &amp; Renovations</dd>
                                </div>
                                <div className="flex flex-col sm:flex-row sm:justify-between py-2 gap-1">
                                    <dt className="text-xs uppercase tracking-wider text-lime/80 text-[#88B896] font-mono font-medium">Delivery Model</dt>
                                    <dd className="text-lime text-[#88B896] font-mono font-medium">Unified Design &amp; Build</dd>
                                </div>
                            </dl>
                        </InView>
                    </div>
                </div>
            </section>

            {/* Our Philosophy Section */}
            <section className="max-w-7xl mx-auto px-6 py-24 sm:py-32">
                <InView className="max-w-3xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                        Core Principles
                    </span>
                    <h2 className="font-serif text-3xl sm:text-5xl md:text-6xl tracking-tight text-ivory text-[#F4F2ED] mt-2">
                        Our Philosophy
                    </h2>
                    <p className="mt-4 text-base sm:text-lg text-[#C4C9C3]/90 font-light leading-relaxed">
                        We believe in design and build as a unified process, creating landscapes that grow naturally from their environment, beginning each project with a deep understanding of the land; its form, climate, and natural rhythms. Rooted in the essence of place, local culture, and the people who inhabit it, every landscape is shaped with its own identity and soul.
                    </p>
                </InView>

                {/* Featured Credo Banner */}
                <InView delay={0.1} className="border-l-2 border-lime border-[#88B896] bg-forest/20 bg-[#0E1310]/70 backdrop-blur-sm p-6 sm:p-10 mb-16 text-ivory text-[#F4F2ED] shadow-lg shadow-[inset_0_1px_0_rgba(176,228,84,0.08)]">
                    <blockquote className="font-serif text-xl sm:text-2xl md:text-3xl text-ivory text-[#F4F2ED] italic leading-relaxed">
                        &ldquo;We do not impose design; we allow the landscape to guide the process. Every detail is crafted with care, ensuring beauty and function evolve together over time. Our landscapes are made to belong, to endure, and to grow alongside the lives within them.&rdquo;
                    </blockquote>
                    <p className="mt-4 text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                        — Kode Landscape Studio Creed
                    </p>
                </InView>

                {/* Philosophy Pillars Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-14">
                    {activePhilosophy.map((item, i) => (
                        <InView key={i} delay={i * 0.1} className="border-t border-forest/30 border-white/10 pt-8 space-y-3">
                            <span className="font-serif text-3xl text-lime text-[#88B896] font-normal">{item.number}</span>
                            <h3 className="font-serif text-2xl tracking-tight text-ivory text-[#F4F2ED]">{item.title}</h3>
                            <p className="text-sm text-[#C4C9C3]/85 font-light leading-relaxed">{item.description}</p>
                        </InView>
                    ))}
                </div>
            </section>

            {/* Why Quality Landscape is Essential for Property Value Section */}
            <section className="bg-forest/15 bg-[#0E1310]/85 backdrop-blur-md border-y border-forest/30 border-white/10 text-ivory text-[#F4F2ED] py-24 sm:py-32 shadow-[inset_0_1px_0_rgba(255,255,255,0.06),inset_0_-1px_0_rgba(255,255,255,0.06)]">
                <div className="max-w-7xl mx-auto px-6">
                    <InView className="max-w-3xl mb-16">
                        <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                            Strategic Investment
                        </span>
                        <h2 className="font-serif text-3xl sm:text-5xl tracking-tight text-ivory text-[#F4F2ED] mt-2">
                            Why Quality Landscape is Essential for Property Value
                        </h2>
                        <p className="mt-6 text-base sm:text-lg text-[#C4C9C3]/90 font-light leading-relaxed">
                            Quality landscape design is essential as it directly enhances a property’s value, identity, and long-term performance. Beyond aesthetics, a thoughtfully designed landscape creates strong first impressions, reinforces architectural character, and establishes a seamless relationship between the built environment and its natural context.
                        </p>
                    </InView>

                    {/* Three Core Dimensions of Value */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <InView delay={0.08} className="border border-forest/40 border-white/10 bg-[#121212]/80 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-8 sm:p-10 space-y-4 shadow-xl hover:border-lime/40 transition-all duration-300 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] flex flex-col justify-between">
                            <div className="space-y-4">
                                <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">01 · Asset &amp; Identity</span>
                                <h3 className="font-serif text-2xl text-ivory text-[#F4F2ED]">Property Value &amp; Identity</h3>
                                <p className="text-sm text-[#C4C9C3]/85 font-light leading-relaxed">
                                    Strategic landscaping increases property value by elevating visual appeal, optimizing outdoor functionality, and strengthening environmental performance. For residential, hospitality, and commercial developments, landscape is not merely an enhancement; it is a strategic investment. It elevates user experience, promotes well-being, and establishes a distinctive sense of place that differentiates the property within a competitive market.
                                </p>
                            </div>
                        </InView>

                        <InView delay={0.16} className="border border-forest/40 border-white/10 bg-[#121212]/80 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-8 sm:p-10 space-y-4 shadow-xl hover:border-lime/40 transition-all duration-300 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] flex flex-col justify-between">
                            <div className="space-y-4">
                                <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">02 · Microclimate &amp; Hydrology</span>
                                <h3 className="font-serif text-2xl text-ivory text-[#F4F2ED]">Environmental Performance</h3>
                                <p className="text-sm text-[#C4C9C3]/85 font-light leading-relaxed">
                                    Carefully planned vegetation helps reduce heat, improve air quality, manage stormwater runoff, prevent erosion, support groundwater recharge, and create a comfortable microclimate; all while maintaining aesthetic harmony with the architecture and existing surroundings.
                                </p>
                            </div>
                        </InView>

                        <InView delay={0.24} className="border border-forest/40 border-white/10 bg-[#121212]/80 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-8 sm:p-10 space-y-4 shadow-xl hover:border-lime/40 transition-all duration-300 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] flex flex-col justify-between">
                            <div className="space-y-4">
                                <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">03 · Ecological Balance</span>
                                <h3 className="font-serif text-2xl text-ivory text-[#F4F2ED]">Sustainable Landscape Approach</h3>
                                <p className="text-sm text-[#C4C9C3]/85 font-light leading-relaxed">
                                    Furthermore, a sustainable landscape approach prioritizes the use of native plants, water-efficient systems, and environmentally responsible materials to support long-term ecological balance.
                                </p>
                            </div>
                        </InView>
                    </div>
                </div>
            </section>

            {/* From Concept to Creation Section (5 Steps) */}
            <section className="max-w-7xl mx-auto px-6 py-24 sm:py-32">
                <InView className="max-w-3xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                        Methodology &amp; Workflow
                    </span>
                    <h2 className="font-serif text-3xl sm:text-5xl tracking-tight text-ivory text-[#F4F2ED] mt-2">
                        From Concept to Creation
                    </h2>
                    <p className="mt-4 text-base sm:text-lg text-[#C4C9C3]/90 font-light leading-relaxed">
                        Our disciplined, five-step collaborative methodology ensures complete clarity, artistic integrity, and rigorous execution from first consultation to final site handover.
                    </p>
                </InView>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    {activeConceptSteps.map((step, i) => (
                        <InView
                            key={i}
                            delay={i * 0.08}
                            className="border border-forest/40 border-white/10 bg-[#121212]/90 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-6 sm:p-8 space-y-4 shadow-lg hover:border-lime/50 hover:shadow-[0_0_25px_rgba(176,228,84,0.12)] transition-all duration-300 flex flex-col justify-between group shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]"
                        >
                            <div>
                                <span className="font-serif text-3xl sm:text-4xl text-lime text-[#88B896] block mb-2 group-hover:translate-x-1 transition-transform duration-300">{step.number}</span>
                                <h3 className="font-serif text-lg sm:text-xl text-ivory text-[#F4F2ED] leading-snug">{step.title}</h3>
                            </div>
                            <p className="text-xs sm:text-sm text-[#C4C9C3]/80 font-light leading-relaxed">
                                {step.description}
                            </p>
                        </InView>
                    ))}
                </div>
            </section>

            {/* Leadership Team (Preserved Props) */}
            {team && team.length > 0 && (
                <section className="bg-forest/15 bg-[#0E1310]/85 backdrop-blur-md border-t border-forest/30 border-white/10 text-ivory text-[#F4F2ED] py-24 sm:py-32 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="max-w-2xl mb-16">
                            <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                                Studio Leadership
                            </span>
                            <h2 className="font-serif text-3xl sm:text-4xl tracking-tight text-ivory text-[#F4F2ED] mt-1">
                                Principal Practitioners
                            </h2>
                        </InView>

                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                            {team.map((member, i) => (
                                <InView key={i} delay={i * 0.08} className="space-y-4">
                                    <div className="aspect-[4/5] overflow-hidden bg-[#121814] border border-forest/40 border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                        <img
                                            src={member.image}
                                            alt={member.name}
                                            className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500 scale-100 hover:scale-105"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div>
                                        <h3 className="font-serif text-xl text-ivory text-[#F4F2ED]">{member.name}</h3>
                                        <p className="text-xs uppercase tracking-wider text-lime text-[#88B896] font-mono font-medium mt-1">{member.role}</p>
                                        <p className="text-xs text-[#C4C9C3]/80 font-light mt-2.5 leading-relaxed">{member.bio}</p>
                                    </div>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Accolades (Preserved Props) */}
            {accolades && accolades.length > 0 && (
                <section className="bg-[rgba(20,32,24,0.35)] bg-forest/10 backdrop-blur-sm border-t border-forest/30 border-white/10 py-20 sm:py-28 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                    <div className="max-w-7xl mx-auto px-6">
                        <InView className="max-w-xl mb-12">
                            <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                                Recognition
                            </span>
                            <h2 className="font-serif text-2xl sm:text-3xl text-ivory text-[#F4F2ED] mt-1">
                                Peer &amp; Industry Accolades
                            </h2>
                        </InView>

                        <div className="space-y-4">
                            {accolades.map((acc, i) => (
                                <InView key={i} className="flex items-center justify-between border-b border-forest/30 border-white/10 py-5 text-sm hover:border-lime/40 transition-colors">
                                    <span className="font-serif text-lg sm:text-xl text-ivory text-[#F4F2ED]">{acc.award}</span>
                                    <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">{acc.year}</span>
                                </InView>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Call to Action */}
            <section className="border-t border-forest/40 border-white/10 py-24 sm:py-32 bg-jet bg-[#0B0E0C]">
                <div className="max-w-7xl mx-auto px-6 text-center">
                    <InView className="max-w-3xl mx-auto space-y-8">
                        <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                            Begin Your Collaboration
                        </span>
                        <h2 className="font-serif text-3xl sm:text-5xl md:text-6xl text-ivory text-[#F4F2ED] tracking-tight">
                            Let the Landscape Guide the Process
                        </h2>
                        <p className="text-base sm:text-lg text-[#C4C9C3]/90 font-light leading-relaxed max-w-2xl mx-auto">
                            Whether creating a secluded private residence, hospitality resort, or landscape renovation in Bali, we bring creative vision and unified build craft to every terrain.
                        </p>
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                            <Button href="/contact" variant="primary" size="lg">
                                Initiate Project Consultation
                            </Button>
                            <Button href="/projects" variant="outline-light" size="lg">
                                View Completed Sanctuaries
                            </Button>
                        </div>
                    </InView>
                </div>
            </section>
        </PublicLayout>
    );
}
