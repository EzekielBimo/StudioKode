import React from 'react';
import { Link } from '@inertiajs/react';
import InView from '../UI/InView';
import Badge from '../UI/Badge';

export default function ProjectCard({ project, priority = false, delay = 0 }) {
    if (!project) return null;

    return (
        <InView delay={delay} className="group">
            <Link href={`/projects/${project.slug}`} className="block overflow-hidden">
                <div className="relative aspect-[16/10] overflow-hidden bg-[#121814] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <img
                        src={project.cover_image}
                        alt={project.title}
                        className="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                        loading={priority ? 'eager' : 'lazy'}
                        decoding="async"
                    />
                    {project.is_featured && (
                        <div className="absolute top-4 left-4">
                            <Badge variant="foliage">Featured Sanctuary</Badge>
                        </div>
                    )}
                </div>

                <div className="pt-4 pb-2">
                    <div className="flex items-center justify-between text-xs text-[#A8B0A6]/80 uppercase tracking-widest font-medium mb-1.5">
                        <span>{project.location}</span>
                        <span>{project.area_size} &middot; {project.year}</span>
                    </div>
                    <h3 className="font-serif text-2xl text-[#F4F2ED] group-hover:text-[#88B896] transition-colors">
                        {project.title}
                    </h3>
                    {project.excerpt && (
                        <p className="mt-1 text-sm text-[#C4C9C3]/80 line-clamp-2 font-light">
                            {project.excerpt}
                        </p>
                    )}
                </div>
            </Link>
        </InView>
    );
}
