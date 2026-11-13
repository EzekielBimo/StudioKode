import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import InView from '../../Components/UI/InView';
import Input from '../../Components/UI/Input';
import Button from '../../Components/UI/Button';
import { MapPin, Mail, Phone, Clock, Send } from 'lucide-react';

export default function ContactIndex({ studios = [], consultationDetails = {} }) {
    const { data, setData, post, processing, errors, recentlySuccessful, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        site_location: '',
        estimated_area: '',
        budget_range: '',
        message: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/inquiries', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <PublicLayout>
            <Head title="Initiate a Consultation — Atelier Locations & Inquiries" />

            <div className="max-w-7xl mx-auto px-6 py-20">
                <InView className="max-w-3xl mb-16">
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        Initiate Commission
                    </span>
                    <h1 className="font-serif text-4xl sm:text-6xl text-[#F4F2ED] mt-2 tracking-tight">
                        Consultation &amp; Atelier Inquiries
                    </h1>
                    <p className="mt-4 text-base text-[#C4C9C3]/90 font-light leading-relaxed">
                        We undertake a limited number of residential sanctuaries and botanical masterplans each season to ensure uncompromising artisanal oversight.
                    </p>
                </InView>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-16">
                    {/* Inquiry Form */}
                    <div className="lg:col-span-7 bg-[rgba(20,32,24,0.65)] backdrop-blur-md border border-white/10 p-8 sm:p-12 shadow-2xl shadow-black/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]">
                        <InView>
                            <h2 className="font-serif text-2xl text-[#F4F2ED] mb-2">Project Brief &amp; Site Assessment</h2>
                            <p className="text-xs text-[#A8B0A6]/70 font-light mb-8">
                                Please provide essential contextual details regarding your property, scale, and architectural aspirations.
                            </p>

                            {recentlySuccessful && (
                                <div className="mb-6 p-4 bg-[#1C2A21] border border-emerald-500/30 text-[#88B896] text-xs uppercase tracking-wider font-medium shadow-inner">
                                    Thank you. Your inquiry has been received. Our principal architects will review your project details.
                                </div>
                            )}

                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <Input
                                        label="Principal Name"
                                        name="name"
                                        type="text"
                                        required
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        error={errors.name}
                                        placeholder="e.g. Julian Finch"
                                    />
                                    <Input
                                        label="Email Address"
                                        name="email"
                                        type="email"
                                        required
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        error={errors.email}
                                        placeholder="e.g. julian@estate.com"
                                    />
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <Input
                                        label="Contact Telephone"
                                        name="phone"
                                        type="tel"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        error={errors.phone}
                                        placeholder="+41 22 819 4022"
                                    />
                                    <Input
                                        label="Site Location / Region"
                                        name="site_location"
                                        type="text"
                                        value={data.site_location}
                                        onChange={(e) => setData('site_location', e.target.value)}
                                        error={errors.site_location}
                                        placeholder="Kyoto, Lake Como, Zurich..."
                                    />
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <Input
                                        label="Estimated Grounds Area"
                                        name="estimated_area"
                                        type="text"
                                        value={data.estimated_area}
                                        onChange={(e) => setData('estimated_area', e.target.value)}
                                        error={errors.estimated_area}
                                        placeholder="e.g. 1,250 m²"
                                    />
                                    <Input
                                        label="Anticipated Budget Framework"
                                        name="budget_range"
                                        type="text"
                                        value={data.budget_range}
                                        onChange={(e) => setData('budget_range', e.target.value)}
                                        error={errors.budget_range}
                                        placeholder="e.g. $150,000 – $300,000"
                                    />
                                </div>

                                <div>
                                    <Input
                                        label="Architectural Intent & Vision"
                                        name="message"
                                        multiline
                                        rows={5}
                                        required
                                        value={data.message}
                                        onChange={(e) => setData('message', e.target.value)}
                                        error={errors.message}
                                        placeholder="Describe your site conditions, existing vegetation, architectural styles, water ambitions, and project timeframe..."
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    size="lg"
                                    disabled={processing}
                                    className="w-full sm:w-auto"
                                >
                                    <Send className="w-4 h-4 mr-2" />
                                    {processing ? 'Submitting Brief...' : 'Transmit Consultation Brief'}
                                </Button>
                            </form>
                        </InView>
                    </div>

                    {/* Atelier Directory & Logistics */}
                    <div className="lg:col-span-5 space-y-12">
                        <InView className="space-y-8">
                            <h2 className="font-serif text-2xl text-[#F4F2ED]">Studio Ateliers</h2>
                            {studios.map((atelier, i) => (
                                <div key={i} className="border-t border-white/10 pt-6 space-y-2 text-sm text-[#F4F2ED]">
                                    <h3 className="font-serif text-xl font-medium text-[#F4F2ED]">{atelier.city}</h3>
                                    <div className="flex items-start text-xs text-[#C4C9C3]/80 font-light">
                                        <MapPin className="w-3.5 h-3.5 mr-2 shrink-0 mt-0.5 text-[#88B896]" />
                                        <span>{atelier.address}</span>
                                    </div>
                                    <div className="flex items-center text-xs text-[#C4C9C3]/80 font-light">
                                        <Mail className="w-3.5 h-3.5 mr-2 shrink-0 text-[#88B896]" />
                                        <a href={`mailto:${atelier.email}`} className="hover:underline">{atelier.email}</a>
                                    </div>
                                    <div className="flex items-center text-xs text-[#C4C9C3]/80 font-light">
                                        <Phone className="w-3.5 h-3.5 mr-2 shrink-0 text-[#88B896]" />
                                        <span>{atelier.phone}</span>
                                    </div>
                                    <div className="flex items-center text-xs text-[#A8B0A6]/70 font-light">
                                        <Clock className="w-3.5 h-3.5 mr-2 shrink-0 text-[#88B896]" />
                                        <span>{atelier.hours}</span>
                                    </div>
                                </div>
                            ))}
                        </InView>

                        {/* Consultation Guidelines */}
                        <InView delay={0.1} className="bg-[rgba(20,32,24,0.45)] backdrop-blur-md border border-white/10 p-6 space-y-3 shadow-lg shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                            <h3 className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                                Commission Timeline
                            </h3>
                            <p className="text-xs text-[#C4C9C3]/80 font-light leading-relaxed">
                                {consultationDetails.leadTime}
                            </p>
                            <p className="text-xs text-[#C4C9C3]/80 font-light leading-relaxed">
                                {consultationDetails.minimumScale}
                            </p>
                        </InView>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
