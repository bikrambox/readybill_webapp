<!-- Error Card -->
<div class="container mb-4 error-div d-none" @if(isset($modalIdAttribute)) id="{{ $modalIdAttribute }}" @endif>
    <div class="card border-danger" style="border-radius: 10px; overflow: hidden;">
        <div class="card-header text-white fw-bold py-0"
            style="background-color: #B64A21; display: flex; justify-content: space-between; align-items: center;">
            <h6 class="my-0">{{ $heading }}</h6>
            <button class="btn btn-sm text-white close-error-btn"
                style="background: transparent; border: none; font-size: 22px;">&times;</button>
        </div>
        <div class="card-body error-body" style="background-color: #FDF2F1;">
        </div>
    </div>
</div>