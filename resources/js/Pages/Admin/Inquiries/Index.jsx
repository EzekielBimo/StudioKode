import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';
import {
    Search,
    X,
    Eye,
    Trash2,
    Calendar,
    MapPin,
    Mail,
    Phone,
    Inbox,
} from 'lucide-react';

export default function InquiriesIndex({ inquiries, filters = {}, status_counts = {} }) {
    const [search, setSearch] = useState(filters.search || '');

    // Normalize inquiries list whether paginated object or array
    const inquiryList = Array.isArray(inquiries) ? inquiries : (inquiries?.data || []);
    const paginationLinks = inquiries?.links || [];

    const currentStatus = filters.status || 'all';

    const handleTabChange = (statusKey) => {
        const query = {};
        if (statusKey && statusKey !== 'all') {
            query.status = statusKey;
        }
        if (search) {
            query.search = search;
        }
        router.get('/admin/inquiries', query, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSearch = (e) => {
        e.preventDefault();
        const query = {};
        if (currentStatus && currentStatus !== 'all') {
            query.status = currentStatus;
        }
        if (search.trim()) {
            query.search = search.trim();
        }
        router.get('/admin/inquiries', query, {
            preserveState: true,
            replace: true,
        });
    };

    const handleClearSearch = () => {
        setSearch('');
        const query = {};
        if (currentStatus && currentStatus !== 'all') {
            query.status = currentStatus;
        }
        router.get('/admin/inquiries', query, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (inquiry) => {
        if (window.confirm(id.inquiries.delete_confirm || 'Apakah Anda yakin ingin menghapus pesan ini?')) {
            router.delete(`/admin/inquiries/${inquiry.id}`);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        try {
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }).format(date);
        } catch {
            return dateString;
        }
    };

    const getInquiryBadge = (status) => {
        const normalized = (status || '').toLowerCase();
        if (normalized === 'new' || normalized === 'baru') {
            return (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    {id.status.baru}
                </span>
            );
        }
        if (normalized === 'contacted' || normalized === 'dihubungi') {
            return (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    {id.status.dihubungi}
                </span>
            );
        }
        return (
            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-stone-800/60 text-stone-400 border border-white/10">
                {id.status.diarsipkan}
            </span>
        );
    };

    const tabs = [
        { key: 'all', label: id.inquiries.tabs.all, count: status_counts.all ?? 0 },
        { key: 'baru', label: id.inquiries.tabs.baru, count: status_counts.baru ?? 0 },
        { key: 'dihubungi', label: id.inquiries.tabs.dihubungi, count: status_counts.dihubungi ?? 0 },
        { key: 'diarsipkan', label: id.inquiries.tabs.diarsipkan, count: status_counts.diarsipkan ?? 0 },
    ];

    return (
        <AdminLayout title={id.inquiries.title}>
            {/* Header */}
            <div className="mb-8">
                <h1 className="font-serif text-3xl sm:text-4xl tracking-tight text-stone-100">
                    {id.inquiries.title}
                </h1>
                <p className="text-xs sm:text-sm text-stone-400 mt-1.5">
                    {id.inquiries.subtitle}
                </p>
            </div>

            {/* Filter Tabs and Search Toolbar */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded mb-6 p-4 sm:p-5">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    {/* Status Tabs */}
                    <div className="flex items-center space-x-1 sm:space-x-2 overflow-x-auto pb-1 md:pb-0">
                        {tabs.map((tab) => {
                            const isActive =
                                currentStatus === tab.key ||
                                (tab.key === 'baru' && currentStatus === 'new') ||
                                (tab.key === 'dihubungi' && currentStatus === 'contacted') ||
                                (tab.key === 'diarsipkan' && currentStatus === 'archived');

                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => handleTabChange(tab.key)}
                                    className={`px-3 py-1.5 rounded text-xs font-medium transition-colors flex items-center space-x-1.5 shrink-0 ${
                                        isActive
                                            ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.15)]'
                                            : 'text-stone-400 hover:text-stone-200 hover:bg-white/5'
                                    }`}
                                >
                                    <span>{tab.label}</span>
                                    <span
                                        className={`px-1.5 py-0.2 rounded-full text-[10px] font-semibold ${
                                            isActive
                                                ? 'bg-white/10 text-emerald-300'
                                                : 'bg-white/5 text-stone-400'
                                        }`}
                                    >
                                        {tab.count}
                                    </span>
                                </button>
                            );
                        })}
                    </div>

                    {/* Search Bar */}
                    <form onSubmit={handleSearch} className="relative flex items-center w-full md:w-80">
                        <div className="relative w-full">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder={id.inquiries.search_placeholder}
                                className="w-full pl-9 pr-8 py-2 text-xs bg-[#0E1310]/80 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] border border-white/10 rounded focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 text-stone-100 placeholder:text-stone-500 transition-colors"
                            />
                            <Search className="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            {search && (
                                <button
                                    type="button"
                                    onClick={handleClearSearch}
                                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-100"
                                >
                                    <X className="w-3.5 h-3.5" />
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            </div>

            {/* Inquiries Table Card */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded overflow-hidden">
                {inquiryList.length === 0 ? (
                    <div className="py-16 px-4 text-center">
                        <div className="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-3 text-stone-400">
                            <Inbox className="w-6 h-6" />
                        </div>
                        <h3 className="font-serif text-lg text-stone-100 mb-1">
                            {id.inquiries.empty_filtered}
                        </h3>
                        <p className="text-xs text-stone-400">
                            Coba ubah kata kunci pencarian atau pilih tab status lain.
                        </p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-[#0E1310]/70 border-b border-white/10 text-stone-400 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th className="py-3 px-4 sm:px-6">{id.inquiries.table.date}</th>
                                    <th className="py-3 px-4 sm:px-6">{id.inquiries.table.client}</th>
                                    <th className="py-3 px-4">{id.inquiries.table.location}</th>
                                    <th className="py-3 px-4">{id.inquiries.table.area} / {id.inquiries.table.budget}</th>
                                    <th className="py-3 px-4">{id.inquiries.table.status}</th>
                                    <th className="py-3 px-4 sm:px-6 text-right">{id.inquiries.table.actions}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {inquiryList.map((inquiry) => (
                                    <tr key={inquiry.id} className="hover:bg-white/[0.03] transition-colors">
                                        <td className="py-4 px-4 sm:px-6 text-stone-400 whitespace-nowrap">
                                            <div className="inline-flex items-center space-x-1.5">
                                                <Calendar className="w-3.5 h-3.5 text-stone-500" />
                                                <span>{formatDate(inquiry.created_at)}</span>
                                            </div>
                                        </td>
                                        <td className="py-4 px-4 sm:px-6">
                                            <p className="font-medium text-stone-100 text-sm">{inquiry.name}</p>
                                            <div className="flex flex-col sm:flex-row sm:items-center sm:space-x-3 text-[11px] text-stone-400 mt-0.5">
                                                <a
                                                    href={`mailto:${inquiry.email}`}
                                                    className="inline-flex items-center hover:text-emerald-400 underline-offset-2 hover:underline transition-colors"
                                                >
                                                    <Mail className="w-3 h-3 mr-1 opacity-70" />
                                                    {inquiry.email}
                                                </a>
                                                {inquiry.phone && (
                                                    <a
                                                        href={`tel:${inquiry.phone}`}
                                                        className="inline-flex items-center hover:text-emerald-400 underline-offset-2 hover:underline transition-colors"
                                                    >
                                                        <Phone className="w-3 h-3 mr-1 opacity-70" />
                                                        {inquiry.phone}
                                                    </a>
                                                )}
                                            </div>
                                        </td>
                                        <td className="py-4 px-4 text-stone-400">
                                            <div className="flex items-center space-x-1.5">
                                                <MapPin className="w-3.5 h-3.5 text-stone-500 shrink-0" />
                                                <span className="truncate max-w-[150px]">
                                                    {inquiry.site_location || '-'}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="py-4 px-4 text-stone-400">
                                            <p className="font-medium text-stone-200">{inquiry.estimated_area || '-'}</p>
                                            <p className="text-[11px] text-stone-400">{inquiry.budget_range || '-'}</p>
                                        </td>
                                        <td className="py-4 px-4 whitespace-nowrap">
                                            {getInquiryBadge(inquiry.status)}
                                        </td>
                                        <td className="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                            <div className="inline-flex items-center space-x-2">
                                                <Link
                                                    href={`/admin/inquiries/${inquiry.id}`}
                                                    className="inline-flex items-center px-2.5 py-1.5 rounded border border-white/10 text-xs font-medium text-emerald-400 hover:bg-emerald-500/10 hover:border-emerald-500/30 transition-colors"
                                                    title={id.common.view_detail}
                                                >
                                                    <Eye className="w-3.5 h-3.5 mr-1" />
                                                    {id.common.view_detail}
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(inquiry)}
                                                    className="p-1.5 rounded border border-rose-500/30 text-rose-400 hover:bg-rose-500/10 transition-colors"
                                                    title={id.common.delete}
                                                >
                                                    <Trash2 className="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination Controls */}
                {paginationLinks && paginationLinks.length > 3 && (
                    <div className="p-4 sm:p-5 border-t border-white/10 flex items-center justify-between">
                        <div className="flex items-center space-x-1 mx-auto sm:mx-0">
                            {paginationLinks.map((link, idx) => (
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
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
