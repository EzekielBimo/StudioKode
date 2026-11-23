import React from 'react';
import { Link } from '@inertiajs/react';
import InView from '../UI/InView';
import Badge from '../UI/Badge';

export default function ProjectCard({ project, priority = false, delay = 0 }) {
    if (!project) return null;

    return (
        <InView delay={delay} className="group">
            <Link href={`/projects/${project.slug}`} className="block overflow-hidden">
                <div className="relative aspect-[16/10] overflow-hidden bg-[#121814] border border-white/10 group-hover:border-[#B0E454]/30 transition-colors duration-500 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <img
                        src={project.cover_image}
                        alt={project.title}
                        className="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105 group-hover:scale-[1.03]"
                        loading={priority ? 'eager' : 'lazy'}
                        decoding="async"
                    />
                    {project.is_featured && (
                        <div className="absolute top-4 left-4">
                            <Badge variant="foliage">Featured Sanctuary</Badge>
                        </div>
                    )}
                    {project.category && (
                        <div className="absolute top-4 right-4">
                            <span className="px-2.5 py-1 text-[10px] uppercase tracking-widest font-medium rounded-full bg-[#0D0D0D]/85 bg-[#0B0E0C]/85 text-[#88B896] text-[#B0E454] border border-[#B0E454]/30 border-white/15 backdrop-blur-sm shadow-[inset_0_1px_0_rgba(176,228,84,0.12)]">
                                {project.category}
                            </span>
                        </div>
                    )}
                </div>

                <div className="pt-4 pb-2">
                    <div className="flex items-center justify-between text-xs text-[#EAFFD0]/70 text-[#A8B0A6]/80 uppercase tracking-widest font-medium mb-1.5">
                        <span>{project.location}</span>
                        <span>{project.area_size} &middot; {project.year}</span>
                    </div>
                    <h3 className="font-serif text-2xl text-[#F4F2ED] text-[#EAFFD0] group-hover:text-[#88B896] group-hover:text-[#B0E454] transition-colors duration-300">
                        {project.title}
                    </h3>
                    {project.excerpt && (
                        <p className="mt-1 text-sm text-[#EAFFD0]/60 text-[#C4C9C3]/80 line-clamp-2 font-light">
                            {project.excerpt}
                        </p>
                    )}
                </div>
            </Link>
        </InView>
    );
}
