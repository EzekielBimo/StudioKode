import React from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';
import {
    Layers,
    Sparkles,
    Inbox,
    MessageSquare,
    ArrowUpRight,
    Calendar,
    MapPin,
    Eye,
} from 'lucide-react';

export default function DashboardIndex({ metrics = {}, recent_projects = [], recent_inquiries = [] }) {
    const kpiCards = [
        {
            label: id.dashboard.metrics.total_projects,
            value: metrics.total_projects ?? 0,
            icon: Layers,
            href: '/admin/projects',
            accent: 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30',
        },
        {
            label: id.dashboard.metrics.featured_projects,
            value: metrics.featured_projects ?? 0,
            icon: Sparkles,
            href: '/admin/projects',
            accent: 'bg-amber-500/15 text-amber-300 border border-amber-500/30',
        },
        {
            label: id.dashboard.metrics.new_inquiries,
            value: metrics.new_inquiries ?? 0,
            icon: Inbox,
            href: '/admin/inquiries?status=baru',
            accent: 'bg-teal-500/15 text-teal-300 border border-teal-500/30',
        },
        {
            label: id.dashboard.metrics.total_inquiries,
            value: metrics.total_inquiries ?? 0,
            icon: MessageSquare,
            href: '/admin/inquiries',
            accent: 'bg-white/5 text-stone-300 border border-white/10',
        },
    ];

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

    return (
        <AdminLayout title={id.dashboard.title}>
            {/* Page Header */}
            <div className="mb-8">
                <h1 className="font-serif text-3xl sm:text-4xl tracking-tight text-stone-100">
                    {id.dashboard.title}
                </h1>
                <p className="text-xs sm:text-sm text-stone-400 mt-1.5 max-w-2xl">
                    {id.dashboard.subtitle}
                </p>
            </div>

            {/* KPI Metrics Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-10">
                {kpiCards.map((kpi, idx) => {
                    const Icon = kpi.icon;
                    return (
                        <div
                            key={idx}
                            className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] hover:border-emerald-500/30 transition-all rounded relative overflow-hidden group"
                        >
                            <div className="flex items-center justify-between mb-4">
                                <span className="text-[11px] uppercase tracking-wider font-semibold text-stone-400">
                                    {kpi.label}
                                </span>
                                <div className={`p-2 rounded-md ${kpi.accent}`}>
                                    <Icon className="w-4 h-4" />
                                </div>
                            </div>
                            <div className="flex items-baseline justify-between">
                                <span className="font-serif text-3xl sm:text-4xl text-stone-100 font-semibold tracking-tight">
                                    {kpi.value}
                                </span>
                                <Link
                                    href={kpi.href}
                                    className="text-xs font-medium text-emerald-400 hover:text-emerald-300 inline-flex items-center group-hover:translate-x-0.5 transition-transform"
                                >
                                    <ArrowUpRight className="w-3.5 h-3.5" />
                                </Link>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Two Column Grid for Recent Activity */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Recent Inquiries Card */}
                <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded flex flex-col justify-between overflow-hidden">
                    <div>
                        <div className="p-5 sm:p-6 border-b border-white/10 flex items-center justify-between">
                            <div>
                                <h2 className="font-serif text-xl tracking-tight text-stone-100">
                                    {id.dashboard.recent_inquiries}
                                </h2>
                                <p className="text-[11px] text-stone-400 mt-0.5">
                                    5 pesan konsultasi terbaru dari calon klien
                                </p>
                            </div>
                            <Link
                                href="/admin/inquiries"
                                className="text-xs font-medium text-emerald-400 hover:text-emerald-300 inline-flex items-center transition-colors"
                            >
                                {id.dashboard.view_all_inquiries}
                                <ArrowUpRight className="w-3.5 h-3.5 ml-1" />
                            </Link>
                        </div>

                        {recent_inquiries.length === 0 ? (
                            <div className="p-8 text-center text-stone-500 text-xs sm:text-sm">
                                {id.dashboard.no_recent_inquiries}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="bg-[#0E1310]/70 border-b border-white/10 text-stone-400 uppercase text-[10px] tracking-wider">
                                        <tr>
                                            <th className="py-3 px-4 sm:px-6">{id.dashboard.inquiry_columns.client}</th>
                                            <th className="py-3 px-3">{id.dashboard.inquiry_columns.location}</th>
                                            <th className="py-3 px-3">{id.dashboard.inquiry_columns.status}</th>
                                            <th className="py-3 px-4 sm:px-6 text-right">{id.dashboard.inquiry_columns.actions}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-white/5">
                                        {recent_inquiries.map((inquiry) => (
                                             <tr key={inquiry.id} className="hover:bg-white/[0.03] transition-colors">
                                                <td className="py-3.5 px-4 sm:px-6">
                                                    <p className="font-medium text-stone-100">{inquiry.name}</p>
                                                    <p className="text-[11px] text-stone-400">{inquiry.email}</p>
                                                </td>
                                                <td className="py-3.5 px-3 text-stone-400">
                                                    <div className="flex items-center space-x-1">
                                                        <MapPin className="w-3 h-3 text-stone-500 shrink-0" />
                                                        <span className="truncate max-w-[120px]">
                                                            {inquiry.site_location || '-'}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3.5 px-3">
                                                    {getInquiryBadge(inquiry.status)}
                                                </td>
                                                <td className="py-3.5 px-4 sm:px-6 text-right">
                                                    <Link
                                                        href={`/admin/inquiries/${inquiry.id}`}
                                                        className="inline-flex items-center text-emerald-400 hover:text-emerald-300 font-medium transition-colors"
                                                        title={id.common.view_detail}
                                                    >
                                                        <Eye className="w-3.5 h-3.5 mr-1" />
                                                        <span>{id.common.view_detail}</span>
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>

                {/* Recent Projects Card */}
                <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded flex flex-col justify-between overflow-hidden">
                    <div>
                        <div className="p-5 sm:p-6 border-b border-white/10 flex items-center justify-between">
                            <div>
                                <h2 className="font-serif text-xl tracking-tight text-stone-100">
                                    {id.dashboard.recent_projects}
                                </h2>
                                <p className="text-[11px] text-stone-400 mt-0.5">
                                    5 proyek portofolio arsitektur terbaru
                                </p>
                            </div>
                            <Link
                                href="/admin/projects"
                                className="text-xs font-medium text-emerald-400 hover:text-emerald-300 inline-flex items-center transition-colors"
                            >
                                {id.dashboard.view_all_projects}
                                <ArrowUpRight className="w-3.5 h-3.5 ml-1" />
                            </Link>
                        </div>

                        {recent_projects.length === 0 ? (
                            <div className="p-8 text-center text-stone-500 text-xs sm:text-sm">
                                {id.dashboard.no_recent_projects}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="bg-[#0E1310]/70 border-b border-white/10 text-stone-400 uppercase text-[10px] tracking-wider">
                                        <tr>
                                            <th className="py-3 px-4 sm:px-6">{id.dashboard.project_columns.title}</th>
                                            <th className="py-3 px-3">{id.dashboard.project_columns.location}</th>
                                            <th className="py-3 px-3">{id.dashboard.project_columns.status}</th>
                                            <th className="py-3 px-4 sm:px-6 text-right">{id.dashboard.project_columns.date}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-white/5">
                                        {recent_projects.map((project) => (
                                            <tr key={project.id} className="hover:bg-white/[0.03] transition-colors">
                                                <td className="py-3.5 px-4 sm:px-6">
                                                    <p className="font-medium text-stone-100">{project.title}</p>
                                                    {project.is_featured && (
                                                        <span className="inline-flex items-center text-[10px] text-amber-300 font-semibold mt-0.5">
                                                            <Sparkles className="w-2.5 h-2.5 mr-1" />
                                                            {id.status.unggulan}
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="py-3.5 px-3 text-stone-400">
                                                    <div className="flex items-center space-x-1">
                                                        <MapPin className="w-3 h-3 text-stone-500 shrink-0" />
                                                        <span className="truncate max-w-[120px]">
                                                            {project.location || '-'}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3.5 px-3">
                                                    {project.published_at ? (
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                            {id.status.terbit}
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-stone-800/60 text-stone-400 border border-white/10">
                                                            {id.status.draf}
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="py-3.5 px-4 sm:px-6 text-right text-stone-400">
                                                    <div className="inline-flex items-center space-x-1">
                                                        <Calendar className="w-3 h-3 text-stone-500" />
                                                        <span>{formatDate(project.created_at)}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
