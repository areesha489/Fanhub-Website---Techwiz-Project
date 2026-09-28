{{--
    Row actions (edit + delete with confirmation).
    Rendered from table column closures via view(...)->render() or used directly.

    @include('admin.partials.row-actions', [
        'editRoute'   => route('admin.content.edit', $row),
        'deleteRoute' => route('admin.content.destroy', $row),
        'label'       => $row->title,
        'deleteOnly'  => false,
    ])
--}}

@php
    $rowLabel = $label ?? 'this record';
    $rowConfirm = $confirm ?? ('Delete ' . $rowLabel . '? This cannot be undone.');
@endphp

<div class="d-flex gap-1 justify-content-end flex-nowrap">
    @unless($deleteOnly ?? false)
        <a
            class="btn btn-fh-ghost btn-sm"
            href="{{ $editRoute }}"
            aria-label="Edit {{ $rowLabel }}"
            title="Edit"
        >
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
        </a>
    @endunless

    <form
        method="POST"
        action="{{ $deleteRoute }}"
        class="d-inline"
        data-confirm="{{ $rowConfirm }}"
    >
        @csrf
        @method('DELETE')
        <button
            class="btn btn-fh-ghost btn-sm text-danger"
            type="submit"
            aria-label="Delete {{ $rowLabel }}"
            title="Delete"
        >
            <i class="bi bi-trash" aria-hidden="true"></i>
        </button>
    </form>
</div>
