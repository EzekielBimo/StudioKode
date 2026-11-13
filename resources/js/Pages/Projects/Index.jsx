import React, { useState, useRef, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import FilterBar from '../../Components/Portfolio/FilterBar';
import InView from '../../Components/UI/InView';

export default function ProjectsIndex({
    projects = [],
    filters = { search: '', sort: 'newest' },
    totalCount = 0,
}) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [sortOption, setSortOption] = useState(filters.sort || 'newest');
    const searchTimeoutRef = useRef(null);

    useEffect(() => {
        setSearchTerm(filters.search || '');
    }, [filters.search]);

    useEffect(() => {
        setSortOption(filters.sort || 'newest');
    }, [filters.sort]);

    useEffect(() => {
        return () => {
            if (searchTimeoutRef.current) {
                clearTimeout(searchTimeoutRef.current);
            }
        };
    }, []);

    const handleSearchChange = (val) => {
        setSearchTerm(val);
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }
        searchTimeoutRef.current = setTimeout(() => {
            router.get(
                '/projects',
                { search: val, sort: sortOption },
                { preserveState: true, replace: true }
            );
        }, 300);
    };

    const handleSortChange = (val) => {
        setSortOption(val);
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }
        router.get(
            '/projects',
            { search: searchTerm, sort: val },
            { preserveState: true, replace: true }
        );
    };

    return (
        <PublicLayout>
            <Head title="Architectural Sanctuaries Portfolio" />

            <div className="max-w-7xl mx-auto px-6 py-16">
                <InView className="max-w-2xl mb-12">
                    <span className="text-xs uppercase tracking-widest text-[#88B896] font-semibold">
                        Our Portfolio
                    </span>
                    <h1 className="font-serif text-4xl sm:text-5xl text-[#F4F2ED] mt-2 tracking-tight">
                        Botanical Sanctuaries &amp; Living Architecture
                    </h1>
                    <p className="mt-4 text-base text-[#C4C9C3]/85 font-light leading-relaxed">
                        Each garden is conceived as a long-term ecological intervention, responding to microclimates, geological substrates, and contemplative spatial flow.
                    </p>
                </InView>

                <FilterBar
                    search={searchTerm}
                    sort={sortOption}
                    onSearchChange={handleSearchChange}
                    onSortChange={handleSortChange}
                    totalCount={totalCount}
                    filteredCount={projects.length}
                />

                <div className="mt-12">
                    {projects.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {projects.map((project, idx) => (
                                <ProjectCard
                                    key={project.id}
                                    project={project}
                                    delay={idx * 0.08}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-24 border border-dashed border-white/15 bg-[rgba(20,32,24,0.40)] backdrop-blur-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                            <p className="font-serif text-xl text-[#F4F2ED]">
                                No sanctuaries found matching your criteria.
                            </p>
                            <button
                                onClick={() => {
                                    if (searchTimeoutRef.current) {
                                        clearTimeout(searchTimeoutRef.current);
                                    }
                                    setSearchTerm('');
                                    setSortOption('newest');
                                    router.get('/projects', { search: '', sort: 'newest' });
                                }}
                                className="mt-4 text-xs uppercase tracking-widest font-semibold text-[#88B896] hover:text-[#A9D6B3] underline hover:no-underline transition-colors"
                            >
                                Reset Search &amp; Filters
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
