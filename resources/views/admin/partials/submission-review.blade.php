{{--
    Inline moderation form for one submission (approve / reject / return to pending
    with an optional note). Rendered inside the submissions table via
    view(...)->render().

    @include('admin.partials.submission-review', ['row' => $submission])
--}}

@php
    $reviewStatusLabel = match ($row->status) {
        'approved' => 'Approved by '.($row->reviewer?->name ?? 'someone').' '.$row->reviewed_at?->diffForHumans(),
        'rejected' => 'Rejected '.$row->reviewed_at?->diffForHumans(),
        default => 'Awaiting review',
    };
@endphp

<form
    method="POST"
    action="{{ route('admin.submissions.status', $row) }}"
    class="d-flex flex-column gap-1"
>
    @csrf
    @method('PATCH')

    <input
        class="fh-field"
        type="text"
        name="admin_note"
        value="{{ old('admin_note.'.$row->id, $row->admin_note) }}"
        maxlength="1000"
        placeholder="Admin note (optional)"
        aria-label="Admin note for {{ $row->title }}"
    >

    <div class="d-flex gap-1 flex-wrap">
        <button class="btn btn-fh-outline btn-sm" type="submit" name="status" value="approved">
            <i class="bi bi-check2" aria-hidden="true"></i> Approve
        </button>
        <button
            class="btn btn-fh-outline btn-sm text-danger"
            type="submit"
            name="status"
            value="rejected"
            data-confirm="Reject submission {{ $row->title }}?"
        >
            <i class="bi bi-x-lg" aria-hidden="true"></i> Reject
        </button>
        @if($row->status !== 'pending')
            <button class="btn btn-fh-ghost btn-sm" type="submit" name="status" value="pending">
                Reset
            </button>
        @endif
    </div>

    <small class="text-muted">{{ $reviewStatusLabel }}</small>
</form>
