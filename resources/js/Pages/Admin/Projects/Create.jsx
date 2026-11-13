import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
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
} from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';

export default function Create() {
    const [autoSlug, setAutoSlug] = useState(true);
    const [coverPreview, setCoverPreview] = useState(null);
    const [masterplanPreview, setMasterplanPreview] = useState(null);

    const { data, setData, post, processing, errors } = useForm({
        title: '',
        slug: '',
        excerpt: '',
        description: '',
        client: '',
        location: '',
        year: new Date().getFullYear(),
        area_size: '',
        cover_image: null,
        masterplan_image: null,
        is_featured: false,
        published_at: '',
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

    const handleRemoveCover = () => {
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

    const handleRemoveMasterplan = () => {
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
        post('/admin/projects', {
            forceFormData: true,
        });
    };

    return (
        <AdminLayout title={id.projects.create_title}>
            {/* Header */}
            <div className="flex items-center space-x-3 pb-6 mb-6 border-b border-white/10">
                <Link
                    href="/admin/projects"
                    className="p-2 text-stone-400 hover:text-emerald-400 hover:bg-white/5 rounded transition-colors"
                    title={id.common.back}
                >
                    <ArrowLeft className="w-5 h-5" />
                </Link>
                <div>
                    <h1 className="font-serif text-3xl tracking-tight text-stone-100">
                        {id.projects.create_title}
                    </h1>
                    <p className="text-xs text-stone-400 mt-1">
                        {id.projects.create_subtitle}
                    </p>
                </div>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left/Main Column: Project Details (2 Cols) */}
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
                                    id="is_featured"
                                    checked={Boolean(data.is_featured)}
                                    onChange={(e) => setData('is_featured', e.target.checked)}
                                    className="mt-1 w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500 bg-[#0E1310] border-white/20 accent-emerald-500"
                                />
                                <label htmlFor="is_featured" className="text-xs cursor-pointer select-none">
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

                        {/* Cover Image Upload Card */}
                        <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded space-y-3">
                            <h2 className="text-xs font-semibold uppercase tracking-wider text-stone-300">
                                {id.projects.fields.cover_image}
                            </h2>
                            <p className="text-[11px] text-stone-400">
                                {id.projects.fields.cover_image_help}
                            </p>

                            {coverPreview ? (
                                <div className="relative aspect-[16/10] rounded overflow-hidden border border-white/10 bg-[#0E1310]">
                                    <img
                                        src={coverPreview}
                                        alt="Preview Cover"
                                        className="w-full h-full object-cover"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                    <button
                                        type="button"
                                        onClick={handleRemoveCover}
                                        className="absolute top-2 right-2 p-1 bg-black/70 text-white rounded-full hover:bg-black/90"
                                    >
                                        <X className="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            ) : (
                                <label className="block border-2 border-dashed border-white/15 rounded-lg p-5 text-center cursor-pointer hover:border-emerald-500/50 bg-[#0E1310]/40 transition-colors">
                                    <UploadCloud className="w-8 h-8 mx-auto text-stone-400 mb-2" />
                                    <span className="text-xs font-medium text-stone-300">
                                        Pilih Berkas Sampul
                                    </span>
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp,image/jpg"
                                        onChange={handleCoverChange}
                                        className="hidden"
                                    />
                                </label>
                            )}
                            {errors.cover_image && (
                                <p className="text-xs text-rose-400">{errors.cover_image}</p>
                            )}
                        </div>

                        {/* Masterplan Image Upload Card */}
                        <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded space-y-3">
                            <h2 className="text-xs font-semibold uppercase tracking-wider text-stone-300">
                                {id.projects.fields.masterplan_image}
                            </h2>
                            <p className="text-[11px] text-stone-400">
                                {id.projects.fields.masterplan_image_help}
                            </p>

                            {masterplanPreview ? (
                                <div className="relative aspect-video rounded overflow-hidden border border-white/10 bg-[#0E1310]">
                                    <img
                                        src={masterplanPreview}
                                        alt="Preview Masterplan"
                                        className="w-full h-full object-cover"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                    <button
                                        type="button"
                                        onClick={handleRemoveMasterplan}
                                        className="absolute top-2 right-2 p-1 bg-black/70 text-white rounded-full hover:bg-black/90"
                                    >
                                        <X className="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            ) : (
                                <label className="block border-2 border-dashed border-white/15 rounded-lg p-5 text-center cursor-pointer hover:border-emerald-500/50 bg-[#0E1310]/40 transition-colors">
                                    <UploadCloud className="w-8 h-8 mx-auto text-stone-400 mb-2" />
                                    <span className="text-xs font-medium text-stone-300">
                                        Pilih Berkas Masterplan
                                    </span>
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp,image/jpg"
                                        onChange={handleMasterplanChange}
                                        className="hidden"
                                    />
                                </label>
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
                                {processing ? id.projects.saving_button : id.projects.save_button}
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
        </AdminLayout>
    );
}
