import React from 'react';
import { Search, X, SlidersHorizontal } from 'lucide-react';

export default function FilterBar({
    search = '',
    sort = 'newest',
    onSearchChange,
    onSortChange,
    totalCount = 0,
    filteredCount = 0,
}) {
    return (
        <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 py-6 border-b border-white/10">
            {/* Search Input */}
            <div className="relative flex-1 max-w-md">
                <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#A8B0A6]/60" />
                <input
                    type="text"
                    value={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    placeholder="Search sanctuaries by name, location, or material..."
                    className="w-full pl-10 pr-9 py-2 bg-[#121814]/90 backdrop-blur-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] border border-white/15 text-sm text-[#F4F2ED] placeholder:text-[#A8B0A6]/40 focus:outline-none focus:border-[#88B896] focus:ring-1 focus:ring-[#88B896]/30 transition-all"
                />
                {search && (
                    <button
                        onClick={() => onSearchChange('')}
                        className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-[#A8B0A6]/60 hover:text-[#F4F2ED] transition-colors"
                        aria-label="Clear search"
                    >
                        <X className="w-3.5 h-3.5" />
                    </button>
                )}
            </div>

            {/* Sort & Count Controls */}
            <div className="flex items-center justify-between md:justify-end space-x-4">
                {filteredCount > 0 && totalCount > 0 && (
                    <span className="text-xs uppercase tracking-widest text-[#A8B0A6]/80 hidden sm:inline">
                        Showing {filteredCount} of {totalCount} Works
                    </span>
                )}

                <div className="flex items-center space-x-2.5">
                    <SlidersHorizontal className="w-4 h-4 text-[#88B896]" />
                    <label htmlFor="sort-select" className="text-xs uppercase tracking-widest text-[#C4C9C3]/80 font-medium">
                        Sort By:
                    </label>
                    <select
                        id="sort-select"
                        value={sort}
                        onChange={(e) => onSortChange(e.target.value)}
                        className="px-3 py-2 bg-[#121814] backdrop-blur-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] border border-white/15 text-xs uppercase tracking-wider text-[#F4F2ED] focus:outline-none focus:border-[#88B896] focus:ring-1 focus:ring-[#88B896]/30 transition-all cursor-pointer"
                    >
                        <option value="newest" className="bg-[#121814] text-[#F4F2ED]">Newest First</option>
                        <option value="oldest" className="bg-[#121814] text-[#F4F2ED]">Oldest First</option>
                        <option value="area_desc" className="bg-[#121814] text-[#F4F2ED]">Largest Area (m²)</option>
                    </select>
                </div>
            </div>
        </div>
    );
}
