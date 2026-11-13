import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Layers } from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';
import ProjectGalleryManager from '../../../Components/Admin/ProjectGalleryManager';

export default function Gallery({ project }) {
    return (
        <AdminLayout title={`${id.gallery.title}: ${project.title}`}>
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-white/10 gap-4">
                <div className="flex items-center space-x-3">
                    <Link
                        href={`/admin/projects/${project.id}/edit`}
                        className="p-2 text-stone-400 hover:text-emerald-400 hover:bg-white/5 rounded transition-colors"
                        title={id.common.back}
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </Link>
                    <div>
                        <div className="flex items-center space-x-2">
                            <h1 className="font-serif text-2xl sm:text-3xl tracking-tight text-stone-100">
                                {id.gallery.title}
                            </h1>
                            <span className="text-xs text-stone-400">
                                — {project.title}
                            </span>
                        </div>
                        <p className="text-xs text-stone-400 mt-1">
                            {id.gallery.subtitle}
                        </p>
                    </div>
                </div>

                <div className="flex items-center space-x-2">
                    <Link
                        href={`/admin/projects/${project.id}/edit`}
                        className="px-3 py-2 text-xs text-emerald-300 bg-white/5 border border-white/10 rounded hover:bg-white/10 transition-colors flex items-center space-x-1.5"
                    >
                        <Layers className="w-3.5 h-3.5" />
                        <span>{id.projects.tabs.details}</span>
                    </Link>
                </div>
            </div>

            {/* Embedded Gallery Manager */}
            <ProjectGalleryManager
                project={project}
                images={project.images || []}
            />
        </AdminLayout>
    );
}
