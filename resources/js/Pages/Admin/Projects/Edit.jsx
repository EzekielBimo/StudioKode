import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import {
    ArrowLeft,
    UploadCloud,
    X,
    Sparkles,
    Calendar,
    Check,
    AlertCircle,
    Info,
    ImageIcon,
    Layers,
    Images,
    RefreshCw,
} from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';
import ProjectGalleryManager, { resolveImageUrl } from '../../../Components/Admin/ProjectGalleryManager';

export default function Edit({ project }) {
    const [activeTab, setActiveTab] = useState('details');
    const [autoSlug, setAutoSlug] = useState(false);
    const [coverPreview, setCoverPreview] = useState(null);
    const [masterplanPreview, setMasterplanPreview] = useState(null);

    // Read URL hash on mount to switch to gallery tab if #gallery
    useEffect(() => {
        if (typeof window !== 'undefined' && window.location.hash === '#gallery') {
            setActiveTab('gallery');
        }
    }, []);

    // Format published_at for datetime-local input
    const formatPublishedAt = (val) => {
        if (!val) return '';
        try {
            const d = new Date(val);
            if (isNaN(d.getTime())) return '';
            return new Date(d.getTime() - d.getTimezoneOffset() * 60000)
                .toISOString()
                .slice(0, 16);
        } catch {
            return '';
        }
    };

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT',
        title: project.title || '',
        slug: project.slug || '',
        excerpt: project.excerpt || '',
        description: project.description || '',
        client: project.client || '',
        location: project.location || '',
        year: project.year || new Date().getFullYear(),
        area_size: project.area_size || '',
        cover_image: null,
        masterplan_image: null,
        is_featured: Boolean(project.is_featured),
        published_at: formatPublishedAt(project.published_at),
    });

    const slugify = (text) => {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    };

    const handleTitleChange = (e) => {
        const titleVal = e.target.value;
        setData((prev) => ({
            ...prev,
            title: titleVal,
            slug: autoSlug ? slugify(titleVal) : prev.slug,
        }));
    };

    const handleSlugChange = (e) => {
        setAutoSlug(false);
        setData('slug', e.target.value);
    };

    const handleCoverChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('cover_image', file);
            if (coverPreview) URL.revokeObjectURL(coverPreview);
            setCoverPreview(URL.createObjectURL(file));
        }
    };

    const handleCancelNewCover = () => {
        if (coverPreview) URL.revokeObjectURL(coverPreview);
        setCoverPreview(null);
        setData('cover_image', null);
    };

    const handleMasterplanChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('masterplan_image', file);
            if (masterplanPreview) URL.revokeObjectURL(masterplanPreview);
            setMasterplanPreview(URL.createObjectURL(file));
        }
    };

    const handleCancelNewMasterplan = () => {
        if (masterplanPreview) URL.revokeObjectURL(masterplanPreview);
        setMasterplanPreview(null);
        setData('masterplan_image', null);
    };

    const handlePublishNow = () => {
        const now = new Date();
        const localIso = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
            .toISOString()
            .slice(0, 16);
        setData('published_at', localIso);
    };

    const handleSaveDraft = () => {
        setData('published_at', '');
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        // Method spoofing via POST to support multipart/form-data with PUT semantics
        post(`/admin/projects/${project.id}`, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const galleryCount = project.images ? project.images.length : (project.images_count ?? 0);

    return (
        <AdminLayout title={`${id.projects.edit_title}: ${project.title}`}>
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-white/10 gap-4">
                <div className="flex items-center space-x-3">
                    <Link
                        href="/admin/projects"
                        className="p-2 text-stone-400 hover:text-emerald-400 hover:bg-white/5 rounded transition-colors"
                        title={id.common.back}
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </Link>
                    <div>
                        <div className="flex items-center space-x-2">
                            <h1 className="font-serif text-2xl sm:text-3xl tracking-tight text-stone-100">
                                {project.title}
                            </h1>
                            {project.is_featured && (
                                <span className="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[11px] font-medium bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <Sparkles className="w-3 h-3 text-amber-300" />
                                    <span>{id.status.unggulan}</span>
                                </span>
                            )}
                        </div>
                        <p className="text-xs text-stone-400 mt-1">
                            {id.projects.edit_subtitle}
                        </p>
                    </div>
                </div>

                <div className="flex items-center space-x-2 self-end sm:self-auto">
                    <a
                        href={`/projects/${project.slug}`}
                        target="_blank"
                        rel="noreferrer"
                        className="px-3 py-2 text-xs text-emerald-300 bg-white/5 border border-white/10 rounded hover:bg-white/10 transition-colors"
                    >
                        {id.nav.view_site}
                    </a>
                </div>
            </div>

            {/* Navigation Tabs */}
            <div className="flex items-center space-x-2 border-b border-white/10 mb-6">
                <button
                    type="button"
                    onClick={() => {
                        setActiveTab('details');
                        if (typeof window !== 'undefined') window.history.replaceState(null, '', ' ');
                    }}
                    className={`px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors flex items-center space-x-2 ${
                        activeTab === 'details'
                            ? 'border-emerald-400 text-emerald-300'
                            : 'border-transparent text-stone-400 hover:text-stone-200'
                    }`}
                >
                    <Layers className="w-4 h-4" />
                    <span>{id.projects.tabs.details}</span>
                </button>

                <button
                    type="button"
                    onClick={() => {
                        setActiveTab('gallery');
                        if (typeof window !== 'undefined') window.location.hash = 'gallery';
                    }}
                    className={`px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors flex items-center space-x-2 ${
                        activeTab === 'gallery'
                            ? 'border-emerald-400 text-emerald-300'
                            : 'border-transparent text-stone-400 hover:text-stone-200'
                    }`}
                >
                    <Images className="w-4 h-4" />
                    <span>{id.projects.tabs.gallery}</span>
                    <span className="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-white/10 text-emerald-300 border border-white/10">
                        {galleryCount}
                    </span>
                </button>
            </div>

            {/* Tab 1: Project Details Form */}
            {activeTab === 'details' && (
                <form onSubmit={handleSubmit}>
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Main Details (2 Cols) */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Title & Slug Card */}
                            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-stone-300 mb-1.5">
                                        {id.projects.fields.title} <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.title}
                                        onChange={handleTitleChange}
                                        placeholder={id.projects.fields.title_placeholder}
                                        className={`w-full text-sm px-3.5 py-2.5 border rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 ${
                                            errors.title
                                                ? 'border-rose-500/80 focus:ring-rose-500/50'
                                                : 'border-white/10 focus:ring-emerald-500/50 focus:border-emerald-500/50'
                                        }`}
                                    />
                                    {errors.title && (
                                        <p className="text-xs text-rose-400 mt-1">{errors.title}</p>
                                    )}
                                </div>

                                <div>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-stone-300">
                                            {id.projects.fields.slug}
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                const nextVal = !autoSlug;
                                                setAutoSlug(nextVal);
                                                if (nextVal) {
                                                    setData('slug', slugify(data.title));
                                                }
                                            }}
                                            className="text-[11px] text-stone-400 hover:text-emerald-400 flex items-center space-x-1"
                                        >
                                            <span className={`w-2 h-2 rounded-full ${autoSlug ? 'bg-emerald-500' : 'bg-stone-600'}`} />
                                            <span>{autoSlug ? id.projects.fields.auto_slug : id.projects.fields.manual_slug}</span>
                                        </button>
                                    </div>
                                    <input
                                        type="text"
                                        value={data.slug}
                                        onChange={handleSlugChange}
                                        placeholder={id.projects.fields.slug_placeholder}
                                        className={`w-full text-xs font-mono px-3.5 py-2 border rounded bg-[#0B0E0C]/90 text-stone-200 placeholder:text-stone-500 focus:outline-none focus:ring-1 ${
                                            errors.slug
                                                ? 'border-rose-500/80 focus:ring-rose-500/50'
                                                : 'border-white/10 focus:ring-emerald-500/50 focus:border-emerald-500/50'
                                        }`}
                                    />
                                    {errors.slug && (
                                        <p className="text-xs text-rose-400 mt-1">{errors.slug}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-stone-300 mb-1.5">
                                        {id.projects.fields.excerpt}
                                    </label>
                                    <textarea
                                        rows="2"
                                        value={data.excerpt}
                                        onChange={(e) => setData('excerpt', e.target.value)}
                                        placeholder={id.projects.fields.excerpt_placeholder}
                                        className="w-full text-xs px-3.5 py-2 border border-white/10 rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-stone-300 mb-1.5">
                                        {id.projects.fields.description} <span className="text-rose-400">*</span>
                                    </label>
                                    <textarea
                                        rows="6"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder={id.projects.fields.description_placeholder}
                                        className={`w-full text-xs px-3.5 py-2.5 border rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 ${
                                            errors.description
                                                ? 'border-rose-500/80 focus:ring-rose-500/50'
                                                : 'border-white/10 focus:ring-emerald-500/50 focus:border-emerald-500/50'
                                        }`}
                                    />
                                    {errors.description && (
                                        <p className="text-xs text-rose-400 mt-1">{errors.description}</p>
                                    )}
                                </div>
                            </div>

                            {/* Architectural Metadata Grid */}
                            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded">
                                <h2 className="text-xs font-semibold uppercase tracking-wider text-stone-300 pb-2 border-b border-white/10 mb-4">
                                    Parameter Arsitektur & Spesifikasi Lahan
                                </h2>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-stone-300 mb-1">
                                        {id.projects.fields.location} <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.location}
                                        onChange={(e) => setData('location', e.target.value)}
                                        placeholder={id.projects.fields.location_placeholder}
                                        className={`w-full text-xs px-3 py-2 border rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 ${
                                            errors.location
                                                ? 'border-rose-500/80 focus:ring-rose-500/50'
                                                : 'border-white/10 focus:ring-emerald-500/50 focus:border-emerald-500/50'
                                        }`}
                                    />
                                    {errors.location && (
                                        <p className="text-xs text-rose-400 mt-1">{errors.location}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-stone-300 mb-1">
                                        {id.projects.fields.area_size} <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.area_size}
                                        onChange={(e) => setData('area_size', e.target.value)}
                                        placeholder={id.projects.fields.area_size_placeholder}
                                        className={`w-full text-xs px-3 py-2 border rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 ${
                                            errors.area_size
                                                ? 'border-rose-500/80 focus:ring-rose-500/50'
                                                : 'border-white/10 focus:ring-emerald-500/50 focus:border-emerald-500/50'
                                        }`}
                                    />
                                    {errors.area_size && (
                                        <p className="text-xs text-rose-400 mt-1">{errors.area_size}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-stone-300 mb-1">
                                        {id.projects.fields.year} <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min="1900"
                                        max="2100"
                                        value={data.year}
                                        onChange={(e) => setData('year', e.target.value)}
                                        placeholder={id.projects.fields.year_placeholder}
                                        className={`w-full text-xs px-3 py-2 border rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 ${
                                            errors.year
                                                ? 'border-rose-500/80 focus:ring-rose-500/50'
                                                : 'border-white/10 focus:ring-emerald-500/50 focus:border-emerald-500/50'
                                        }`}
                                    />
                                    {errors.year && (
                                        <p className="text-xs text-rose-400 mt-1">{errors.year}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-stone-300 mb-1">
                                        {id.projects.fields.client}
                                    </label>
                                    <input
                                        type="text"
                                        value={data.client}
                                        onChange={(e) => setData('client', e.target.value)}
                                        placeholder={id.projects.fields.client_placeholder}
                                        className="w-full text-xs px-3 py-2 border border-white/10 rounded bg-[#0E1310]/80 text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Settings & Visual Assets (1 Col) */}
                    <div className="space-y-6">
                        {/* Publish & Featured Status Card */}
                        <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded space-y-4">
                            <h2 className="text-xs font-semibold uppercase tracking-wider text-stone-300 pb-2 border-b border-white/10">
                                Status & Pengaturan
                            </h2>

                            {/* Featured Toggle */}
                            <div className="flex items-start space-x-3 pt-1">
                                <input
                                    type="checkbox"
                                    id="is_featured_edit"
                                    checked={Boolean(data.is_featured)}
                                    onChange={(e) => setData('is_featured', e.target.checked)}
                                    className="mt-1 w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500 bg-[#0E1310] border-white/20 accent-emerald-500"
                                />
                                <label htmlFor="is_featured_edit" className="text-xs cursor-pointer select-none">
                                    <div className="font-semibold text-stone-100 flex items-center space-x-1">
                                        <Sparkles className="w-3.5 h-3.5 text-amber-300" />
                                        <span>{id.projects.fields.is_featured}</span>
                                    </div>
                                    <p className="text-[11px] text-stone-400 mt-0.5">
                                        {id.projects.fields.is_featured_hint}
                                    </p>
                                </label>
                            </div>

                            {/* Publication Date */}
                            <div className="pt-3 border-t border-white/10">
                                <label className="block text-xs font-semibold text-stone-300 mb-1">
                                    {id.projects.fields.published_at}
                                </label>
                                <input
                                    type="datetime-local"
                                    value={data.published_at}
                                    onChange={(e) => setData('published_at', e.target.value)}
                                    className="w-full text-xs px-3 py-2 border border-white/10 rounded bg-[#0E1310]/80 text-stone-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 [color-scheme:dark]"
                                />
                                <div className="flex items-center space-x-2 mt-2">
                                    <button
                                        type="button"
                                        onClick={handlePublishNow}
                                        className="text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline"
                                    >
                                        {id.projects.fields.publish_now}
                                    </button>
                                    <span className="text-stone-600">•</span>
                                    <button
                                        type="button"
                                        onClick={handleSaveDraft}
                                        className="text-[11px] text-stone-400 hover:text-rose-400"
                                    >
                                        {id.projects.fields.set_as_draft}
                                    </button>
                                </div>
                                <p className="text-[11px] text-stone-400 mt-1.5">
                                    {id.projects.fields.published_at_help}
                                </p>
                            </div>
                        </div>

                        {/* Cover Image Replacement Card */}
                        <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded space-y-3">
                            <h2 className="text-xs font-semibold uppercase tracking-wider text-stone-300">
                                {id.projects.fields.cover_image}
                            </h2>

                            {coverPreview ? (
                                <div className="space-y-2">
                                    <div className="relative aspect-[16/10] rounded overflow-hidden border border-white/10 bg-[#0E1310]">
                                        <img
                                            src={coverPreview}
                                            alt="Pratinjau Gambar Baru"
                                            className="w-full h-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                        <button
                                            type="button"
                                            onClick={handleCancelNewCover}
                                            className="absolute top-2 right-2 p-1 bg-black/70 text-white rounded-full hover:bg-black/90"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                    <p className="text-[11px] text-amber-300 bg-amber-500/15 p-2 rounded border border-amber-500/30">
                                        Berkas baru dipilih dan siap diperbarui.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {project.cover_image && (
                                        <div className="aspect-[16/10] rounded overflow-hidden border border-white/10 bg-[#0E1310]">
                                            <img
                                                src={resolveImageUrl(project.cover_image)}
                                                alt="Gambar Sampul Saat Ini"
                                                className="w-full h-full object-cover"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        </div>
                                    )}
                                    <label className="block border border-white/10 rounded p-2.5 text-center cursor-pointer hover:bg-white/5 transition-colors">
                                        <span className="text-xs font-medium text-emerald-300 flex items-center justify-center space-x-1">
                                            <RefreshCw className="w-3.5 h-3.5" />
                                            <span>{id.projects.fields.replace_image}</span>
                                        </span>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp,image/jpg"
                                            onChange={handleCoverChange}
                                            className="hidden"
                                        />
                                    </label>
                                </div>
                            )}
                            {errors.cover_image && (
                                <p className="text-xs text-rose-400">{errors.cover_image}</p>
                            )}
                        </div>

                        {/* Masterplan Image Replacement Card */}
                        <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded space-y-3">
                            <h2 className="text-xs font-semibold uppercase tracking-wider text-stone-300">
                                {id.projects.fields.masterplan_image}
                            </h2>

                            {masterplanPreview ? (
                                <div className="space-y-2">
                                    <div className="relative aspect-video rounded overflow-hidden border border-white/10 bg-[#0E1310]">
                                        <img
                                            src={masterplanPreview}
                                            alt="Pratinjau Masterplan Baru"
                                            className="w-full h-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                        <button
                                            type="button"
                                            onClick={handleCancelNewMasterplan}
                                            className="absolute top-2 right-2 p-1 bg-black/70 text-white rounded-full hover:bg-black/90"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                    <p className="text-[11px] text-amber-300 bg-amber-500/15 p-2 rounded border border-amber-500/30">
                                        Berkas masterplan baru dipilih dan siap diperbarui.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {project.masterplan_image ? (
                                        <div className="aspect-video rounded overflow-hidden border border-white/10 bg-[#0E1310]">
                                            <img
                                                src={resolveImageUrl(project.masterplan_image)}
                                                alt="Masterplan Saat Ini"
                                                className="w-full h-full object-cover"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        </div>
                                    ) : (
                                        <div className="py-4 text-center border border-dashed border-white/15 rounded text-xs text-stone-400">
                                            Belum ada gambar masterplan.
                                        </div>
                                    )}
                                    <label className="block border border-white/10 rounded p-2.5 text-center cursor-pointer hover:bg-white/5 transition-colors">
                                        <span className="text-xs font-medium text-emerald-300 flex items-center justify-center space-x-1">
                                            <UploadCloud className="w-3.5 h-3.5" />
                                            <span>
                                                {project.masterplan_image
                                                    ? id.projects.fields.replace_image
                                                    : 'Unggah Gambar Masterplan'}
                                            </span>
                                        </span>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp,image/jpg"
                                            onChange={handleMasterplanChange}
                                            className="hidden"
                                        />
                                    </label>
                                </div>
                            )}
                            {errors.masterplan_image && (
                                <p className="text-xs text-rose-400">{errors.masterplan_image}</p>
                            )}
                        </div>

                        {/* Submit Button Bar */}
                        <div className="space-y-2 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 text-xs font-semibold rounded border border-emerald-400/30 shadow-[0_0_20px_rgba(16,185,129,0.25)] disabled:opacity-50 transition-all"
                            >
                                {processing ? id.projects.updating_button : id.projects.update_button}
                            </button>
                            <Link
                                href="/admin/projects"
                                className="block w-full py-2.5 text-center text-xs text-stone-300 hover:text-white rounded border border-white/10 bg-white/5 hover:bg-white/10 transition-colors"
                            >
                                {id.common.cancel}
                            </Link>
                        </div>
                    </div>
                </div>
            </form>
            )}

            {/* Tab 2: Gallery Management */}
            {activeTab === 'gallery' && (
                <ProjectGalleryManager
                    project={project}
                    images={project.images || []}
                />
            )}
        </AdminLayout>
    );
}
