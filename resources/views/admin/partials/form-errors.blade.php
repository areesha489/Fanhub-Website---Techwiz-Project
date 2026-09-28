{{-- Validation + flash errors, shared by every admin form. --}}
@if($errors->any())
    <div class="alert alert-danger mb-3" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill me-2 mt-1" aria-hidden="true"></i>
            <div>
                <strong>Please fix the following {{ $errors->count() === 1 ? 'issue' : 'issues' }}:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach($errors->all() as $adminError)
                        <li>{{ $adminError }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
