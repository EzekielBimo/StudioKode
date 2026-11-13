import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';
import {
    ArrowLeft,
    Mail,
    Phone,
    MapPin,
    Maximize2,
    DollarSign,
    Calendar,
    Clock,
    Trash2,
    CheckCircle,
    Archive,
    RotateCcw,
} from 'lucide-react';

export default function InquiriesShow({ inquiry }) {
    const [updating, setUpdating] = useState(false);

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        try {
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'full',
                timeStyle: 'short',
            }).format(date);
        } catch {
            return dateString;
        }
    };

    const handleStatusChange = (newStatus) => {
        setUpdating(true);
        router.patch(
            `/admin/inquiries/${inquiry.id}`,
            { status: newStatus },
            {
                preserveScroll: true,
                onFinish: () => setUpdating(false),
            }
        );
    };

    const handleDelete = () => {
        if (window.confirm(id.inquiries.delete_confirm || 'Apakah Anda yakin ingin menghapus pesan ini?')) {
            router.delete(`/admin/inquiries/${inquiry.id}`);
        }
    };

    const normalizedStatus = (inquiry.status || '').toLowerCase();

    const getStatusBadge = () => {
        if (normalizedStatus === 'new' || normalizedStatus === 'baru') {
            return (
                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2 animate-pulse" />
                    {id.status.baru}
                </span>
            );
        }
        if (normalizedStatus === 'contacted' || normalizedStatus === 'dihubungi') {
            return (
                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    <span className="w-1.5 h-1.5 rounded-full bg-amber-400 mr-2" />
                    {id.status.dihubungi}
                </span>
            );
        }
        return (
            <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-stone-800/60 text-stone-400 border border-white/10">
                <span className="w-1.5 h-1.5 rounded-full bg-stone-400 mr-2" />
                {id.status.diarsipkan}
            </span>
        );
    };

    return (
        <AdminLayout title={`${id.inquiries.detail_title}: ${inquiry.name}`}>
            {/* Top Navigation & Actions Bar */}
            <div className="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <Link
                    href="/admin/inquiries"
                    className="inline-flex items-center text-xs font-medium uppercase tracking-wider text-stone-400 hover:text-emerald-400 transition-colors"
                >
                    <ArrowLeft className="w-4 h-4 mr-1.5" />
                    {id.inquiries.back_to_list}
                </Link>

                <div className="flex items-center space-x-2">
                    {/* Status Toggle Buttons */}
                    {normalizedStatus !== 'baru' && normalizedStatus !== 'new' && (
                        <button
                            type="button"
                            disabled={updating}
                            onClick={() => handleStatusChange('baru')}
                            className="inline-flex items-center px-3 py-1.5 rounded border border-white/10 bg-white/5 text-xs font-medium text-stone-200 hover:bg-emerald-500/15 hover:text-emerald-300 hover:border-emerald-500/30 transition-colors disabled:opacity-50"
                        >
                            <RotateCcw className="w-3.5 h-3.5 mr-1.5" />
                            {id.inquiries.mark_new}
                        </button>
                    )}

                    {normalizedStatus !== 'dihubungi' && normalizedStatus !== 'contacted' && (
                        <button
                            type="button"
                            disabled={updating}
                            onClick={() => handleStatusChange('dihubungi')}
                            className="inline-flex items-center px-3 py-1.5 rounded border border-amber-500/30 bg-amber-500/15 text-xs font-medium text-amber-300 hover:bg-amber-500/25 transition-colors disabled:opacity-50"
                        >
                            <CheckCircle className="w-3.5 h-3.5 mr-1.5 text-amber-300" />
                            {id.inquiries.mark_contacted}
                        </button>
                    )}

                    {normalizedStatus !== 'diarsipkan' && normalizedStatus !== 'archived' && (
                        <button
                            type="button"
                            disabled={updating}
                            onClick={() => handleStatusChange('diarsipkan')}
                            className="inline-flex items-center px-3 py-1.5 rounded border border-white/10 bg-white/5 text-xs font-medium text-stone-400 hover:bg-white/10 hover:text-stone-200 transition-colors disabled:opacity-50"
                        >
                            <Archive className="w-3.5 h-3.5 mr-1.5" />
                            {id.inquiries.mark_archived}
                        </button>
                    )}

                    <button
                        type="button"
                        onClick={handleDelete}
                        className="inline-flex items-center px-3 py-1.5 rounded border border-rose-500/30 bg-rose-950/40 text-xs font-medium text-rose-300 hover:bg-rose-900/40 transition-colors"
                    >
                        <Trash2 className="w-3.5 h-3.5 mr-1.5" />
                        {id.inquiries.delete_inquiry}
                    </button>
                </div>
            </div>

            {/* Main Detail Header Card */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_24px_rgba(0,0,0,0.35),inset_0_1px_0_rgba(255,255,255,0.08)] rounded p-6 sm:p-8 mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-white/10">
                    <div>
                        <div className="flex items-center space-x-3 mb-2">
                            <h1 className="font-serif text-3xl sm:text-4xl text-stone-100 tracking-tight">
                                {inquiry.name}
                            </h1>
                            {getStatusBadge()}
                        </div>
                        <div className="flex items-center text-xs text-stone-400 space-x-2">
                            <Clock className="w-3.5 h-3.5" />
                            <span>Diterima pada {formatDate(inquiry.created_at)}</span>
                        </div>
                    </div>
                </div>

                {/* 2-Column Info Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 py-6 border-b border-white/10">
                    {/* Client Contact Info */}
                    <div>
                        <h3 className="text-xs uppercase tracking-wider font-semibold text-emerald-300 mb-4 flex items-center">
                            <Mail className="w-3.5 h-3.5 mr-1.5" />
                            {id.inquiries.client_info}
                        </h3>
                        <dl className="space-y-3 text-xs sm:text-sm">
                            <div>
                                <dt className="text-stone-400 text-[11px] uppercase tracking-wider font-medium">
                                    {id.inquiries.table.client}
                                </dt>
                                <dd className="font-medium text-stone-100 mt-0.5">{inquiry.name}</dd>
                            </div>
                            <div>
                                <dt className="text-stone-400 text-[11px] uppercase tracking-wider font-medium">
                                    {id.inquiries.table.email}
                                </dt>
                                <dd className="mt-0.5">
                                    <a
                                        href={`mailto:${inquiry.email}`}
                                        className="text-emerald-400 hover:underline font-medium inline-flex items-center"
                                    >
                                        <Mail className="w-3.5 h-3.5 mr-1 text-stone-400" />
                                        {inquiry.email}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-stone-400 text-[11px] uppercase tracking-wider font-medium">
                                    {id.inquiries.table.phone}
                                </dt>
                                <dd className="mt-0.5">
                                    {inquiry.phone ? (
                                        <a
                                            href={`tel:${inquiry.phone}`}
                                            className="text-emerald-400 hover:underline font-medium inline-flex items-center"
                                        >
                                            <Phone className="w-3.5 h-3.5 mr-1 text-stone-400" />
                                            {inquiry.phone}
                                        </a>
                                    ) : (
                                        <span className="text-stone-400">-</span>
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {/* Project Scope Info */}
                    <div>
                        <h3 className="text-xs uppercase tracking-wider font-semibold text-emerald-300 mb-4 flex items-center">
                            <MapPin className="w-3.5 h-3.5 mr-1.5" />
                            {id.inquiries.project_info}
                        </h3>
                        <dl className="space-y-3 text-xs sm:text-sm">
                            <div>
                                <dt className="text-stone-400 text-[11px] uppercase tracking-wider font-medium">
                                    {id.inquiries.table.location}
                                </dt>
                                <dd className="font-medium text-stone-100 mt-0.5 flex items-center">
                                    <MapPin className="w-3.5 h-3.5 mr-1 text-stone-400" />
                                    {inquiry.site_location || '-'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-stone-400 text-[11px] uppercase tracking-wider font-medium">
                                    {id.inquiries.table.area}
                                </dt>
                                <dd className="font-medium text-stone-100 mt-0.5 flex items-center">
                                    <Maximize2 className="w-3.5 h-3.5 mr-1 text-stone-400" />
                                    {inquiry.estimated_area || '-'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-stone-400 text-[11px] uppercase tracking-wider font-medium">
                                    {id.inquiries.table.budget}
                                </dt>
                                <dd className="font-medium text-stone-100 mt-0.5 flex items-center">
                                    <DollarSign className="w-3.5 h-3.5 mr-1 text-stone-400" />
                                    {inquiry.budget_range || '-'}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {/* Consultation Message Block */}
                <div className="pt-6">
                    <h3 className="text-xs uppercase tracking-wider font-semibold text-emerald-300 mb-3">
                        {id.inquiries.message_label}
                    </h3>
                    <div className="bg-[#0E1310]/80 backdrop-blur-xs border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] p-5 sm:p-6 text-sm text-stone-200 leading-relaxed whitespace-pre-wrap rounded">
                        {inquiry.message}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
