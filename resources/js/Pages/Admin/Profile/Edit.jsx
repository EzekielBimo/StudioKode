import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { User, Lock, CheckCircle2, AlertCircle, Eye, EyeOff, ShieldCheck } from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { id } from '../../../lib/id';

export default function Edit({ user }) {
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    // Profile Information Form
    const {
        data: profileData,
        setData: setProfileData,
        put: putProfile,
        processing: profileProcessing,
        errors: profileErrors,
        recentlySuccessful: profileSuccess,
    } = useForm({
        name: user?.name || '',
        email: user?.email || '',
    });

    // Password Update Form
    const {
        data: passwordData,
        setData: setPasswordData,
        put: putPassword,
        processing: passwordProcessing,
        errors: passwordErrors,
        reset: resetPassword,
        recentlySuccessful: passwordSuccess,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitProfile = (e) => {
        e.preventDefault();
        putProfile('/admin/profile', {
            preserveScroll: true,
        });
    };

    const submitPassword = (e) => {
        e.preventDefault();
        putPassword('/admin/profile/password', {
            preserveScroll: true,
            onSuccess: () => resetPassword(),
        });
    };

    return (
        <AdminLayout title={id.profile.title}>
            <Head title={id.profile.title} />

            <div className="space-y-8 max-w-4xl">
                {/* Page Header */}
                <div>
                    <h1 className="font-serif text-3xl sm:text-4xl text-[#F4F6F0] tracking-tight">
                        {id.profile.title}
                    </h1>
                    <p className="mt-2 text-sm text-[#A7ACA0] max-w-2xl leading-relaxed">
                        {id.profile.subtitle}
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-8">
                    {/* Card 1: Informasi Akun Admin */}
                    <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_24px_rgba(0,0,0,0.35),inset_0_1px_0_rgba(255,255,255,0.08)] rounded-2xl p-6 sm:p-8 space-y-6">
                        <div className="flex items-center space-x-3 pb-4 border-b border-white/10">
                            <div className="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/25 flex items-center justify-center text-emerald-400">
                                <User className="w-5 h-5" />
                            </div>
                            <div>
                                <h2 className="font-serif text-xl text-[#F4F6F0]">
                                    {id.profile.info_card_title}
                                </h2>
                                <p className="text-xs text-[#A7ACA0]">
                                    {id.profile.info_card_subtitle}
                                </p>
                            </div>
                        </div>

                        <form onSubmit={submitProfile} className="space-y-5">
                            {/* Nama Lengkap */}
                            <div>
                                <label className="block text-xs uppercase tracking-wider font-medium text-[#C5C8C0] mb-2">
                                    {id.profile.name_label} <span className="text-emerald-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={profileData.name}
                                    onChange={(e) => setProfileData('name', e.target.value)}
                                    placeholder={id.profile.name_placeholder}
                                    className={`w-full px-4 py-3 rounded-xl bg-[#0B0E0C]/70 border text-sm text-[#F4F6F0] placeholder-[#7C8375] focus:outline-none transition-all ${
                                        profileErrors.name
                                            ? 'border-rose-500 focus:border-rose-400 focus:ring-1 focus:ring-rose-400'
                                            : 'border-white/10 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400'
                                    }`}
                                />
                                {profileErrors.name && (
                                    <p className="mt-1.5 text-xs text-rose-400 flex items-center">
                                        <AlertCircle className="w-3.5 h-3.5 mr-1 shrink-0" />
                                        {profileErrors.name}
                                    </p>
                                )}
                            </div>

                            {/* Alamat Email */}
                            <div>
                                <label className="block text-xs uppercase tracking-wider font-medium text-[#C5C8C0] mb-2">
                                    {id.profile.email_label} <span className="text-emerald-400">*</span>
                                </label>
                                <input
                                    type="email"
                                    value={profileData.email}
                                    onChange={(e) => setProfileData('email', e.target.value)}
                                    placeholder={id.profile.email_placeholder}
                                    className={`w-full px-4 py-3 rounded-xl bg-[#0B0E0C]/70 border text-sm text-[#F4F6F0] placeholder-[#7C8375] focus:outline-none transition-all ${
                                        profileErrors.email
                                            ? 'border-rose-500 focus:border-rose-400 focus:ring-1 focus:ring-rose-400'
                                            : 'border-white/10 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400'
                                    }`}
                                />
                                {profileErrors.email && (
                                    <p className="mt-1.5 text-xs text-rose-400 flex items-center">
                                        <AlertCircle className="w-3.5 h-3.5 mr-1 shrink-0" />
                                        {profileErrors.email}
                                    </p>
                                )}
                            </div>

                            {/* Tombol Simpan Profil */}
                            <div className="flex items-center justify-between pt-2">
                                <button
                                    type="submit"
                                    disabled={profileProcessing}
                                    className="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs uppercase tracking-wider font-semibold transition-all shadow-md shadow-emerald-950/40 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {profileProcessing
                                        ? id.profile.saving_profile_button
                                        : id.profile.save_profile_button}
                                </button>

                                {profileSuccess && (
                                    <span className="text-xs text-emerald-400 flex items-center animate-in fade-in">
                                        <CheckCircle2 className="w-4 h-4 mr-1.5" />
                                        {id.flash.saved}
                                    </span>
                                )}
                            </div>
                        </form>
                    </div>

                    {/* Card 2: Perbarui Kata Sandi */}
                    <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_24px_rgba(0,0,0,0.35),inset_0_1px_0_rgba(255,255,255,0.08)] rounded-2xl p-6 sm:p-8 space-y-6">
                        <div className="flex items-center space-x-3 pb-4 border-b border-white/10">
                            <div className="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/25 flex items-center justify-center text-emerald-400">
                                <Lock className="w-5 h-5" />
                            </div>
                            <div>
                                <h2 className="font-serif text-xl text-[#F4F6F0]">
                                    {id.profile.password_card_title}
                                </h2>
                                <p className="text-xs text-[#A7ACA0]">
                                    {id.profile.password_card_subtitle}
                                </p>
                            </div>
                        </div>

                        <form onSubmit={submitPassword} className="space-y-5">
                            {/* Kata Sandi Saat Ini */}
                            <div>
                                <label className="block text-xs uppercase tracking-wider font-medium text-[#C5C8C0] mb-2">
                                    {id.profile.current_password_label} <span className="text-emerald-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type={showCurrentPassword ? 'text' : 'password'}
                                        value={passwordData.current_password}
                                        onChange={(e) => setPasswordData('current_password', e.target.value)}
                                        placeholder={id.profile.current_password_placeholder}
                                        className={`w-full px-4 py-3 pr-12 rounded-xl bg-[#0B0E0C]/70 border text-sm text-[#F4F6F0] placeholder-[#7C8375] focus:outline-none transition-all ${
                                            passwordErrors.current_password
                                                ? 'border-rose-500 focus:border-rose-400 focus:ring-1 focus:ring-rose-400'
                                                : 'border-white/10 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400'
                                        }`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-[#7C8375] hover:text-[#C5C8C0] transition-colors"
                                        aria-label="Lihat kata sandi"
                                    >
                                        {showCurrentPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                                {passwordErrors.current_password && (
                                    <p className="mt-1.5 text-xs text-rose-400 flex items-center">
                                        <AlertCircle className="w-3.5 h-3.5 mr-1 shrink-0" />
                                        {passwordErrors.current_password}
                                    </p>
                                )}
                            </div>

                            {/* Kata Sandi Baru */}
                            <div>
                                <label className="block text-xs uppercase tracking-wider font-medium text-[#C5C8C0] mb-2">
                                    {id.profile.new_password_label} <span className="text-emerald-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type={showNewPassword ? 'text' : 'password'}
                                        value={passwordData.password}
                                        onChange={(e) => setPasswordData('password', e.target.value)}
                                        placeholder={id.profile.new_password_placeholder}
                                        className={`w-full px-4 py-3 pr-12 rounded-xl bg-[#0B0E0C]/70 border text-sm text-[#F4F6F0] placeholder-[#7C8375] focus:outline-none transition-all ${
                                            passwordErrors.password
                                                ? 'border-rose-500 focus:border-rose-400 focus:ring-1 focus:ring-rose-400'
                                                : 'border-white/10 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400'
                                        }`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowNewPassword(!showNewPassword)}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-[#7C8375] hover:text-[#C5C8C0] transition-colors"
                                        aria-label="Lihat kata sandi"
                                    >
                                        {showNewPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                                {passwordErrors.password && (
                                    <p className="mt-1.5 text-xs text-rose-400 flex items-center">
                                        <AlertCircle className="w-3.5 h-3.5 mr-1 shrink-0" />
                                        {passwordErrors.password}
                                    </p>
                                )}
                            </div>

                            {/* Konfirmasi Kata Sandi Baru */}
                            <div>
                                <label className="block text-xs uppercase tracking-wider font-medium text-[#C5C8C0] mb-2">
                                    {id.profile.confirm_password_label} <span className="text-emerald-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type={showConfirmPassword ? 'text' : 'password'}
                                        value={passwordData.password_confirmation}
                                        onChange={(e) => setPasswordData('password_confirmation', e.target.value)}
                                        placeholder={id.profile.confirm_password_placeholder}
                                        className="w-full px-4 py-3 pr-12 rounded-xl bg-[#0B0E0C]/70 border border-white/10 text-sm text-[#F4F6F0] placeholder-[#7C8375] focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 focus:outline-none transition-all"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-[#7C8375] hover:text-[#C5C8C0] transition-colors"
                                        aria-label="Lihat kata sandi"
                                    >
                                        {showConfirmPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                            </div>

                            {/* Tombol Simpan Kata Sandi */}
                            <div className="flex items-center justify-between pt-2">
                                <button
                                    type="submit"
                                    disabled={passwordProcessing}
                                    className="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs uppercase tracking-wider font-semibold transition-all shadow-md shadow-emerald-950/40 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {passwordProcessing
                                        ? id.profile.saving_password_button
                                        : id.profile.save_password_button}
                                </button>

                                {passwordSuccess && (
                                    <span className="text-xs text-emerald-400 flex items-center animate-in fade-in">
                                        <CheckCircle2 className="w-4 h-4 mr-1.5" />
                                        {id.flash.updated}
                                    </span>
                                )}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
