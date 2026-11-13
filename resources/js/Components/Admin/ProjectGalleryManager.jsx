import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { Reorder, useDragControls } from 'framer-motion';
import {
    GripVertical,
    UploadCloud,
    Trash2,
    Check,
    X,
    Edit2,
    ImageIcon,
    AlertCircle,
    ArrowUpDown,
    CheckCircle2,
} from 'lucide-react';
import { id } from '../../lib/id';

export function resolveImageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
        return path;
    }
    if (path.startsWith('/storage/')) {
        return path;
    }
    if (path.startsWith('storage/')) {
        return `/${path}`;
    }
    return `/storage/${path}`;
}

function GalleryItemRow({
    item,
    index,
    onSaveCaption,
    onDelete,
    isUpdatingCaption,
    isDeleting,
}) {
    const controls = useDragControls();
    const [isEditing, setIsEditing] = useState(false);
    const [captionText, setCaptionText] = useState(item.caption || '');

    useEffect(() => {
        setCaptionText(item.caption || '');
    }, [item.caption]);

    const handleSave = () => {
        onSaveCaption(item.id, captionText, () => {
            setIsEditing(false);
        });
    };

    const handleCancel = () => {
        setCaptionText(item.caption || '');
        setIsEditing(false);
    };

    return (
        <Reorder.Item
            value={item}
            dragListener={false}
            dragControls={controls}
            className="bg-[#0E1310]/80 backdrop-blur-xs border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] hover:border-emerald-500/30 p-3 sm:p-4 rounded shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all hover:shadow-md select-none"
        >
            <div className="flex items-center space-x-3 w-full sm:w-auto flex-1 min-w-0">
                {/* Drag Handle */}
                <div
                    onPointerDown={(e) => controls.start(e)}
                    className="cursor-grab active:cursor-grabbing p-1.5 text-stone-400 hover:text-emerald-400 hover:bg-white/5 rounded touch-none shrink-0 transition-colors"
                    title={id.gallery.drag_handle}
                >
                    <GripVertical className="w-5 h-5" />
                </div>

                {/* Sequence badge */}
                <span className="font-mono text-xs font-semibold text-stone-400 w-6 text-center shrink-0">
                    #{index + 1}
                </span>

                {/* Image Thumbnail with Fixed Aspect Ratio (prevent CLS) */}
                <div className="w-20 h-15 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-white/10 shrink-0 relative">
                    <img
                        src={resolveImageUrl(item.image_path)}
                        alt={item.caption || `Foto ${index + 1}`}
                        className="w-full h-full object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                </div>

                {/* Caption / Editable Field */}
                <div className="flex-1 min-w-0 pr-2">
                    {isEditing ? (
                        <div className="flex items-center space-x-2">
                            <input
                                type="text"
                                value={captionText}
                                onChange={(e) => setCaptionText(e.target.value)}
                                placeholder={id.gallery.caption_placeholder}
                                className="w-full text-xs px-2.5 py-1.5 border border-emerald-500/50 bg-[#0B0E0C] text-stone-100 placeholder:text-stone-500 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500/50"
                                autoFocus
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') handleSave();
                                    if (e.key === 'Escape') handleCancel();
                                }}
                            />
                            <button
                                type="button"
                                onClick={handleSave}
                                disabled={isUpdatingCaption}
                                className="p-1.5 bg-emerald-600 hover:bg-emerald-500 text-stone-50 rounded disabled:opacity-50 transition-colors"
                                title={id.gallery.caption_save}
                            >
                                <Check className="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                onClick={handleCancel}
                                className="p-1.5 bg-stone-800 hover:bg-stone-700 text-stone-300 rounded transition-colors"
                                title={id.common.cancel}
                            >
                                <X className="w-3.5 h-3.5" />
                            </button>
                        </div>
                    ) : (
                        <div className="flex items-center space-x-2 group">
                            <p className="text-xs sm:text-sm text-stone-200 truncate">
                                {item.caption ? (
                                    <span>{item.caption}</span>
                                ) : (
                                    <span className="italic text-stone-500 text-xs">
                                        (Belum ada takarir)
                                    </span>
                                )}
                            </p>
                            <button
                                type="button"
                                onClick={() => setIsEditing(true)}
                                className="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-stone-400 hover:text-emerald-400 rounded"
                                title={id.common.edit}
                            >
                                <Edit2 className="w-3.5 h-3.5" />
                            </button>
                        </div>
                    )}
                </div>
            </div>

            {/* Actions */}
            <div className="flex items-center space-x-2 self-end sm:self-center shrink-0">
                {!isEditing && (
                    <button
                        type="button"
                        onClick={() => setIsEditing(true)}
                        className="px-2.5 py-1 text-xs text-emerald-400 hover:bg-emerald-500/10 rounded border border-white/10 hover:border-emerald-500/30 flex items-center space-x-1 transition-colors"
                    >
                        <Edit2 className="w-3 h-3" />
                        <span>{id.common.edit}</span>
                    </button>
                )}
                <button
                    type="button"
                    onClick={() => onDelete(item)}
                    disabled={isDeleting}
                    className="px-2.5 py-1 text-xs text-rose-400 hover:bg-rose-500/10 rounded border border-rose-500/30 flex items-center space-x-1 disabled:opacity-50 transition-colors"
                >
                    <Trash2 className="w-3 h-3" />
                    <span>{id.common.delete}</span>
                </button>
            </div>
        </Reorder.Item>
    );
}

export default function ProjectGalleryManager({ project, images: initialImages = [] }) {
    const [items, setItems] = useState(initialImages);
    const [hasOrderChanged, setHasOrderChanged] = useState(false);
    const [isSavingOrder, setIsSavingOrder] = useState(false);

    // Upload state
    const [selectedFiles, setSelectedFiles] = useState([]);
    const [isUploading, setIsUploading] = useState(false);
    const [isDraggingOver, setIsDraggingOver] = useState(false);
    const [uploadError, setUploadError] = useState(null);

    // Mutation states
    const [updatingCaptionId, setUpdatingCaptionId] = useState(null);
    const [deletingId, setDeletingId] = useState(null);

    // Keep items synced if prop changes
    useEffect(() => {
        setItems(initialImages);
        setHasOrderChanged(false);
    }, [initialImages]);

    const handleReorder = (newItems) => {
        setItems(newItems);
        setHasOrderChanged(true);
    };

    const handleSaveOrder = () => {
        if (!hasOrderChanged || isSavingOrder) return;
        setIsSavingOrder(true);

        const orders = items.map((item, index) => ({
            id: item.id,
            sort_order: index + 1,
        }));
        const image_ids = items.map((item) => item.id);

        router.post(
            `/admin/projects/${project.id}/images/reorder`,
            { orders, image_ids },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setHasOrderChanged(false);
                    setIsSavingOrder(false);
                },
                onError: () => {
                    setIsSavingOrder(false);
                },
            }
        );
    };

    const handleFileSelect = (e) => {
        const files = Array.from(e.target.files || []);
        if (files.length === 0) return;
        queueFiles(files);
        e.target.value = '';
    };

    const queueFiles = (files) => {
        setUploadError(null);
        const validExtensions = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        const newQueue = [];

        for (const file of files) {
            if (!validExtensions.includes(file.type)) {
                setUploadError('Hanya berkas gambar JPG, JPEG, PNG, atau WEBP yang diperbolehkan.');
                continue;
            }
            if (file.size > 5 * 1024 * 1024) {
                setUploadError('Ukuran gambar tidak boleh melebihi 5 MB per berkas.');
                continue;
            }
            newQueue.push({
                file,
                previewUrl: URL.createObjectURL(file),
                caption: '',
            });
        }

        setSelectedFiles((prev) => [...prev, ...newQueue]);
    };

    const handleRemoveQueuedFile = (index) => {
        setSelectedFiles((prev) => {
            const copy = [...prev];
            if (copy[index]?.previewUrl) {
                URL.revokeObjectURL(copy[index].previewUrl);
            }
            copy.splice(index, 1);
            return copy;
        });
    };

    const handleQueuedCaptionChange = (index, value) => {
        setSelectedFiles((prev) => {
            const copy = [...prev];
            copy[index].caption = value;
            return copy;
        });
    };

    const handleUploadQueued = () => {
        if (selectedFiles.length === 0 || isUploading) return;
        setIsUploading(true);
        setUploadError(null);

        const formData = new FormData();
        selectedFiles.forEach((item) => {
            formData.append('images[]', item.file);
            formData.append('captions[]', item.caption || '');
        });

        router.post(`/admin/projects/${project.id}/images`, formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                // Clear previews
                selectedFiles.forEach((item) => URL.revokeObjectURL(item.previewUrl));
                setSelectedFiles([]);
                setIsUploading(false);
            },
            onError: (err) => {
                setIsUploading(false);
                const firstMsg = Object.values(err)[0];
                setUploadError(firstMsg || 'Gagal mengunggah foto.');
            },
        });
    };

    const handleSaveCaption = (imageId, captionText, callback) => {
        setUpdatingCaptionId(imageId);
        router.put(
            `/admin/project-images/${imageId}`,
            { caption: captionText },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setUpdatingCaptionId(null);
                    if (callback) callback();
                },
                onError: () => {
                    setUpdatingCaptionId(null);
                },
            }
        );
    };

    const handleDeleteImage = (item) => {
        if (!window.confirm(id.gallery.delete_confirm_message)) {
            return;
        }

        setDeletingId(item.id);
        router.delete(`/admin/project-images/${item.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingId(null);
            },
            onError: () => {
                setDeletingId(null);
            },
        });
    };

    return (
        <div className="space-y-8">
            {/* Multi-upload Dropzone Card */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded">
                <div className="mb-4">
                    <h2 className="font-serif text-xl tracking-tight text-stone-100">
                        {id.gallery.upload_title}
                    </h2>
                    <p className="text-xs text-stone-400 mt-1">
                        {id.gallery.file_hint}
                    </p>
                </div>

                {uploadError && (
                    <div className="mb-4 p-3 bg-rose-950/80 border border-rose-500/30 text-rose-200 text-xs rounded flex items-center space-x-2">
                        <AlertCircle className="w-4 h-4 shrink-0 text-rose-400" />
                        <span>{uploadError}</span>
                    </div>
                )}

                {/* Dropzone Container */}
                <div
                    onDragOver={(e) => {
                        e.preventDefault();
                        setIsDraggingOver(true);
                    }}
                    onDragLeave={() => setIsDraggingOver(false)}
                    onDrop={(e) => {
                        e.preventDefault();
                        setIsDraggingOver(false);
                        const files = Array.from(e.dataTransfer.files || []);
                        queueFiles(files);
                    }}
                    className={`border-2 border-dashed rounded-lg p-6 sm:p-8 text-center transition-colors ${
                        isDraggingOver
                            ? 'border-emerald-400 bg-emerald-500/15'
                            : 'border-white/15 hover:border-emerald-500/50 bg-[#0E1310]/40'
                    }`}
                >
                    <UploadCloud className="w-10 h-10 mx-auto text-stone-400 mb-3" />
                    <p className="text-sm font-medium text-stone-100">
                        {id.gallery.dropzone_prompt}
                    </p>
                    <p className="text-xs text-stone-400 mt-1">
                        JPG, PNG, WEBP (maks. 5MB)
                    </p>

                    <label className="mt-4 inline-block px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 text-xs font-semibold rounded cursor-pointer border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)] transition-all">
                        <span>{id.projects.fields.browse_file}</span>
                        <input
                            type="file"
                            multiple
                            accept="image/jpeg,image/png,image/webp,image/jpg"
                            onChange={handleFileSelect}
                            className="hidden"
                        />
                    </label>
                </div>

                {/* Staged Upload Queue */}
                {selectedFiles.length > 0 && (
                    <div className="mt-6 border-t border-white/10 pt-6 space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xs font-semibold uppercase tracking-wider text-stone-300">
                                {id.gallery.selected_queue} ({selectedFiles.length})
                            </h3>
                            <button
                                type="button"
                                onClick={() => {
                                    selectedFiles.forEach((item) => URL.revokeObjectURL(item.previewUrl));
                                    setSelectedFiles([]);
                                }}
                                className="text-xs text-stone-400 hover:text-rose-400 underline transition-colors"
                            >
                                Kosongkan Antrean
                            </button>
                        </div>

                        <div className="space-y-3 max-h-80 overflow-y-auto pr-1">
                            {selectedFiles.map((staged, idx) => (
                                <div
                                    key={idx}
                                    className="flex items-center space-x-3 p-3 bg-[#0E1310]/80 border border-white/10 rounded"
                                >
                                    <div className="w-16 h-12 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-white/10 shrink-0">
                                        <img
                                            src={staged.previewUrl}
                                            alt={`Preview ${idx + 1}`}
                                            className="w-full h-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <p className="text-xs font-medium text-stone-200 truncate">
                                            {staged.file.name}
                                        </p>
                                        <input
                                            type="text"
                                            value={staged.caption}
                                            onChange={(e) => handleQueuedCaptionChange(idx, e.target.value)}
                                            placeholder={id.gallery.caption_placeholder}
                                            className="mt-1 w-full text-xs px-2.5 py-1 border border-white/10 rounded bg-[#0B0E0C] text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50"
                                        />
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() => handleRemoveQueuedFile(idx)}
                                        className="p-1.5 text-stone-400 hover:text-rose-400 rounded transition-colors"
                                        title={id.common.delete}
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </button>
                                </div>
                            ))}
                        </div>

                        <div className="flex justify-end pt-2">
                            <button
                                type="button"
                                onClick={handleUploadQueued}
                                disabled={isUploading}
                                className="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 text-xs font-semibold rounded border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)] disabled:opacity-50 transition-all flex items-center space-x-2"
                            >
                                {isUploading ? (
                                    <>
                                        <div className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                        <span>{id.gallery.uploading_button}</span>
                                    </>
                                ) : (
                                    <>
                                        <UploadCloud className="w-4 h-4" />
                                        <span>{id.gallery.upload_button}</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Gallery Sorting & List Card */}
            <div className="bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.08)] rounded">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-white/10 gap-3">
                    <div>
                        <div className="flex items-center space-x-2">
                            <h2 className="font-serif text-xl tracking-tight text-stone-100">
                                {id.gallery.reorder_title}
                            </h2>
                            <span className="px-2 py-0.5 rounded-full text-xs bg-white/10 text-emerald-300 border border-white/10">
                                {id.gallery.total_photos} {items.length}
                            </span>
                        </div>
                        <p className="text-xs text-stone-400 mt-1">
                            {id.gallery.reorder_instruction}
                        </p>
                    </div>

                    {hasOrderChanged && (
                        <div className="flex items-center space-x-2">
                            <span className="text-xs text-amber-300 bg-amber-500/15 border border-amber-500/30 px-2.5 py-1 rounded">
                                Urutan berubah
                            </span>
                            <button
                                type="button"
                                onClick={handleSaveOrder}
                                disabled={isSavingOrder}
                                className="px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 text-xs font-semibold rounded border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)] disabled:opacity-50 transition-all flex items-center space-x-1.5"
                            >
                                <ArrowUpDown className="w-3.5 h-3.5" />
                                <span>
                                    {isSavingOrder
                                        ? id.gallery.saving_order_button
                                        : id.gallery.save_order_button}
                                </span>
                            </button>
                        </div>
                    )}
                </div>

                {items.length === 0 ? (
                    <div className="py-12 text-center">
                        <ImageIcon className="w-12 h-12 mx-auto text-stone-500/40 mb-3" />
                        <p className="text-sm text-stone-400">
                            {id.gallery.empty_state}
                        </p>
                    </div>
                ) : (
                    <Reorder.Group
                        axis="y"
                        values={items}
                        onReorder={handleReorder}
                        className="space-y-3"
                    >
                        {items.map((item, index) => (
                            <GalleryItemRow
                                key={item.id}
                                item={item}
                                index={index}
                                onSaveCaption={handleSaveCaption}
                                onDelete={handleDeleteImage}
                                isUpdatingCaption={updatingCaptionId === item.id}
                                isDeleting={deletingId === item.id}
                            />
                        ))}
                    </Reorder.Group>
                )}
            </div>
        </div>
    );
}
