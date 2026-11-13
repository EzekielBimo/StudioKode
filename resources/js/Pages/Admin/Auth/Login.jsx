import React, { useState } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Lock, Mail, Eye, EyeOff, AlertCircle, CheckCircle2, ArrowLeft, Shield } from 'lucide-react';
import { id } from '../../../lib/id';

export default function Login() {
    const { flash } = usePage().props;
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        clearErrors();
        post('/admin/login', {
            preserveScroll: true,
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="min-h-screen bg-[#0B0E0C] text-[#F5F2EB] flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8 selection:bg-[#1C2A21] selection:text-[#F9F8F6]">
            <Head title={`${id.auth.title} — ${id.brand.name}`} />

            {/* Back to public site header button */}
            <div className="w-full max-w-md mb-6 flex justify-between items-center">
                <Link
                    href="/"
                    className="inline-flex items-center text-xs uppercase tracking-widest text-stone-400 hover:text-emerald-400 font-medium transition-colors group"
                >
                    <ArrowLeft className="w-3.5 h-3.5 mr-2 transition-transform group-hover:-translate-x-1" />
                    {id.auth.back_to_site}
                </Link>
                <div className="flex items-center space-x-1 text-xs text-stone-400">
                    <Shield className="w-3.5 h-3.5 text-emerald-400" />
                    <span className="text-[10px] uppercase tracking-wider font-semibold">SSL Terproteksi</span>
                </div>
            </div>

            {/* Login Card with Framer Motion Entrance */}
            <motion.div
                initial={{ opacity: 0, y: 16, filter: 'blur(4px)' }}
                animate={{ opacity: 1, y: 0, filter: 'blur(0px)' }}
                transition={{ duration: 0.6, ease: [0.21, 0.47, 0.32, 0.98] }}
                className="w-full max-w-md bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.12)] p-8 sm:p-10 relative rounded"
            >
                {/* Brand Header */}
                <div className="text-center mb-8">
                    <Link href="/" className="inline-block group mb-3">
                        <span className="font-serif text-3xl sm:text-4xl tracking-tight text-stone-100 group-hover:text-emerald-300 transition-colors block">
                            {id.brand.name}
                        </span>
                        <span className="text-[9px] uppercase tracking-[0.25em] text-stone-400 font-medium block mt-0.5">
                            {id.brand.tagline}
                        </span>
                    </Link>
                    <div className="w-12 h-px bg-emerald-500/30 mx-auto my-3" />
                    <h1 className="font-serif text-xl sm:text-2xl text-stone-100 font-medium">
                        {id.auth.heading}
                    </h1>
                    <p className="mt-2 text-xs text-stone-400 leading-relaxed font-light">
                        {id.auth.subtitle}
                    </p>
                </div>

                {/* Session Flash Alert (Error) */}
                {flash?.error && (
                    <motion.div
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="mb-6 p-3.5 bg-rose-950/80 border border-rose-500/30 text-rose-200 text-xs flex items-start gap-2.5 rounded backdrop-blur-md"
                        role="alert"
                    >
                        <AlertCircle className="w-4 h-4 text-rose-400 shrink-0 mt-0.5" />
                        <span className="font-medium">{flash.error}</span>
                    </motion.div>
                )}

                {/* Session Flash Alert (Success / Logged out) */}
                {flash?.success && (
                    <motion.div
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="mb-6 p-3.5 bg-[#142018]/90 border border-emerald-500/30 text-emerald-200 text-xs flex items-start gap-2.5 rounded backdrop-blur-md"
                        role="status"
                    >
                        <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
                        <span className="font-medium">{flash.success}</span>
                    </motion.div>
                )}

                {/* Login Form */}
                <form onSubmit={handleSubmit} className="space-y-5" noValidate>
                    {/* Email Field */}
                    <div>
                        <label
                            htmlFor="email"
                            className="block text-xs uppercase tracking-wider font-medium text-stone-300 mb-1.5"
                        >
                            {id.auth.email_label} <span className="text-rose-400">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                <Mail className="w-4 h-4" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                required
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder={id.auth.email_placeholder}
                                aria-invalid={errors.email ? 'true' : 'false'}
                                aria-describedby={errors.email ? 'email-error' : undefined}
                                className={`w-full pl-10 pr-4 py-2.5 bg-[#0E1310]/80 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] border text-sm text-stone-100 transition-colors placeholder:text-stone-500 focus:outline-none focus:ring-1 rounded ${
                                    errors.email
                                        ? 'border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/50'
                                        : 'border-white/10 focus:border-emerald-500/50 focus:ring-emerald-500/50'
                                }`}
                            />
                        </div>
                        {errors.email && (
                            <p id="email-error" className="mt-1.5 text-xs text-rose-400 font-medium">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    {/* Password Field */}
                    <div>
                        <label
                            htmlFor="password"
                            className="block text-xs uppercase tracking-wider font-medium text-stone-300 mb-1.5"
                        >
                            {id.auth.password_label} <span className="text-rose-400">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                <Lock className="w-4 h-4" />
                            </div>
                            <input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                autoComplete="current-password"
                                required
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder={id.auth.password_placeholder}
                                aria-invalid={errors.password ? 'true' : 'false'}
                                aria-describedby={errors.password ? 'password-error' : undefined}
                                className={`w-full pl-10 pr-10 py-2.5 bg-[#0E1310]/80 backdrop-blur-xs shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] border text-sm text-stone-100 transition-colors placeholder:text-stone-500 focus:outline-none focus:ring-1 rounded ${
                                    errors.password
                                        ? 'border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/50'
                                        : 'border-white/10 focus:border-emerald-500/50 focus:ring-emerald-500/50'
                                }`}
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-stone-400 hover:text-emerald-400 transition-colors focus:outline-none"
                                aria-label={showPassword ? id.auth.toggle_hide_password : id.auth.toggle_show_password}
                            >
                                {showPassword ? (
                                    <EyeOff className="w-4 h-4" />
                                ) : (
                                    <Eye className="w-4 h-4" />
                                )}
                            </button>
                        </div>
                        {errors.password && (
                            <p id="password-error" className="mt-1.5 text-xs text-rose-400 font-medium">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    {/* Remember Me Toggle */}
                    <div className="flex items-center justify-between pt-1">
                        <label htmlFor="remember" className="flex items-center cursor-pointer select-none">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="w-4 h-4 rounded border-white/20 bg-[#0E1310] text-emerald-500 focus:ring-emerald-500 accent-emerald-500 cursor-pointer"
                            />
                            <span className="ml-2.5 text-xs text-stone-300 font-normal">
                                {id.auth.remember_me}
                            </span>
                        </label>
                    </div>

                    {/* Submit Button */}
                    <div className="pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full inline-flex items-center justify-center bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 transition-all duration-300 py-3 text-xs uppercase tracking-widest font-semibold border border-emerald-400/30 shadow-[0_0_20px_rgba(16,185,129,0.25)] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-[#0B0E0C] disabled:opacity-50 disabled:pointer-events-none select-none rounded"
                        >
                            {processing ? (
                                <>
                                    <svg
                                        className="animate-spin -ml-1 mr-2.5 h-4 w-4 text-stone-50"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                    <span>{id.auth.submitting_button}</span>
                                </>
                            ) : (
                                <span>{id.auth.submit_button}</span>
                            )}
                        </button>
                    </div>
                </form>

                {/* Security Footer Notice */}
                <div className="mt-8 pt-6 border-t border-white/10 text-center">
                    <p className="text-[11px] text-stone-400 leading-relaxed font-light">
                        {id.auth.security_notice}
                    </p>
                </div>
            </motion.div>
        </div>
    );
}
