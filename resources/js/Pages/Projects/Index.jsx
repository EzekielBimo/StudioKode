import React, { useState, useRef, useEffect, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import ProjectCard from '../../Components/Portfolio/ProjectCard';
import FilterBar from '../../Components/Portfolio/FilterBar';
import InView from '../../Components/UI/InView';

export default function ProjectsIndex({
    projects = [],
    filters = { search: '', sort: 'newest' },
    activeCategory: propCategory,
    categoryCounts = {},
    totalCount = 0,
}) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [sortOption, setSortOption] = useState(filters.sort || 'newest');
    const searchTimeoutRef = useRef(null);

    // Normalize category slug for active state: 'all' | 'design' | 'construction'
    const normalizeCategory = (cat) => {
        if (!cat || cat === 'all') return 'all';
        const lower = String(cat).toLowerCase().trim();
        if (lower === 'design' || lower === 'project design' || lower === 'project-design') return 'design';
        if (lower === 'construction' || lower === 'project construction' || lower === 'project-construction') return 'construction';
        return lower;
    };

    const activeCategory = useMemo(() => {
        return normalizeCategory(propCategory || filters.category);
    }, [propCategory, filters.category]);

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

    const executeNavigation = (newSearch, newSort, newCategory) => {
        const query = {};
        if (newSearch && newSearch.trim() !== '') {
            query.search = newSearch.trim();
        }
        if (newSort && newSort !== 'newest') {
            query.sort = newSort;
        }
        if (newCategory && newCategory !== 'all') {
            query.category = newCategory;
        }

        router.get('/projects', query, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSearchChange = (val) => {
        setSearchTerm(val);
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }
        searchTimeoutRef.current = setTimeout(() => {
            executeNavigation(val, sortOption, activeCategory);
        }, 300);
    };

    const handleSortChange = (val) => {
        setSortOption(val);
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }
        executeNavigation(searchTerm, val, activeCategory);
    };

    const handleCategoryChange = (catSlug) => {
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }
        executeNavigation(searchTerm, sortOption, catSlug);
    };

    const categoryTabs = [
        {
            id: 'all',
            label: 'All Works',
            count: categoryCounts?.all ?? totalCount,
        },
        {
            id: 'design',
            label: 'Project Design',
            count: categoryCounts?.design,
        },
        {
            id: 'construction',
            label: 'Project Construction',
            count: categoryCounts?.construction,
        },
    ];

    return (
        <PublicLayout>
            <Head title="Architectural Sanctuaries Portfolio" />

            <div className="max-w-7xl mx-auto px-6 py-16 sm:py-20">
                <InView className="max-w-2xl mb-10">
                    <span className="text-xs uppercase tracking-widest text-lime text-[#88B896] font-mono font-semibold">
                        Our Portfolio
                    </span>
                    <h1 className="font-serif text-4xl sm:text-5xl lg:text-6xl text-ivory text-[#F4F2ED] mt-2 tracking-tight">
                        Botanical Sanctuaries &amp; Living Architecture
                    </h1>
                    <p className="mt-4 text-base text-[#EAFFD0]/75 text-[#C4C9C3]/85 font-light leading-relaxed">
                        Each garden is conceived as a long-term ecological intervention, responding to microclimates, geological substrates, and contemplative spatial flow.
                    </p>
                </InView>

                {/* Category Switcher Tabs */}
                <div className="mb-6 flex flex-wrap items-center gap-3">
                    <div
                        role="tablist"
                        aria-label="Filter projects by discipline"
                        className="inline-flex p-1 rounded-full bg-jet-surface/90 bg-[#121814]/90 border border-white/15 backdrop-blur-md shadow-[inset_0_1px_0_rgba(255,255,255,0.06),0_4px_16px_rgba(0,0,0,0.3)] max-w-full overflow-x-auto no-scrollbar scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden overscroll-x-contain"
                    >
                        {categoryTabs.map((tab) => {
                            const isSelected = activeCategory === tab.id;
                            return (
                                <button
                                    key={tab.id}
                                    role="tab"
                                    type="button"
                                    aria-selected={isSelected}
                                    onClick={() => handleCategoryChange(tab.id)}
                                    className={`relative px-4 py-2 rounded-full text-xs uppercase tracking-widest transition-all duration-200 whitespace-nowrap focus:outline-none flex items-center gap-2 cursor-pointer shrink-0 ${
                                        isSelected
                                            ? 'bg-forest bg-[#1C3024] text-lime text-[#F4F6F0] border border-lime/40 border-emerald-500/40 shadow-[0_0_12px_rgba(176,228,84,0.18),inset_0_1px_0_rgba(255,255,255,0.15)] font-semibold'
                                            : 'text-[#EAFFD0]/70 text-[#A8B0A6]/75 hover:text-ivory hover:text-[#F4F2ED] hover:bg-white/5 border border-transparent font-medium'
                                    }`}
                                >
                                    <span>{tab.label}</span>
                                    {typeof tab.count === 'number' && (
                                        <span
                                            className={`text-[10px] px-1.5 py-0.5 rounded-full transition-colors ${
                                                isSelected
                                                    ? 'bg-lime/20 bg-emerald-400/20 text-lime text-emerald-300 font-semibold'
                                                    : 'bg-white/10 text-[#EAFFD0]/60 text-[#A8B0A6]'
                                            }`}
                                        >
                                            {tab.count}
                                        </span>
                                    )}
                                </button>
                            );
                        })}
                    </div>
                </div>

                <FilterBar
                    search={searchTerm}
                    sort={sortOption}
                    onSearchChange={handleSearchChange}
                    onSortChange={handleSortChange}
                    totalCount={totalCount}
                    filteredCount={projects?.length || 0}
                />

                <div className="mt-12">
                    {(projects?.length || 0) > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {(projects || []).map((project, idx) => (
                                <ProjectCard
                                    key={project.id}
                                    project={project}
                                    delay={idx * 0.08}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-24 border border-dashed border-white/15 bg-jet-surface/40 bg-[rgba(20,32,24,0.40)] backdrop-blur-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                            <p className="font-serif text-xl text-ivory text-[#F4F2ED]">
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
                                className="mt-4 text-xs uppercase tracking-widest font-semibold text-lime text-[#88B896] hover:text-lime-hover hover:text-[#A9D6B3] underline hover:no-underline transition-colors cursor-pointer"
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
