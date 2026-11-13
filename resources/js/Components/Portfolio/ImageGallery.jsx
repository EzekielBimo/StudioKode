import React, { useState, useEffect } from 'react';
import InView from '../UI/InView';
import Modal from '../UI/Modal';
import { Maximize2, ChevronLeft, ChevronRight } from 'lucide-react';

export default function ImageGallery({ images = [] }) {
    const [selectedIndex, setSelectedIndex] = useState(null);

    useEffect(() => {
        const handleKeyDown = (e) => {
            if (selectedIndex === null) return;
            if (e.key === 'ArrowLeft') {
                setSelectedIndex((prev) => (prev - 1 + images.length) % images.length);
            } else if (e.key === 'ArrowRight') {
                setSelectedIndex((prev) => (prev + 1) % images.length);
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [selectedIndex, images.length]);

    if (!images || images.length === 0) return null;

    const currentImage = selectedIndex !== null ? images[selectedIndex] : null;

    return (
        <div>
            {/* Gallery Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {images.map((img, idx) => (
                    <InView key={img.id || idx} delay={idx * 0.08} className="group relative">
                        <div
                            onClick={() => setSelectedIndex(idx)}
                            className="aspect-[4/3] overflow-hidden bg-[#121814] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] cursor-pointer relative"
                        >
                            <img
                                src={img.image_path}
                                alt={img.caption || 'Project visual'}
                                className="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                loading="lazy"
                                decoding="async"
                            />
                            <div className="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span className="p-3 bg-[#121814]/90 backdrop-blur-md border border-white/15 rounded-full text-[#F4F2ED] shadow-xl group-hover:border-[#88B896]/50 transform scale-90 group-hover:scale-100 transition-all">
                                    <Maximize2 className="w-5 h-5" />
                                </span>
                            </div>
                        </div>
                        {img.caption && (
                            <p className="mt-2 text-xs text-[#A8B0A6]/80 font-light italic">
                                {img.caption}
                            </p>
                        )}
                    </InView>
                ))}
            </div>

            {/* High-Resolution Lightbox Modal */}
            <Modal
                isOpen={selectedIndex !== null}
                onClose={() => setSelectedIndex(null)}
                title={currentImage?.caption || 'Sanctuary Architectural Archive'}
                maxWidth="max-w-6xl"
            >
                {currentImage && (
                    <div className="relative flex flex-col items-center">
                        {/* Lightbox Image Container */}
                        <div className="relative w-full aspect-[16/10] max-h-[70vh] bg-[#0B0E0C] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] flex items-center justify-center overflow-hidden">
                            <img
                                key={currentImage.image_path}
                                src={currentImage.image_path}
                                alt={currentImage.caption || 'Expanded image'}
                                className="w-full h-full object-contain"
                                decoding="async"
                            />

                            {/* Left / Right Navigation Buttons */}
                            {images.length > 1 && (
                                <>
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedIndex((prev) => (prev - 1 + images.length) % images.length);
                                        }}
                                        className="absolute left-4 top-1/2 -translate-y-1/2 p-2.5 bg-[#142018]/80 hover:bg-[#1E2E23] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] text-[#F4F2ED] rounded-full backdrop-blur-sm transition-colors"
                                        aria-label="Previous Image"
                                    >
                                        <ChevronLeft className="w-6 h-6" />
                                    </button>
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedIndex((prev) => (prev + 1) % images.length);
                                        }}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 p-2.5 bg-[#142018]/80 hover:bg-[#1E2E23] border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)] text-[#F4F2ED] rounded-full backdrop-blur-sm transition-colors"
                                        aria-label="Next Image"
                                    >
                                        <ChevronRight className="w-6 h-6" />
                                    </button>
                                </>
                            )}
                        </div>

                        {/* Caption & Counter */}
                        <div className="w-full mt-4 flex items-center justify-between text-xs text-[#A8B0A6]/80 tracking-wider uppercase font-medium">
                            <span className="font-light italic text-[#F4F2ED]/90 normal-case text-sm">
                                {currentImage.caption || 'Archival Record'}
                            </span>
                            <span className="font-mono">
                                {String(selectedIndex + 1).padStart(2, '0')} / {String(images.length).padStart(2, '0')}
                            </span>
                        </div>
                    </div>
                )}
            </Modal>
        </div>
    );
}
