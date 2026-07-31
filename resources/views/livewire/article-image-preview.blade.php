<div class="mb-3">
    <label for="imageUrl" class="form-label fw-semibold">
        <i class="bi bi-image me-2"></i> Article Image
    </label>
    <div class="input-group">
        <input 
            type="url" 
            class="form-control rounded-pill" 
            id="imageUrl" 
            placeholder="https://example.com/image.jpg" 
            wire:model.live="imageUrl"
            wire:loading.attr="disabled"
        >
        @if($imageUrl)
            <button 
                type="button" 
                class="btn btn-outline-secondary rounded-pill" 
                wire:click="clearImage"
                title="Remove image"
            >
                <i class="bi bi-x-circle"></i>
            </button>
        @endif
    </div>
    
    @error('imageUrl')
        <div class="text-danger small mt-1">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
        </div>
    @enderror

    <div class="mt-3">
        @if($isLoading)
            <div class="d-flex align-items-center text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <small>Loading image...</small>
            </div>
        @elseif($imageData)
            <div class="border rounded-3 p-3 bg-light">
                <h6 class="text-muted mb-2">
                    <i class="bi bi-eye me-1"></i>Image Preview 
                </h6>
                <div class="text-center">
                   
                        <img 
                            src="{{ $imageData }}" 
                            alt="Image Preview" 
                            class="img-fluid rounded shadow-sm" 
                            style="max-height: 300px; max-width: 100%;"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                        >
                        <div style="display: none;" class="text-danger">
                            <i class="bi bi-image me-1"></i>Error loading image 
                        </div>
                   
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        URL: <code>{{ $imageUrl }}</code>
                    </small>
                </div>
            </div>
        @elseif($imageUrl)
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <div>
                    <small>Insert a valid URL to see the image preview </small>
                </div>
            </div>
        @else
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                    Insert the URL of an image to see the preview of the image
            </div>
        @endif
    </div>
</div>
