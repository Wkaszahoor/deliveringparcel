{{-- Shared hero slide form fields (create + edit).
    Expects: $slide, plus (create) $nextPosition or (edit) nothing --}}
<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="md:col-span-2 space-y-4">
        <x-admin.card title="Slide content">
            <div class="mb-3">
                <label for="title" class="mb-1 block text-sm font-medium text-slate-700">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $slide->title) }}" maxlength="255" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('title') ? ' border-red-400' : '' }}">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-3">
                <label for="subtitle" class="mb-1 block text-sm font-medium text-slate-700">Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle', $slide->subtitle) }}" maxlength="255" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('subtitle') ? ' border-red-400' : '' }}">
                @error('subtitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="mb-3">
                    <label for="btn_text" class="mb-1 block text-sm font-medium text-slate-700">Button text</label>
                    <input type="text" id="btn_text" name="btn_text" value="{{ old('btn_text', $slide->btn_text) }}" maxlength="100" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('btn_text') ? ' border-red-400' : '' }}">
                    @error('btn_text')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-3">
                    <label for="btn_link" class="mb-1 block text-sm font-medium text-slate-700">Button link</label>
                    <input type="text" id="btn_link" name="btn_link" value="{{ old('btn_link', $slide->btn_link) }}" maxlength="255" placeholder="/services or https://…" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('btn_link') ? ' border-red-400' : '' }}">
                    @error('btn_link')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="mb-3">
                    <label for="position" class="mb-1 block text-sm font-medium text-slate-700">Position</label>
                    <input type="number" id="position" name="position" value="{{ old('position', $slide->position ?? ($nextPosition ?? 1)) }}" min="0" max="999999" step="1" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('position') ? ' border-red-400' : '' }}">
                    <p class="mt-1 text-xs text-slate-500">Lower numbers appear first. Clashing positions are resolved automatically.</p>
                    @error('position')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-3 flex items-end pb-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $slide->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                        Active (visible on the site)
                    </label>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Slide options">
            @include('admin.heroslider._options', [
                'optionFields' => $optionFields,
                'values'       => $values ?? [],
            ])
        </x-admin.card>
    </div>

    <div>
        <x-admin.card title="Image *">
            <div class="mb-2 text-center" id="image-preview-wrap" @if (empty($slide->image)) style="display:none;" @endif>
                <img id="image-preview" src="{{ $slide->imageUrl() }}" alt="slide preview" class="mx-auto max-h-[200px] rounded-md">
            </div>
            <div class="mb-1">
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.gif,.svg" @if (empty($slide->id)) required @endif class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200{{ $errors->has('image') ? ' border-red-400' : '' }}">
            </div>
            @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-slate-500">Choose image (jpg, png, webp, gif, svg — max 2MB). Recommended: wide banner, at least 1600×600px.</p>
        </x-admin.card>
    </div>
</div>
