{{--
    Inline status/response form for one feedback entry. Rendered inside the
    feedback table via view(...)->render() and posted to admin.feedback.update.
--}}

<form
    method="POST"
    action="{{ route('admin.feedback.update', $row) }}"
    class="d-flex flex-column gap-1"
>
    @csrf
    @method('PATCH')

    <select
        class="fh-field"
        name="status"
        aria-label="Status for {{ $row->subject }}"
        required
    >
        @foreach(['open' => 'Open', 'in_review' => 'In review', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $statusValue => $statusLabel)
            <option value="{{ $statusValue }}" @selected(old('status.'.$row->id, $row->status) === $statusValue)>{{ $statusLabel }}</option>
        @endforeach
    </select>

    <textarea
        class="fh-field"
        name="admin_response"
        rows="2"
        maxlength="5000"
        placeholder="Reply shown to the member…"
        aria-label="Response for {{ $row->subject }}"
    >{{ old('admin_response.'.$row->id, $row->admin_response) }}</textarea>

    <button class="btn btn-fh-outline btn-sm" type="submit">
        <i class="bi bi-send" aria-hidden="true"></i> Save response
    </button>

    @if($row->admin_response)
        <small class="text-muted">Last reply: {{ \Illuminate\Support\Str::limit($row->admin_response, 70) }}</small>
    @elseif($row->user_id)
        <small class="text-muted">No reply sent yet</small>
    @else
        <small class="text-muted">Guest feedback — no member to notify</small>
    @endif
</form>
