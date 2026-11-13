import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Plus,
    Search,
    X,
    Edit3,
    Trash2,
    Images,
    MapPin,
    Calendar,
    Sparkles,
    CheckCircle2,
    Eye,
    Layers,
    Filter,
} from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';
import { resolveImageUrl } from '../../../Components/Admin/ProjectGalleryManager';

export default function Index({ projects, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [featured, setFeatured] = useState(filters.featured ?? '');
    const [deletingId, setDeletingId] = useState(null);

    // Normalize projects list whether paginated object or raw collection
    const projectList = Array.isArray(projects)
        ? projects
        : projects?.data || [];
    const paginationLinks = !Array.isArray(projects) ? projects?.links : null;

    const applyFilters = (newSearch, newFeatured) => {
        const query = {};
        if (newSearch && newSearch.trim() !== '') {
            query.search = newSearch.trim();
        }
        if (newFeatured !== '' && newFeatured !== null && newFeatured !== undefined) {
            query.featured = newFeatured;
        }

        router.get('/admin/projects', query, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        applyFilters(search, featured);
    };

    const handleClearSearch = () => {
        setSearch('');
        applyFilters('', featured);
    };

    const handleFeaturedChange = (val) => {
        setFeatured(val);
        applyFilters(search, val);
    };

    const handleDelete = (project) => {
        if (!window.confirm(id.projects.confirm_delete_message)) {
            return;
        }

        setDeletingId(project.id);
        router.delete(`/admin/projects/${project.id}`, {
            preserveScroll: true,
            onFinish: () => setDeletingId(null),
        });
    };

    return (
        <AdminLayout title={id.projects.title}>
            {/* Page Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-white/10 gap-4">
                <div>
                    <h1 className="font-serif text-3xl sm:text-4xl tracking-tight text-stone-100">
                        {id.projects.title}
                    </h1>
                    <p className="text-xs sm:text-sm text-stone-400 mt-1.5">
                        {id.projects.subtitle}
                    </p>
                </div>
                <div>
                    <Link
                        href="/admin/projects/create"
                        className="inline-flex items-center space-x-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 text-xs font-semibold rounded border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)] transition-all"
                    >
                        <Plus className="w-4 h-4" />
                        <span>{id.projects.create_button}</span>
                    </Link>
                </div>
            </div>

            {/* Filter Toolbar Card */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded mb-6 p-4 sm:p-5">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    {/* Search Form */}
                    <form
                        onSubmit={handleSearchSubmit}
                        className="relative flex-1 max-w-lg"
                    >
                        <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder={id.projects.search_placeholder}
                            className="w-full pl-9 pr-9 py-2 text-xs border border-white/10 rounded bg-[#0E1310]/80 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] text-stone-100 placeholder:text-stone-500 focus:bg-[#0E1310] focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-colors"
                        />
                        {search && (
                            <button
                                type="button"
                                onClick={handleClearSearch}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-100"
                            >
                                <X className="w-3.5 h-3.5" />
                            </button>
                        )}
                    </form>

                    {/* Featured Filter Dropdown */}
                    <div className="flex items-center space-x-3 self-end md:self-auto">
                        <div className="flex items-center space-x-2 text-xs text-stone-400">
                            <Filter className="w-3.5 h-3.5" />
                            <span>Unggulan:</span>
                        </div>
                        <select
                            value={featured}
                            onChange={(e) => handleFeaturedChange(e.target.value)}
                            className="text-xs px-3 py-2 border border-white/10 rounded bg-[#0E1310]/80 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] text-stone-200 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50"
                        >
                            <option value="">{id.projects.filter_all_featured}</option>
                            <option value="1">{id.projects.filter_featured}</option>
                            <option value="0">{id.projects.filter_standard}</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* Projects Table Card */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="border-b border-white/10 bg-[#0E1310]/70 text-[11px] uppercase tracking-wider text-stone-400 font-semibold">
                                <th className="py-3 px-4 w-20">{id.projects.table.thumbnail}</th>
                                <th className="py-3 px-4">{id.projects.table.title}</th>
                                <th className="py-3 px-4">{id.projects.table.location}</th>
                                <th className="py-3 px-4">{id.projects.table.area}</th>
                                <th className="py-3 px-4 text-center">{id.projects.table.status}</th>
                                <th className="py-3 px-4 text-center">{id.projects.table.featured}</th>
                                <th className="py-3 px-4 text-center">{id.projects.table.images_count}</th>
                                <th className="py-3 px-4 text-right">{id.projects.table.actions}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5 text-xs">
                            {projectList.length === 0 ? (
                                <tr>
                                    <td colSpan="8" className="py-12 text-center text-stone-400">
                                        <Layers className="w-10 h-10 mx-auto text-stone-500/40 mb-2" />
                                        <p className="text-sm font-medium text-stone-300">
                                            {search || featured !== ''
                                                ? id.projects.empty_filtered
                                                : id.projects.empty_state}
                                        </p>
                                    </td>
                                </tr>
                            ) : (
                                projectList.map((project) => {
                                    const isPublished = Boolean(project.published_at);
                                    const isFeatured = Boolean(project.is_featured);
                                    const imagesCount = project.images_count ?? (project.images ? project.images.length : 0);

                                    return (
                                        <tr
                                            key={project.id}
                                            className="hover:bg-white/[0.03] transition-colors"
                                        >
                                            {/* Thumbnail */}
                                            <td className="py-3 px-4">
                                                <div className="w-16 h-12 aspect-[4/3] rounded overflow-hidden bg-[#0E1310] border border-white/10 shrink-0">
                                                    <img
                                                        src={resolveImageUrl(project.cover_image)}
                                                        alt={project.title}
                                                        className="w-full h-full object-cover"
                                                        loading="lazy"
                                                        decoding="async"
                                                    />
                                                </div>
                                            </td>

                                            {/* Title & Client */}
                                            <td className="py-3 px-4">
                                                <div className="font-medium text-stone-100">
                                                    <Link
                                                        href={`/admin/projects/${project.id}/edit`}
                                                        className="hover:text-emerald-300 transition-colors font-semibold"
                                                    >
                                                        {project.title}
                                                    </Link>
                                                </div>
                                                {project.client ? (
                                                    <div className="text-[11px] text-stone-400 mt-0.5">
                                                        {project.client}
                                                    </div>
                                                ) : (
                                                    <div className="text-[11px] text-stone-500 italic mt-0.5">
                                                        Klien privat
                                                    </div>
                                                )}
                                            </td>

                                            {/* Location & Year */}
                                            <td className="py-3 px-4 whitespace-nowrap">
                                                <div className="flex items-center space-x-1.5 text-stone-200">
                                                    <MapPin className="w-3.5 h-3.5 text-stone-400 shrink-0" />
                                                    <span>{project.location}</span>
                                                </div>
                                                <div className="flex items-center space-x-1 text-[11px] text-stone-400 mt-0.5">
                                                    <Calendar className="w-3 h-3 text-stone-500 shrink-0" />
                                                    <span>{project.year}</span>
                                                </div>
                                            </td>

                                            {/* Area */}
                                            <td className="py-3 px-4 font-mono text-[11px] text-stone-300 whitespace-nowrap">
                                                {project.area_size}
                                            </td>

                                            {/* Publication Status */}
                                            <td className="py-3 px-4 text-center whitespace-nowrap">
                                                {isPublished ? (
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                        {id.projects.filter_published}
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-stone-800/60 text-stone-400 border border-white/10">
                                                        {id.projects.filter_draft}
                                                    </span>
                                                )}
                                            </td>

                                            {/* Featured Status */}
                                            <td className="py-3 px-4 text-center whitespace-nowrap">
                                                {isFeatured ? (
                                                    <span className="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[11px] font-medium bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                        <Sparkles className="w-3 h-3 text-amber-300" />
                                                        <span>{id.status.unggulan}</span>
                                                    </span>
                                                ) : (
                                                    <span className="text-stone-600 text-xs">—</span>
                                                )}
                                            </td>

                                            {/* Gallery Count */}
                                            <td className="py-3 px-4 text-center whitespace-nowrap">
                                                <Link
                                                    href={`/admin/projects/${project.id}/edit#gallery`}
                                                    className="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[11px] font-medium bg-white/5 text-emerald-300 border border-white/10 hover:bg-white/10 transition-colors"
                                                >
                                                    <Images className="w-3 h-3 text-emerald-400" />
                                                    <span>{imagesCount} Foto</span>
                                                </Link>
                                            </td>

                                            {/* Actions */}
                                            <td className="py-3 px-4 text-right whitespace-nowrap">
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Link
                                                        href={`/admin/projects/${project.id}/edit`}
                                                        className="p-1.5 text-emerald-400 hover:bg-emerald-500/10 rounded border border-white/10 hover:border-emerald-500/30 transition-colors"
                                                        title={id.common.edit}
                                                    >
                                                        <Edit3 className="w-3.5 h-3.5" />
                                                    </Link>
                                                    <Link
                                                        href={`/admin/projects/${project.id}/edit#gallery`}
                                                        className="p-1.5 text-emerald-400 hover:bg-emerald-500/10 rounded border border-white/10 hover:border-emerald-500/30 transition-colors"
                                                        title={id.projects.manage_gallery}
                                                    >
                                                        <Images className="w-3.5 h-3.5" />
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(project)}
                                                        disabled={deletingId === project.id}
                                                        className="p-1.5 text-rose-400 hover:bg-rose-500/10 rounded border border-rose-500/30 disabled:opacity-50 transition-colors"
                                                        title={id.common.delete}
                                                    >
                                                        <Trash2 className="w-3.5 h-3.5" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination Controls */}
                {paginationLinks && paginationLinks.length > 3 && (
                    <div className="p-4 sm:p-5 border-t border-white/10 flex items-center justify-between">
                        <div className="flex items-center space-x-1 mx-auto sm:mx-0">
                            {paginationLinks.map((link, idx) =>
                                link.url ? (
                                    <Link
                                        key={idx}
                                        href={link.url}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 text-xs rounded border transition-colors ${
                                            link.active
                                                ? 'bg-emerald-600 text-stone-50 border-emerald-400/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]'
                                                : 'text-stone-400 border-white/10 hover:bg-white/5 hover:text-stone-200'
                                        }`}
                                    />
                                ) : (
                                    <span
                                        key={idx}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className="px-3 py-1.5 text-xs rounded border border-transparent text-stone-600"
                                    />
                                )
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
