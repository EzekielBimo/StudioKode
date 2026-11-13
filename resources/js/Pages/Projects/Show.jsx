import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ImageGallery from '../../Components/Portfolio/ImageGallery';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import InView from '../../Components/UI/InView';
import Badge from '../../Components/UI/Badge';
import { ArrowLeft, MapPin, Calendar, Maximize2, UserCheck } from 'lucide-react';

export default function ProjectsShow({ project, images = [], relatedProjects = [] }) {
    if (!project) return null;

    return (
        <PublicLayout>
            <Head title={`${project.title} — Garden Architecture Sanctuary`} />

            {/* Back Navigation & Breadcrumb */}
            <div className="max-w-7xl mx-auto px-6 pt-12 pb-6">
                <Link
                    href="/projects"
                    className="inline-flex items-center text-xs uppercase tracking-widest text-[#A8B0A6]/70 hover:text-[#88B896] font-medium transition-colors"
                >
                    <ArrowLeft className="w-4 h-4 mr-2" /> Back to Portfolio
                </Link>
            </div>

            {/* Title & Metadata Section */}
            <div className="max-w-7xl mx-auto px-6 pb-12">
                <InView>
                    <div className="flex flex-wrap items-center gap-2 mb-3">
                        {project.is_featured && <Badge variant="foliage">Featured Sanctuary</Badge>}
                        <Badge variant="outline">{project.area_size}</Badge>
                    </div>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] tracking-tight">
                        {project.title}
                    </h1>
                </InView>

                {/* Metadata Grid */}
                <InView
                    delay={0.1}
                    className="grid grid-cols-2 md:grid-cols-4 gap-6 py-8 mt-6 border-y border-white/10 text-xs uppercase tracking-widest font-medium text-[#F4F2ED]"
                >
                    <div>
                        <span className="text-[#A8B0A6]/60 block mb-1">Location</span>
                        <div className="flex items-center">
                            <MapPin className="w-3.5 h-3.5 mr-1.5 text-[#88B896]" />
                            {project.location}
                        </div>
                    </div>
                    <div>
                        <span className="text-[#A8B0A6]/60 block mb-1">Year Completed</span>
                        <div className="flex items-center">
                            <Calendar className="w-3.5 h-3.5 mr-1.5 text-[#88B896]" />
                            {project.year}
                        </div>
                    </div>
                    <div>
                        <span className="text-[#A8B0A6]/60 block mb-1">Grounds Scale</span>
                        <div className="flex items-center">
                            <Maximize2 className="w-3.5 h-3.5 mr-1.5 text-[#88B896]" />
                            {project.area_size}
                        </div>
                    </div>
                    <div>
                        <span className="text-[#A8B0A6]/60 block mb-1">Client / Trust</span>
                        <div className="flex items-center">
                            <UserCheck className="w-3.5 h-3.5 mr-1.5 text-[#88B896]" />
                            {project.client || 'Private Commission'}
                        </div>
                    </div>
                </InView>
            </div>

            {/* Hero Cover Image */}
            <section className="max-w-7xl mx-auto px-6 pb-16">
                <InView className="aspect-[21/9] overflow-hidden bg-[#121814] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <img
                        src={project.cover_image}
                        alt={project.title}
                        className="w-full h-full object-cover"
                    />
                </InView>
            </section>

            {/* Narrative & Masterplan */}
            <section className="max-w-7xl mx-auto px-6 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-16">
                    <div className="lg:col-span-2 space-y-6">
                        <InView>
                            <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                                Architectural Narrative
                            </span>
                            <h2 className="font-serif text-3xl text-[#F4F2ED] mt-2 mb-6">
                                Ecological Intent &amp; Spatial Choreography
                            </h2>
                            <div className="prose prose-invert max-w-none text-base text-[#C4C9C3]/90 font-light leading-relaxed space-y-4 whitespace-pre-line">
                                {project.description}
                            </div>
                        </InView>
                    </div>

                    {/* Masterplan Sidebar */}
                    <div className="space-y-6">
                        {project.masterplan_image && (
                            <InView delay={0.15} className="border border-white/10 bg-[rgba(20,32,24,0.65)] backdrop-blur-md p-6 space-y-3 shadow-xl shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                                    Territorial Drawing
                                </span>
                                <h3 className="font-serif text-xl text-[#F4F2ED]">
                                    Masterplan Schematic
                                </h3>
                                <div className="aspect-[4/3] overflow-hidden bg-[#121814] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                                    <img
                                        src={project.masterplan_image}
                                        alt={`${project.title} masterplan schematic`}
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                                <p className="text-xs text-[#A8B0A6]/70 font-light">
                                    Hydrological contours, dry-creek drainage runnels, and specimen canopy placement.
                                </p>
                            </InView>
                        )}
                    </div>
                </div>
            </section>

            {/* Visual Archive / ImageGallery */}
            {images.length > 0 && (
                <section className="max-w-7xl mx-auto px-6 py-16 border-t border-white/10">
                    <InView className="mb-10">
                        <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                            Visual Documentation
                        </span>
                        <h2 className="font-serif text-3xl text-[#F4F2ED] mt-2">
                            Atmospheric &amp; Material Archives
                        </h2>
                    </InView>
                    <ImageGallery images={images} />
                </section>
            )}

            {/* Related Sanctuaries */}
            {relatedProjects.length > 0 && (
                <section className="max-w-7xl mx-auto px-6 py-24 border-t border-white/10">
                    <InView className="mb-10">
                        <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                            Further Sanctuaries
                        </span>
                        <h2 className="font-serif text-3xl text-[#F4F2ED] mt-2">
                            Related Works
                        </h2>
                    </InView>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {relatedProjects.map((p, idx) => (
                            <ProjectCard key={p.id} project={p} delay={idx * 0.1} />
                        ))}
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
